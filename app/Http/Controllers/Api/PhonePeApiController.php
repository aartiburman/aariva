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
use App\Services\Logistics\NCMService;
use App\Services\Payment\PhonePeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PhonePeApiController extends Controller
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
            $response = PhonePeService::initiatePayment($tempOrder);
        } else {
            $response = PhonePeService::initiatePayment($order);
        }
        
        if ($response['status']) {
            return response()->json([
                'status' => true,
                'payment_url' => $response['payment_url'],
                'formData' => $response['formData'],
                'merchantTransactionId' => $response['merchantTransactionId'],
                'order_reference_id' => $orderReferenceId,
            ]);
        }
        
        return response()->json(['status' => false, 'message' => 'PhonePe initiation failed'], 400);
    }

    public function verifyPayment(Request $request)
    {
        $transactionId = $request->transaction_id ?? $request->merchantTransactionId;
        if (!$transactionId) {
            return response()->json(['status' => false, 'message' => 'Missing transaction ID'], 400);
        }
        
        $result = PhonePeService::verifyPayment($transactionId);
        if ($result['status']) {
            return response()->json(['status' => true, 'message' => 'Payment verified', 'data' => $result['data']]);
        }
        return response()->json(['status' => false, 'message' => $result['message']], 400);
    }

    public function success(Request $request)
    {
        Log::info('PhonePe success callback', $request->all());
        
        $transactionId = $request->merchantTransactionId ?? $request->transactionId;
        if (!$transactionId) {
            return response()->json(['status' => false, 'message' => 'Invalid response'], 400);
        }
        
        $parts = explode('-', $transactionId);
        array_pop($parts);
        $orderReferenceId = implode('-', $parts);
        
        $order = Order::where('order_reference_id', $orderReferenceId)->first();
        
        if (!$order) {
            $cacheKey = 'pending_order_' . $orderReferenceId;
            $orderData = Cache::get($cacheKey);
            if (!$orderData) {
                return response()->json(['status' => false, 'message' => 'Order not found'], 404);
            }
            
            try {
                $order = DB::transaction(function () use ($orderData, $transactionId) {
                    $userData = User::where('users.id', $orderData['user_id'])
                        ->leftJoin('countries', 'users.country_id', '=', 'countries.id')
                        ->select('users.*', 'countries.currency_code')
                        ->first();

                    $order = Order::create([
                        'order_reference_id' => $orderData['order_reference_id'],
                        'transaction_id' => $transactionId,
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

                    $ncmService = new NCMService();
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
                Log::error('PhonePe order creation failed: ' . $e->getMessage());
                return response()->json(['status' => false, 'message' => 'Order creation failed'], 500);
            }
        } else {
            if ($order->payment_status == 1) {
                return response()->json(['status' => true, 'message' => 'Payment already completed']);
            }
            $order->update(['payment_status' => 1, 'status' => 1, 'transaction_id' => $transactionId]);
            OrderItem::where('order_id', $order->id)->update(['payment_status' => 1, 'status' => 1]);
        }

        return response()->json(['status' => true, 'message' => 'Payment successful']);
    }

    public function failure(Request $request)
    {
        Log::info('PhonePe failure callback', $request->all());
        return response()->json(['status' => false, 'message' => 'Payment failed'], 400);
    }
}
