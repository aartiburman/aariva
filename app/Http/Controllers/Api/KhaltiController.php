<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\Payment\KhaltiService;
use Illuminate\Support\Facades\Log;

class KhaltiController extends Controller
{
    /**
     * Handle Khalti Verification Callback
     */
    public function verify(Request $request)
    {
        $pidx = $request->pidx;
        $orderId = $request->purchase_order_id;
        $status = $request->status;

        if (!$pidx) {
            return response()->json(['status' => false, 'message' => 'Missing pidx'], 400);
        }

        $verification = KhaltiService::verifyPayment($pidx);

        if ($verification['status'] && $verification['payment_status'] === 'Completed') {
            $order = Order::where('order_reference_id', $orderId)->first();
            
            if ($order) {
                $order->update([
                    'payment_status' => 1, // Paid
                    'transaction_id' => $pidx,
                ]);

                // Update individual order items
                $order->items()->update(['payment_status' => 1]);

                return response()->json([
                    'status' => true, 
                    'message' => 'Payment verified and order updated.',
                    'order_reference_id' => $orderId
                ]);
            }

            return response()->json(['status' => false, 'message' => 'Order not found.'], 404);
        }

        return response()->json([
            'status' => false, 
            'message' => 'Payment verification failed or incomplete.',
            'khalti_status' => $verification['payment_status'] ?? $status
        ], 400);
    }
}
