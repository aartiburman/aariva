<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Helpers\NotificationHelper;
use App\Models\Campaign;
use Carbon\Carbon;
use App\Models\WalletTransaction;

use App\Services\Logistics\NCMService;

class PayPalApiController extends Controller
{
    public function createPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'shipping_address_id' => 'required|exists:shipping_addresses,id',
            'use_wallet' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $cartItems = Cart::where('user_id', $request->user_id)->get();
        if ($cartItems->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Cart is empty'], 400);
        }

        // echo $UserData->currency_code;
// print_r($UserData->currency_code);die;

        try {
        $UserData = User::where('users.id', $request->user_id)->leftjoin('countries', 'users.country_id', '=', 'countries.id')->first();
         $currency_code = $UserData->currency_code;
            return DB::transaction(function () use ($request, $cartItems, $currency_code) {
                // Calculate Totals
                $subTotal = $cartItems->sum('total_price');
                $deliveryCharges = 0; 
                $taxes = 0; 
                $totalCost = $subTotal + $deliveryCharges + $taxes;

                $walletUsed = 0;
                $rewardUsed = 0;
                $useReward = $request->boolean('use_reward') || $request->boolean('reward_balance') || $request->boolean('use_wallet');
                if ($useReward) {
                    $user = User::find($request->user_id);
                    $available = (float) ($user->reward_balance ?? 0);
                    $tenPercentOfUser = round($available * 0.10, 2);
                    $maxByOrder = (float) $totalCost;
                    $rewardUsed = max(0, min($tenPercentOfUser, $maxByOrder));
                }

                // Create Order
                $order = Order::create([
                    'order_reference_id' => 'ORD-' . strtoupper(Str::random(10)),
                    'user_id' => $request->user_id,
                    'shipping_id' => $request->shipping_address_id,
                    'order_status' => '0', // Pending
                    'payment_status' => '0', // Unpaid
                    'payment_mode' => 'PayPal',
                    'currency_code' => $currency_code,
                    'sub_total' => $subTotal,
                    'delivery_charges' => $deliveryCharges,
                    'taxes' => $taxes,
                    'total_cost' => $totalCost,
                    'wallet_used' => $walletUsed,
                    'reward_used' => $rewardUsed,
                    'order_date' => now(),
                    'is_apply_offer' => $request->is_apply_offer ?? false,
                    'offer_id' => $request->offer_id,
                ]);

                // Create Order Items
                foreach ($cartItems as $item) {
                    // Campaign (no stacking with product offer)
                    $product = \App\Models\Product::with('offer')->find($item->product_id);
                    $today = Carbon::now();
                    $hasActiveOffer = false;
                    if (
                        $product && $product->offer &&
                        $product->offer->status == 1 &&
                        $product->offer->valid_from <= $today &&
                        $product->offer->valid_until >= $today
                    ) {
                        $hasActiveOffer = true;
                    }
                    $campaignId = null;
                    $campaignPerUnitDiscount = 0.0;
                    // Campaign discount disabled for all calculations
                    $activeCampaign = null;

                    if ($activeCampaign) {
                        $campaignId = $activeCampaign->id;
                        $unitBase = ($item->price - $item->discount);
                        $campaignPerUnitDiscount = 0.0;
                        if ($activeCampaign->offer_id && $activeCampaign->offer) {
                            if ((string)$activeCampaign->offer->type === '1' && $activeCampaign->offer->value > 0) {
                                $campaignPerUnitDiscount = round(($unitBase * $activeCampaign->offer->value) / 100, 2);
                            }
                        } else {
                            $campaignPerUnitDiscount = round(($unitBase * $activeCampaign->discount_percent) / 100, 2);
                        }
                    }
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'vendor_id' => $item->vendor_id,
                        'quantity' => $item->qty,
                        'price' => $item->price,
                        'discount' => $item->discount,
                        'campaign_id' => $campaignId,
                        'campaign_discount' => $campaignPerUnitDiscount,
                        'actual_price' => ($item->price - $item->discount - $campaignPerUnitDiscount),
                        'total_actual_price' => ($item->price - $item->discount - $campaignPerUnitDiscount) * $item->qty,
                        'status' => '0',
                        'payment_status' => '0',
                        'payment_mode' => 'PayPal',
                        'currency' => $currency_code,
                    ]);
                }

                // PayPal Integration
                $provider = new PayPalClient;
                $provider->getAccessToken();

                $payableAmount = max(0, $totalCost - $rewardUsed);
                $paypalOrder = $provider->createOrder([
                    "intent" => "CAPTURE",
                    "application_context" => [
                        "return_url" => route('api.paypal.success', ['order_id' => $order->id]),
                        "cancel_url" => route('api.paypal.cancel'),
                    ],
                    "purchase_units" => [[
                        "amount" => [
                            "currency_code" => $currency_code,
                            "value" => number_format($payableAmount, 2, '.', '')
                        ],
                        "custom_id" => $order->id
                    ]]
                ]);

                if (isset($paypalOrder['id']) && $paypalOrder['status'] == 'CREATED') {
                    $approvalUrl = '';
                    foreach ($paypalOrder['links'] as $link) {
                        if ($link['rel'] == 'approve') {
                            $approvalUrl = $link['href'];
                        }
                    }

                    return response()->json([
                        'status' => true,
                        'message' => 'PayPal order created successfully',
                        'paypal_order_id' => $paypalOrder['id'],
                        'approval_url' => $approvalUrl,
                        'order_id' => $order->id,
                        'order_reference_id' => $order->order_reference_id
                    ]);
                }

                return response()->json([
                    'status' => false,
                    'message' => 'PayPal API Error',
                    'error' => $paypalOrder
                ], 400);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function capturePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paypal_order_id' => 'required',
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $provider = new PayPalClient;
            $provider->getAccessToken();

            $response = $provider->capturePaymentOrder($request->paypal_order_id);

            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                $order = Order::find($request->order_id);
                $order->update([
                    'payment_status' => '1', // Paid
                    'order_status' => '1', // Confirmed
                ]);

                // Update OrderItems
                OrderItem::where('order_id', $order->id)->update([
                    'payment_status' => '1',
                    'status' => '1'
                ]);

                // Deduct reward used (reward wallet, after successful payment)
                $rewardUsed = (float) ($order->reward_used ?? 0);
                if ($rewardUsed > 0) {
                    $user = User::find($order->user_id);
                    $refId = 'ORDER-REWARD-' . $order->id;
                    $exists = WalletTransaction::where('reference_id', $refId)->exists();
                    if ($user && ($user->reward_balance ?? 0) >= $rewardUsed && !$exists) {
                        $user->reward_balance -= $rewardUsed;
                        $user->save();
                        WalletTransaction::create([
                            'user_id' => $user->id,
                            'amount' => $rewardUsed,
                            'type' => 'debit',
                            'description' => 'reward_used_for_order',
                            'reference_id' => $refId,
                            'status' => 'completed',
                        ]);
                        NotificationHelper::notifyCustomer($user->id, [
                            'title' => 'Reward Used Successfully',
                            'message' => 'NPR ' . number_format($rewardUsed, 2) . ' reward was used for Order #' . ($order->order_reference_id ?? $order->id) . '.',
                            'type' => 'orders',
                            'url' => '#',
                            'icon' => 'solar:wallet-linear',
                            'priority' => 'medium',
                        ]);
                    }
                }

                // Clear Cart
                Cart::where('user_id', $order->user_id)->delete();

                return response()->json([
                    'status' => true,
                    'message' => 'Payment captured successfully',
                    'data' => $response
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Payment capture failed',
                'data' => $response
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function success(Request $request)
    {
        $paypal_order_id = $request->token;
        $order_id = $request->order_id;

        if (!$paypal_order_id || !$order_id) {
            return response()->json([
                'status' => false,
                'message' => 'Missing PayPal token or order ID'
            ], 400);
        }

        try {
            $provider = new PayPalClient;
            $provider->getAccessToken();

            // Capture the payment
            $response = $provider->capturePaymentOrder($paypal_order_id);

            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                $order = Order::find($order_id);
                if (!$order) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Order not found in database'
                    ], 404);
                }

                // Update Order Status
                $order->update([
                    'payment_status' => '1', // Paid
                    'order_status' => '1',   // Confirmed
                ]);

                // Update Order Items Status
                $orderItems = OrderItem::where('order_id', $order->id)->get();
                $ncmService = new NCMService();

                foreach ($orderItems as $item) {
                    $item->update([
                        'payment_status' => '1',
                        'status' => '1'
                    ]);

                    // Create NCM Shipment for online payment after verification
                    $ncmService->createShipment($item);
                }

                // Notify Vendors
                $orderItems = OrderItem::where('order_id', $order->id)->get();
                $vendorIds = $orderItems->pluck('vendor_id')->unique();
                foreach ($vendorIds as $vendorId) {
                    NotificationHelper::notifyVendor($vendorId, [
                        'title' => 'New Order Received',
                        'message' => 'You have received a new order #' . $order->order_reference_id,
                        'type' => 'orders',
                        'url' => route('orders.details', $order->order_reference_id),
                        'icon' => 'solar:bag-bold-duotone',
                        'priority' => 'high'
                    ]);
                }

                // Wallet balance not used; no wallet deduction here

                // Deduct reward used (reward wallet, after successful payment)
                $rewardUsed = (float) ($order->reward_used ?? 0);
                if ($rewardUsed > 0) {
                    $user = User::find($order->user_id);
                    if ($user && ($user->reward_balance ?? 0) >= $rewardUsed) {
                        $user->reward_balance -= $rewardUsed;
                        $user->save();
                        WalletTransaction::create([
                            'user_id' => $user->id,
                            'amount' => $rewardUsed,
                            'type' => 'debit',
                            'description' => 'reward_used_for_order',
                            'reference_id' => 'ORDER-REWARD-' . $order->id,
                            'status' => 'completed',
                        ]);
                        NotificationHelper::notifyCustomer($user->id, [
                            'title' => 'Reward Used Successfully',
                            'message' => 'NPR ' . number_format($rewardUsed, 2) . ' reward was used for Order #' . ($order->order_reference_id ?? $order->id) . '.',
                            'type' => 'orders',
                            'url' => '#',
                            'icon' => 'solar:wallet-linear',
                            'priority' => 'medium',
                        ]);
                    }
                }

                // Campaign discount applied notification
                $hasCampaignItems = $order->items()->whereNotNull('campaign_id')->where('campaign_id', '>', 0)->exists();
                if ($hasCampaignItems) {
                    NotificationHelper::notifyCustomer($order->user_id, [
                        'title' => 'Campaign Discount Applied',
                        'message' => 'Campaign discount has been applied to your order #' . ($order->order_reference_id ?? $order->id) . '.',
                        'type' => 'promotions',
                        'url' => '#',
                        'icon' => 'solar:percent-linear',
                        'priority' => 'medium',
                    ]);
                }

                // Clear the user's cart
                Cart::where('user_id', $order->user_id)->delete();

                return response()->json([
                    'status' => true,
                    'message' => 'Payment successful and order placed',
                    'paypal_order_id' => $paypal_order_id,
                    'order_details' => [
                        'order_id' => $order->id,
                        'order_reference_id' => $order->order_reference_id,
                        'total_amount' => $order->total_cost,
                        'payment_status' => 'Paid'
                    ]
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Payment capture failed',
                'paypal_response' => $response
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong during capture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel()
    {
        return response()->json([
            'status' => false,
            'message' => 'Payment cancelled by user'
        ]);
    }
}
