<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RefundRequest;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    /**
     * Get refund requests for the vendor.
     */
    public function getVendorRefunds(Request $request)
    {
        $vendor_id = Auth::id();

        $refunds = RefundRequest::where('vendor_id', $vendor_id)
            ->with(['orderItem.product', 'orderItem.variant', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)->withQueryString();

        return view('backend.vendor.refund.index', compact('refunds'));
    }

    /**
     * Show refund detail for vendor.
     */
    public function show($id)
    {
        $refund = RefundRequest::where('id', $id)
            ->where('vendor_id', Auth::id())
            ->with(['orderItem.product', 'orderItem.variant', 'user', 'order'])
            ->findOrFail($id);

        return view('backend.vendor.refund.show', compact('refund'));
    }

    /**
     * Vendor action on refund request (initiate or reject).
     */
    public function vendorAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refund_id' => 'required|exists:refund_requests,id',
            'action' => 'required|in:initiate,reject',
            'message' => 'required_if:action,reject|string|nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $refund = RefundRequest::where('id', $request->refund_id)
            ->where('vendor_id', Auth::id())
            ->first();

        if (!$refund) {
            return response()->json([
                'status' => false,
                'message' => 'Refund request not found or access denied.'
            ], 404);
        }

        if ($refund->vendor_status != 0) {
            return response()->json([
                'status' => false,
                'message' => 'Action already taken on this refund request.'
            ], 400);
        }

        if ($request->action == 'initiate') {
            $refund->vendor_status = 1; // Initiated
            $refund->vendor_message = $request->message;
        } else {
            $refund->vendor_status = 2; // Rejected
            $refund->vendor_message = $request->message;
        }

        $refund->save();

        // Notify Admin and Customer
        $order = \App\Models\Order::find($refund->order_id);
        $orderRef = $order->order_reference_id ?? $order->id;
        $vendorName = Auth::user()->store_name ?? Auth::user()->name;

        if ($request->action == 'initiate') {
            // Notify Admin
            $admins = \App\Models\User::where('role', '1')->get();
            foreach ($admins as $admin) {
                try {
                    \App\Helpers\NotificationHelper::send($admin, [
                        'title' => 'Refund Initiated by Vendor',
                        'message' => 'Vendor ' . $vendorName . ' has initiated a refund for Order #' . $orderRef,
                        'type' => 'refund',
                        'url' => route('refund.show', $refund->id),
                        'icon' => 'solar:back-bold-duotone',
                        'priority' => 'medium'
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Refund Initiation Admin Notification Error: ' . $e->getMessage());
                }
            }
        } else {
            // Notify Admin of Vendor Rejection
            $admins = \App\Models\User::where('role', '1')->get();
            foreach ($admins as $admin) {
                try {
                    \App\Helpers\NotificationHelper::send($admin, [
                        'title' => 'Refund Rejected by Vendor',
                        'message' => 'Vendor ' . $vendorName . ' has rejected a refund request for Order #' . $orderRef,
                        'type' => 'refund',
                        'url' => route('refund.show', $refund->id),
                        'icon' => 'solar:close-circle-bold-duotone',
                        'priority' => 'low'
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Refund Rejection Admin Notification Error: ' . $e->getMessage());
                }
            }

            // Notify Customer on Rejection
            $customer = \App\Models\User::find($refund->user_id);
            if ($customer) {
                try {
                    \App\Helpers\NotificationHelper::send($customer, [
                        'title' => 'Refund Request Rejected',
                        'message' => 'Your refund request for Order #' . $orderRef . ' has been rejected by the vendor.',
                        'type' => 'refund',
                        'url' => route('vendor.refund.show', $refund->id), // Adjust URL as needed for customer view
                        'icon' => 'solar:close-circle-bold-duotone',
                        'priority' => 'medium'
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Refund Rejection Customer Notification Error: ' . $e->getMessage());
                }
            }
        }

        return redirect()->back()->with('success', 'Refund request ' . ($request->action == 'initiate' ? 'initiated to admin' : 'rejected') . ' successfully.');
    }
}