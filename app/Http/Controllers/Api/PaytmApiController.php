<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Helpers\NotificationHelper;
use App\Helpers\EmailHelper;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Helpers\CampaignBudgetHelper;
use App\Services\Payment\PaytmService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PaytmApiController extends Controller
{
    public function initiatePayment(Request $request)
    {
        $orderReferenceId = $request->order_reference_id;
        $order = Order::where('order_reference_id', $orderReferenceId)->first();
        
        if (!$order) {
            $cacheKey = 'pending_order_' . $orderReferenceId;
            $orderData = Cache::get($cacheKey);
            if (!$orderData) {
                return response()->json(['status' => false, 'message' => 'Order not found'], 404);
            }
            $tempOrder = (object)[
                'id' => null,
                'order_reference_id' => $orderReferenceId,
                'user_id' => $orderData['user_id'],
                'total_cost' => $orderData['total_cost'],
                'user' => (object)['phone' => '', 'email' => ''],
            ];
            $response = PaytmService::initiatePayment($tempOrder);
        } else {
            $response = PaytmService::initiatePayment($order);
        }
        
        if ($response['status']) {
            return response()->json([
                'status' => true,
                'payment_url' => $response['payment_url'],
                'formData' => $response['formData'],
                'orderId' => $response['orderId'],
                'order_reference_id' => $orderReferenceId,
            ]);
        }
        
        return response()->json(['status' => false, 'message' => 'Paytm initiation failed'], 400);
    }

    public function verifyPayment(Request $request)
    {
        $result = PaytmService::verifyPayment($request);
        if ($result['status']) {
            return response()->json(['status' => true, 'message' => 'Payment verified', 'data' => $result['data']]);
        }
        return response()->json(['status' => false, 'message' => $result['message']], 400);
    }

    public function success(Request $request)
    {
        Log::info('Paytm success callback', $request->all());

        $orderId = $request->ORDERID ?? $request->orderId;
        if (!$orderId) {
            return response()->json(['status' => false, 'message' => 'Invalid response'], 400);
        }

        $parts = explode('-', $orderId);
        array_pop($parts);
        $orderReferenceId = implode('-', $parts);

        $result = PaytmService::verifyPayment($request);
        if (!$result['status']) {
            return response()->json(['status' => false, 'message' => 'Payment verification failed'], 400);
        }

        $order = Order::where('order_reference_id', $orderReferenceId)->first();

        if (!$order) {
            $cacheKey = 'pending_order_' . $orderReferenceId;
            $orderData = Cache::get($cacheKey);
            if (!$orderData) {
                return response()->json(['status' => false, 'message' => 'Order not found'], 404);
            }

            try {
                $order = DB::transaction(function () use ($orderData, $orderId) {
                    $order = Order::create([
                        'order_reference_id' => $orderData['order_reference_id'],
                        'transaction_id' => $orderId,
                        'user_id' => $orderData['user_id'],
                        'shipping_id' => $orderData['shipping_id'],
                        'status' => 1,
                        'payment_status' => 1,
                        'payment_mode' => $orderData['payment_mode'],
                        'currency_code' => $orderData['currency_code'],
                        'sub_total' => $orderData['sub_total'],
                        'delivery_charges' => $orderData['delivery_charges'],
                        'taxes' => $orderData['taxes'],
                        'total_cost' => $orderData['total_cost'],
                        'product_discounts' => $orderData['product_discounts'],
                        'coupon_discounts' => $orderData['coupon_discounts'],
                        'offer_discounts' => $orderData['offer_discounts'],
                        'total_discount' => $orderData['total_discount'],
                        'coupon_id' => $orderData['coupon_id'] ?? null,
                        'coupon_code' => $orderData['coupon_code'],
                        'reward_used' => $orderData['reward_used'],
                        'order_date' => now(),
                        'delivery_date' => $orderData['delivery_date'],
                    ]);

                    foreach ($orderData['summary_items'] as $item) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item['product_id'],
                            'variant_id' => $item['variant_id'],
                            'vendor_id' => $item['vendor_id'],
                            'campaign_id' => $item['campaign_id'] ?? null,
                            'quantity' => $item['qty'],
                            'price' => $item['unit_price'],
                            'discount' => $item['product_unit_discount'],
                            'offer_discount' => $item['offer_unit_discount'],
                            'campaign_discount' => $item['campaign_unit_discount'],
                            'actual_price' => $item['price_after_discounts'],
                            'total_actual_price' => $item['price_after_discounts'] * $item['qty'],
                            'vendor_tax' => $item['vendor_tax'],
                            'tax_amount' => $item['tax_amount'],
                            'status' => 1,
                            'payment_status' => 1,
                            'payment_mode' => $orderData['payment_mode'],
                            'currency' => $orderData['currency_code'],
                            'delivery_charges' => 0,
                        ]);
                    }

                    Cart::where('user_id', $orderData['user_id'])->delete();
                    Cache::forget('pending_order_' . $orderData['order_reference_id']);
                    return $order;
                });
            } catch (\Exception $e) {
                Log::error('Paytm order creation failed: ' . $e->getMessage());
                return response()->json(['status' => false, 'message' => 'Order creation failed'], 500);
            }
        } else {
            if ($order->payment_status == 1) return response()->json(['status' => true, 'message' => 'Already completed']);
            $order->update(['payment_status' => 1, 'status' => 1, 'transaction_id' => $orderId]);
            OrderItem::where('order_id', $order->id)->update(['payment_status' => 1, 'status' => 1]);
        }

        return response()->json(['status' => true, 'message' => 'Payment successful']);
    }

    public function failure(Request $request)
    {
        Log::info('Paytm failure callback', $request->all());
        return response()->json(['status' => false, 'message' => 'Payment failed'], 400);
    }
}
