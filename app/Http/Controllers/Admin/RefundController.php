<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RefundRequest;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    /**
     * Get all refund requests (that are initiated by vendor).
     */
    public function getAllRefunds(Request $request)
    {
        $query = RefundRequest::join('order_items', 'refund_requests.order_item_id', '=', 'order_items.id')
            ->select('refund_requests.*', 'order_items.status as order_status')
            ->with(['orderItem.product', 'orderItem.variant', 'user', 'vendor']);
        $query = $this->applyFilters($request, $query);

        // Export logic
        if ($request->has('export')) {
            return $this->exportRefunds($query->get());
        }

        $refunds = $query->orderBy('refund_requests.updated_at', 'desc')->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'table' => view('backend.admin.refund.partials.table', compact('refunds'))->render(),
                'pagination' => $refunds->links()->render(),
                'info' => 'Showing ' . ($refunds->firstItem() ?? 0) . ' to ' . ($refunds->lastItem() ?? 0) . ' of ' . $refunds->total() . ' entries'
            ]);
        }

        return view('backend.admin.refund.index', compact('refunds'));
    }

    private function applyFilters(Request $request, $query)
    {
        // Status filter (admin_status)
        if ($request->filled('status')) {
            $query->where('refund_requests.admin_status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);

            if (count($dates) == 2) {
                $query->whereDate('refund_requests.created_at', '>=', $dates[0])
                      ->whereDate('refund_requests.created_at', '<=', $dates[1]);
            } else {
                $query->whereDate('refund_requests.created_at', $dates[0]);
            }
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('refund_requests.id', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('vendor', function ($vq) use ($search) {
                      $vq->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('orderItem.product', function ($pq) use ($search) {
                      $pq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    private function exportRefunds($refunds)
    {
        $filename = "refund_requests_" . date('Y-m-d_H-i-s') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'User', 'Vendor', 'Product', 'Amount', 'Vendor Status', 'Admin Status', 'Date'];

        $callback = function() use($refunds, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($refunds as $refund) {
                $vendorStatus = 'Pending';
                if($refund->vendor_status == 1) $vendorStatus = 'Initiated';
                elseif($refund->vendor_status == 2) $vendorStatus = 'Rejected';

                $adminStatus = 'Pending';
                if($refund->admin_status == 1) $adminStatus = 'Approved';
                elseif($refund->admin_status == 2) $adminStatus = 'Rejected';

                fputcsv($file, [
                    $refund->id,
                    $refund->user->name ?? 'N/A',
                    $refund->vendor->name ?? 'N/A',
                    $refund->orderItem->product->name ?? 'N/A',
                    number_format($refund->amount, 2),
                    $vendorStatus,
                    $adminStatus,
                    $refund->created_at->format('d M, Y')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show refund detail.
     */
    public function show($id)
    {
        $refund = RefundRequest::with(['orderItem.product', 'orderItem.variant', 'user', 'vendor', 'order'])->findOrFail($id);
        return view('backend.admin.refund.show', compact('refund'));
    }

    /**
     * Admin action on refund request (approve or reject).
     */
    public function adminAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refund_id' => 'required|exists:refund_requests,id',
            'action' => 'required|in:approve,reject',
            'message' => 'required_if:action,reject|string|nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $refund = RefundRequest::find($request->refund_id);

        if ($refund->admin_status != 0) {
            return redirect()->back()->with('error', 'Action already taken on this refund request.');
        }

        if ($refund->vendor_status != 1) {
             // Admin should ideally only act if vendor initiated.
             // But if business logic allows admin to override, we can remove this check.
             return redirect()->back()->with('error', 'Vendor has not initiated this refund request yet.');
        }

        return DB::transaction(function () use ($request, $refund) {
            if ($request->action == 'approve') {
                $refund->admin_status = 1; // Approved
                $refund->admin_message = $request->message;
                $refund->save();

                // Notify Customer and Vendor
                $customer = User::find($refund->user_id);
                $vendor = User::find($refund->vendor_id);
                $order = Order::find($refund->order_id);
                $orderRef = $order->order_reference_id ?? $order->id;

                if ($customer) {
                    try {
                        \App\Helpers\NotificationHelper::send($customer, [
                            'title' => 'Refund Approved',
                            'message' => 'Your refund for Order #' . $orderRef . ' has been approved and credited to your wallet.',
                            'type' => 'refund',
                            'url' => route('vendor.refund.show', $refund->id), // Adjust URL as needed
                            'icon' => 'solar:check-circle-bold-duotone',
                            'priority' => 'high'
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Refund Approval Customer Notification Error: ' . $e->getMessage());
                    }
                }

                if ($vendor) {
                    try {
                        \App\Helpers\NotificationHelper::send($vendor, [
                            'title' => 'Refund Processed by Admin',
                            'message' => 'The refund for Order #' . $orderRef . ' has been approved by admin.',
                            'type' => 'refund',
                            'url' => route('vendor.refund.show', $refund->id),
                            'icon' => 'solar:check-circle-bold-duotone',
                            'priority' => 'medium'
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Refund Approval Vendor Notification Error: ' . $e->getMessage());
                    }
                }

                // Credit to User Wallet (product amount)
                $user = User::find($refund->user_id);
                if ($user) {
                    $user->wallet_balance += $refund->amount;
                    $user->save();

                    // Log Transaction
                    WalletTransaction::create([
                        'user_id' => $user->id,
                        'amount' => $refund->amount,
                        'type' => 'credit',
                        'description' => 'Refund for Order Item #' . $refund->order_item_id,
                        'reference_id' => $refund->id,
                        'status' => 1
                    ]);
                }

                // Reward wallet refund: return proportionally (refund_amount/order_total * reward_used)
                $order = Order::find($refund->order_id);
                $rewardUsed = (float) ($order->reward_used ?? 0);
                if ($rewardUsed > 0 && $order && $order->total_cost > 0) {
                    $refundRatio = $refund->amount / $order->total_cost;
                    $rewardToReturn = round($rewardUsed * $refundRatio, 2);
                    if ($rewardToReturn > 0 && $user) {
                        $user->reward_balance = ($user->reward_balance ?? 0) + $rewardToReturn;
                        $user->save();
                        WalletTransaction::create([
                            'user_id' => $user->id,
                            'amount' => $rewardToReturn,
                            'type' => 'credit',
                            'description' => 'Reward refund (proportional) for Order Item #' . $refund->order_item_id,
                            'reference_id' => 'REFUND-REWARD-' . $refund->id,
                            'status' => 1
                        ]);
                    }
                }

                return redirect()->back()->with('success', 'Refund approved and amount credited to user wallet.');

            } else {
                $refund->admin_status = 2; // Rejected
                $refund->admin_message = $request->message;
                $refund->save();

                // Notify Customer and Vendor
                $customer = User::find($refund->user_id);
                $vendor = User::find($refund->vendor_id);
                $order = Order::find($refund->order_id);
                $orderRef = $order->order_reference_id ?? $order->id;

                if ($customer) {
                    try {
                        \App\Helpers\NotificationHelper::send($customer, [
                            'title' => 'Refund Request Rejected by Admin',
                            'message' => 'Your refund request for Order #' . $orderRef . ' has been rejected by admin.',
                            'type' => 'refund',
                            'url' => route('vendor.refund.show', $refund->id), // Adjust URL as needed
                            'icon' => 'solar:close-circle-bold-duotone',
                            'priority' => 'medium'
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Refund Rejection Admin Notification Error (Customer): ' . $e->getMessage());
                    }
                }

                if ($vendor) {
                    try {
                        \App\Helpers\NotificationHelper::send($vendor, [
                            'title' => 'Refund Rejected by Admin',
                            'message' => 'The refund request for Order #' . $orderRef . ' has been rejected by admin.',
                            'type' => 'refund',
                            'url' => route('vendor.refund.show', $refund->id),
                            'icon' => 'solar:close-circle-bold-duotone',
                            'priority' => 'medium'
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Refund Rejection Admin Notification Error (Vendor): ' . $e->getMessage());
                    }
                }

                return redirect()->back()->with('success', 'Refund request rejected.');
            }
        });
    }
}