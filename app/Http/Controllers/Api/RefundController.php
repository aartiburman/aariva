<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RefundRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    /**
     * Create a new refund request.
     */
    public function createRefundRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'order_id' => 'required|exists:orders,id',
            'order_item_id' => 'required|exists:order_items,id',
            'refund_reason' => 'required|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'images' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify that the order belongs to the user
        $order = Order::where('id', $request->order_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found or access denied.'
            ], 404);
        }

        // Verify that the order item belongs to the order
        $orderItem = OrderItem::where('id', $request->order_item_id)
            ->where('order_id', $order->id??$request->order_id)
            ->first();
            

        if (!$orderItem) {
            return response()->json([
                'status' => false,
                'message' => 'Order item not found.'
            ], 404);
        }

        
        // Check if refund already requested for this item
        $existingRefund = RefundRequest::where('order_item_id', $orderItem->id)->first();
        if ($existingRefund) {
            return response()->json([
                'status' => false,
                'message' => 'Refund request already exists for this item.'
            ], 400);
        }

        // Check time constraints (7-15 days from delivery) - Example logic
        if ($order->delivery_date) {
            $deliveryDate = \Carbon\Carbon::parse($order->delivery_date);
            $daysDiff = $deliveryDate->diffInDays(now());
            if ($daysDiff > 15) {
                return response()->json([
                    'status' => false,
                    'message' => 'Refund period has expired (15 days policy).'
                ], 400);
            }
        } else {
             // If not delivered yet, maybe allow cancellation/refund?
             // Or assume it must be delivered to request refund.
             // For now, let's proceed assuming user can request.
        }

        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $fileName = time() . '_' . rand(100, 999) . '_' .
                        preg_replace('/[^A-Za-z0-9_.-]/', '_', $image->getClientOriginalName());

                    $image->move(public_path('uploads/refunds'), $fileName);

                    $imagePaths[] = $fileName; // array me store karo
                }
            }
        }


        $refund = RefundRequest::create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'user_id' => $request->user_id,
            'vendor_id' => $orderItem->vendor_id,
            'refund_reason' => $request->refund_reason,
            'description' => $request->description,
            'images' => $imagePaths,
            'amount' => $orderItem->total_actual_price ?? $orderItem->price * $orderItem->quantity, // Fallback
            'vendor_status' => 0, // Pending
            'admin_status' => 0, // Pending
        ]);

        // Send Notification to Vendor and Admin
        $vendor = User::find($orderItem->vendor_id);
        if ($vendor) {
            try {
                \App\Helpers\NotificationHelper::send($vendor, [
                    'title' => 'New Refund Request',
                    'message' => 'A customer has requested a refund for Order #' . ($order->order_reference_id ?? $order->id),
                    'type' => 'refund',
                    'url' => route('vendor.refund.show', $refund->id),
                    'icon' => 'solar:back-bold-duotone',
                    'priority' => 'high'
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Refund Request Vendor Notification Error: ' . $e->getMessage());
            }
        }

        $admins = User::where('role', '1')->get();
        foreach ($admins as $admin) {
            try {
                \App\Helpers\NotificationHelper::send($admin, [
                    'title' => 'New Refund Request',
                    'message' => 'Refund requested for Order #' . ($order->order_reference_id ?? $order->id) . ' from ' . ($vendor->store_name ?? $vendor->name),
                    'type' => 'refund',
                    'url' => route('refund.show', $refund->id),
                    'icon' => 'solar:back-bold-duotone',
                    'priority' => 'medium'
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Refund Request Admin Notification Error: ' . $e->getMessage());
            }
        }
        
        Orderitem::where('id', $request->order_item_id)->update(['payment_status' => '3','status'=>'5']);

        

     

        return response()->json([
            'status' => true,
            'message' => 'Refund request submitted successfully.',
            'data' => $refund
        ]);
    }

    /**
     * Get refund requests for a user.
     */
    public function getUserRefunds(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $refunds = RefundRequest::where('user_id', $request->user_id)
            ->with(['orderItem.product', 'orderItem.variant'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $refunds
        ]);
    }

    /**
     * Get user wallet balance and transactions.
     */
    public function getWalletDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::with('country')->find($request->user_id);
        $currencyCode = 'INR';
        if ($user && $user->country && !empty($user->country->currency_code)) {
            $currencyCode = $user->country->currency_code;
        }
        $transactions = WalletTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($t) use ($currencyCode) {
                return [
                    'id' => $t->id,
                    'amount' => $t->amount,
                    'type' => $t->type,
                    'description' => $t->description,
                    'reference_id' => $t->reference_id,
                    'status' => $t->status,
                    'currency_code' => $currencyCode,
                    'created_at' => $t->created_at,
                    'updated_at' => $t->updated_at,
                ];
            });

        return response()->json([
            'status' => true,
            'data' => [
                'wallet_balance' => $user->wallet_balance ?? 0,
                'reward_balance' => $user->reward_balance ?? 0,
                'currency_code' => $currencyCode,
                'transactions' => $transactions
            ]
        ]);
    }
}
