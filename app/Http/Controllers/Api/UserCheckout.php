<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ImageHelper;
use App\Helpers\EmailHelper;
use App\Helpers\PriceHelper;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ProductVariant;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Helpers\NotificationHelper;
use App\Models\Campaign;
use App\Models\WalletTransaction;
use App\Models\Offer;
use App\Models\Coupon;

use App\Services\Logistics\NCMService;
use App\Helpers\PriceCalculationHelper;
use App\Helpers\CampaignBudgetHelper;
use App\Services\Payment\KhaltiService;
use App\Services\Payment\PhonePeService;
use App\Services\Payment\PaytmService;

class UserCheckout extends Controller
{
    private function resolveCouponCode(Request $request)
    {
        return $request->coupon_code
            ?? $request->coupon
            ?? $request->coupan_code
            ?? $request->coupan
            ?? $request->input('couponCode');
    }

    public function sync_ncm_status(Request $request, $reference_id = null)
    {
        Log::info('NCM Webhook/Sync Request Received', $request->all());
        
        // Handle Test Webhook
        if ($request->has('test') && $request->test) {
            Log::info('NCM Test Webhook Received');
            return response()->json(['status' => 'success', 'message' => 'Test webhook received']);
        }

        // 1. Handle Webhook Payload from NCM
        $ncmService = new NCMService();
        $updatedCount = 0;

        // Handle Single Order Webhook (order_id)
        if ($request->has('order_id') && $request->has('status')) {
            $trackingId = $request->order_id;
            $ncmStatus = $request->status;
            Log::info('Processing NCM Single Order Webhook', ['order_id' => $trackingId, 'status' => $ncmStatus]);

            $item = OrderItem::where('tracking_id', $trackingId)->where('logistics_provider', 'NCM')->first();
            if ($item) {
                $newLogisticsStatus = $ncmService->mapStatus($ncmStatus);
                $newNumericStatus = $ncmService->mapNumericStatus($ncmStatus);

                $updateData = [];
                if ($newLogisticsStatus && $newLogisticsStatus !== $item->logistics_status) {
                    $updateData['logistics_status'] = $newLogisticsStatus;
                }
                if ($newNumericStatus !== null && $newNumericStatus != $item->status) {
                    $updateData['status'] = $newNumericStatus;
                    if ($newNumericStatus == 3 && strtoupper($item->payment_mode) === 'COD') {
                        $updateData['payment_status'] = 'Completed';
                    }
                }

                if (!empty($updateData)) {
                    $item->update($updateData);
                    $updatedCount++;
                }

                Log::info('NCM Single Order Webhook Processed', ['item_id' => $item->id, 'updated' => !empty($updateData)]);
               
                return response()->json(['status' => 'received', 'message' => 'Webhook processed', 'updated' => $updatedCount]);
            }
        }

        // Handle Bulk Order Webhook (order_ids)


        if ($request->has('order_ids') && $request->has('status')) {
            $trackingIds = $request->order_ids;
            $ncmStatus = $request->status;
            Log::info('Processing NCM Bulk Order Webhook', ['order_ids' => $trackingIds, 'status' => $ncmStatus]);

            foreach ($trackingIds as $trackingId) {
                $item = OrderItem::where('tracking_id', $trackingId)->where('logistics_provider', 'NCM')->first();
                if ($item) {
                    $newLogisticsStatus = $ncmService->mapStatus($ncmStatus);
                    $newNumericStatus = $ncmService->mapNumericStatus($ncmStatus);

                    $updateData = [];
                    if ($newLogisticsStatus && $newLogisticsStatus !== $item->logistics_status) {
                        $updateData['logistics_status'] = $newLogisticsStatus;
                    }
                    if ($newNumericStatus !== null && $newNumericStatus != $item->status) {
                        $updateData['status'] = $newNumericStatus;
                        if ($newNumericStatus == 3 && strtoupper($item->payment_mode) === 'COD') {
                            $updateData['payment_status'] = 'Completed';
                        }
                    }

                    if (!empty($updateData)) {
                        $item->update($updateData);
                        $updatedCount++;
                    }
                }
            }

            Log::info('NCM Bulk Order Webhook Processed', ['updated' => $updatedCount]);
            return response()->json(['status' => 'received', 'message' => 'Bulk webhook processed', 'updated' => $updatedCount]);
        }

        // Handle Legacy Webhook Format (id) - for backward compatibility
        if ($request->has('id') && $request->has('status')) {
            $trackingId = $request->id;
            $ncmStatus = $request->status;
            Log::info('Processing NCM Legacy Webhook', ['id' => $trackingId, 'status' => $ncmStatus]);

            $item = OrderItem::where('tracking_id', $trackingId)->where('logistics_provider', 'NCM')->first();
            if ($item) {
                $newLogisticsStatus = $ncmService->mapStatus($ncmStatus);
                $newNumericStatus = $ncmService->mapNumericStatus($ncmStatus);

                $updateData = [];
                if ($newLogisticsStatus && $newLogisticsStatus !== $item->logistics_status) {
                    $updateData['logistics_status'] = $newLogisticsStatus;
                }
                if ($newNumericStatus !== null && $newNumericStatus != $item->status) {
                    $updateData['status'] = $newNumericStatus;
                    if ($newNumericStatus == 3 && strtoupper($item->payment_mode) === 'COD') {
                        $updateData['payment_status'] = 'Completed';
                    }
                }

                if (!empty($updateData)) {
                    $item->update($updateData);
                    $updatedCount++;
                }

                Log::info('NCM Legacy Webhook Processed', ['item_id' => $item->id, 'updated' => !empty($updateData)]);
                return response()->json(['status' => true, 'message' => 'Webhook processed']);
            }
        }

        // 2. Handle Manual Sync via Reference ID
        $reference_id = $reference_id ?? $request->reference_id;
        if (!$reference_id) {
            return response()->json(['status' => false, 'message' => 'Reference ID or Webhook data is required'], 400);
        }

        $order = Order::where('order_reference_id', $reference_id)->first();
        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }
        $items = $order->items()->where('logistics_provider', 'NCM')->get();

        foreach ($items as $item) {
            $trackingData = $ncmService->trackShipment($item->tracking_id);
            if ($trackingData && isset($trackingData['status'])) {
                $ncmStatus = $trackingData['status'];
                $newLogisticsStatus = $ncmService->mapStatus($ncmStatus);
                $newNumericStatus = $ncmService->mapNumericStatus($ncmStatus);

                $updateData = [];
                if ($newLogisticsStatus && $newLogisticsStatus !== $item->logistics_status) {
                    $updateData['logistics_status'] = $newLogisticsStatus;
                }

                if ($newNumericStatus !== null && $newNumericStatus != $item->status) {
                    $updateData['status'] = $newNumericStatus;

                    // If delivered, also mark payment as completed for COD
                    if ($newNumericStatus == 3 && strtoupper($item->payment_mode) === 'COD') {
                        $updateData['payment_status'] = 'Completed';
                    }
                }

                if (!empty($updateData)) {
                    $item->update($updateData);
                    $updatedCount++;
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => $updatedCount > 0 ? "Synced successfully. {$updatedCount} item(s) updated." : "All statuses are up to date."
        ]);
    }

    public function place_order(Request $request)
    {
        Log::info('place_order function called', ['request' => $request->all()]);
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'shipping_address_id' => 'nullable|exists:shipping_addresses,id',
            'name' => 'required_without:shipping_address_id|string|max:255',
            'phone' => 'required_without:shipping_address_id|string|max:20',
            'address' => 'required_without:shipping_address_id|string',
            'city_id' => 'required_without:shipping_address_id|exists:cities,id',
            'payment_mode' => 'required|in:COD,Card,PhonePe,Paytm',
            'coupon_code' => 'nullable|string',
            'coupon' => 'nullable|string',
            'offer_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cartItems = Cart::where('user_id', $request->user_id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Cart is empty'
            ]);
        }

        $userData = User::where('users.id', $request->user_id)
            ->leftJoin('countries', 'users.country_id', '=', 'countries.id')
            ->select('users.*', 'countries.currency_code')
            ->first();

        $currency_code = $userData->currency_code ?? 'NPR';

        // SHIPPING ADDRESS
        $cityId = $request->city_id;
        if ($request->shipping_address_id) {
            $shippingId = $request->shipping_address_id;
            $shippingAddress = ShippingAddress::find($shippingId);
            if ($shippingAddress) {
                $cityId = $shippingAddress->city_id;
            }
        } else {
            $shipping = ShippingAddress::create([
                'user_id' => $request->user_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city_id' => $request->city_id,
                'state_id' => $request->state_id,
                'zip' => $request->zip,
                'country_id' => $request->country_id,
            ]);
            $shippingId = $shipping->id;
            $cityId = $request->city_id;
        }

        if (!$cityId && $request->user_id) {
            $lastAddress = ShippingAddress::where('user_id', $request->user_id)->latest()->first();
            if ($lastAddress) {
                $cityId = $lastAddress->city_id;
            } else {
                $user = User::find($request->user_id);
                $cityId = $user->city_id ?? null;
            }
        }

        $summary = PriceCalculationHelper::calculateSummary($cartItems, $this->resolveCouponCode($request), $cityId);

        $rewardUsed = 0;
        if ($request->boolean('use_reward')) {
            $user = User::find($request->user_id);
            $availableReward = $user->reward_balance ?? 0;
            $maxRewardAllowed = round($summary['total_cost'] * 0.10, 2);
            $rewardUsed = min($availableReward, $maxRewardAllowed);
        }

        $totalCostAfterRewards = max(0, $summary['total_cost'] - $rewardUsed);

        $maxDeliveryDays = 3;
        foreach ($summary['items'] as $item) {
            $vendor = User::find($item['vendor_id']);
            $days = $vendor->delivery_days ?? '2-3';
            if (preg_match_all('/\d+/', $days, $matches)) {
                $currentMax = max($matches[0]);
                if ($currentMax > $maxDeliveryDays) {
                    $maxDeliveryDays = (int)$currentMax;
                }
            }
        }
        $expectedDeliveryDate = now()->addDays($maxDeliveryDays);

        $orderReferenceId = 'ORD-' . strtoupper(Str::random(10));
        $transactionId = 'TXN-' . strtoupper(Str::random(12));

        // For COD: create order immediately
        if (in_array(strtoupper($request->payment_mode), ['COD', 'CARD'])) {
            $isCard = strtoupper($request->payment_mode) === 'CARD';
            $paymentStatus = $isCard ? 1 : 0;
            $orderStatus = 0;

            $savedCard = $isCard ? $this->saveCardIfPresent($request) : null;
            $cardId = $savedCard ? $savedCard->id : null;
            $cardType = $isCard ? ($request->card_type ?? null) : null;

            return DB::transaction(function () use ($request, $cartItems, $currency_code, $userData, $cityId, $summary, $rewardUsed, $totalCostAfterRewards, $expectedDeliveryDate, $orderReferenceId, $transactionId, $shippingId, $paymentStatus, $orderStatus, $cardId, $cardType) {

                $order = Order::create([
                    'order_reference_id' => $orderReferenceId,
                    'transaction_id' => $transactionId,
                    'user_id' => $request->user_id,
                    'shipping_id' => $shippingId,
                    'status' => $orderStatus,
                    'payment_status' => $paymentStatus,
                    'payment_mode' => $request->payment_mode,
                    'card_type' => $cardType,
                    'card_id' => $cardId,
                    'currency_code' => $currency_code,
                    'sub_total' => $summary['sub_total'],
                    'delivery_charges' => $summary['delivery_charges'],
                    'taxes' => $summary['taxes'],
                    'total_cost' => $totalCostAfterRewards,
                    'product_discounts' => $summary['product_discounts'],
                    'coupon_discounts' => $summary['coupon_discounts'],
                    'offer_discounts' => $summary['offer_discounts'],
                    'total_discount' => $summary['total_discount'],
                    'coupon_id' => $summary['coupon_id'] ?? null,
                    'coupon_code' => $this->resolveCouponCode($request),
                    'reward_used' => $rewardUsed,
                    'order_date' => now(),
                    'delivery_date' => $expectedDeliveryDate,
                ]);

                $delivery_charges = $summary['delivery_charges'];
                $item_count = count($summary['items']);
                $per_item_delivery_charge = $item_count > 0 ? $delivery_charges / $item_count : 0;

                $total_before_global_discounts = collect($summary['items'])->sum(function ($item) {
                    return $item['price_after_discounts'] * $item['qty'];
                });
                $coupon_discount_total = (float) ($summary['coupon_discounts'] ?? 0.0);
                $reward_discount_total = (float) ($rewardUsed ?? 0.0);

                foreach ($summary['items'] as $item) {
                    $line_subtotal = $item['price_after_discounts'] * $item['qty'];
                    $pro_rated_coupon = 0.0;
                    $pro_rated_reward = 0.0;
                    if ($total_before_global_discounts > 0) {
                        $pro_rated_coupon = round(($line_subtotal / $total_before_global_discounts) * $coupon_discount_total, 2);
                        $pro_rated_reward = round(($line_subtotal / $total_before_global_discounts) * $reward_discount_total, 2);
                    }
                    $final_line_total = max(0, $line_subtotal + $item['tax_amount'] + $per_item_delivery_charge - $pro_rated_coupon - $pro_rated_reward);

                    $orderItem = OrderItem::create([
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
                        'total_actual_price' => round($final_line_total, 2),
                        'vendor_tax' => $item['vendor_tax'],
                        'tax_amount' => $item['tax_amount'],
                        'status' => $orderStatus,
                        'payment_status' => $paymentStatus,
                        'payment_mode' => $request->payment_mode,
                        'card_id' => $cardId,
                        'currency' => $currency_code,
                        'delivery_charges' => $per_item_delivery_charge,
                    ]);
                    
                    $orderItem->load(['order', 'order.user', 'order.shippingAddress', 'order.shippingAddress.city', 'vendor', 'vendor.city', 'product']);
                    Log::info('place_order immediate: About to call NCM createShipment', [
                        'order_item_id' => $orderItem->id,
                        'order_item' => $orderItem->toArray()
                    ]);
                    try {
                        $ncmService = new NCMService();
                        $ncmResult = $ncmService->createShipment($orderItem);
                        Log::info('place_order immediate: NCM createShipment result', ['result' => $ncmResult]);
                    } catch (\Exception $e) {
                        Log::error('place_order immediate: NCM createShipment exception: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(), [
                            'exception' => $e,
                            'trace' => $e->getTraceAsString()
                        ]);
                    }

                    if (!empty($item['campaign_id']) && (float)$item['campaign_unit_discount'] > 0) {
                        $discountUsage = (float)$item['campaign_unit_discount'] * (int)$item['qty'];
                        CampaignBudgetHelper::applyDiscountUsage((int)$item['campaign_id'], (int)$item['vendor_id'], $discountUsage);
                    }

                    $vendor = User::find($item['vendor_id']);
                    if ($vendor) {
                        if (!empty(trim((string)$vendor->email))) {
                            EmailHelper::send(
                                $vendor->email,
                                'New Order Received - #' . $order->order_reference_id,
                                '',
                                'emails.vendor-order-notification',
                                [
                                    'vendor_name' => $vendor->name,
                                    'customer_name' => $userData->name ?? 'Customer',
                                    'order_id' => $order->id,
                                    'product_name' => Product::where('id', $item['product_id'])->value('name') ?? 'Product',
                                    'product_image' => ImageHelper::getProductImage(
                                        (ProductVariant::where('id', $item['variant_id'])->value('image') ?? null)
                                            ?: (Product::where('id', $item['product_id'])->value('thumbnail') ?? null)
                                    ),
                                    'quantity' => $orderItem->quantity,
                                    'earnings' => PriceHelper::formatPrice($orderItem->total_actual_price),
                                    'dashboard_url' => config('app.url') . '/vendor/dashboard'
                                ]
                            );
                        }
                        NotificationHelper::notifyVendor($vendor->id, [
                            'title' => 'New Order Received',
                            'message' => 'You have received a new order #' . $order->order_reference_id . ' for ' . (Product::where('id', $item['product_id'])->value('name') ?? 'product'),
                            'type' => 'orders',
                            'url' => 'orders-details/' . $order->order_reference_id,
                            'icon' => 'solar:cart-large-minimalistic-bold-duotone'
                        ]);
                    }
                }

                NotificationHelper::notifyAdmins([
                    'title' => 'New Marketplace Order',
                    'message' => 'A new order #' . $order->order_reference_id . ' has been placed by ' . ($userData->name ?? 'Customer'),
                    'type' => 'orders',
                    'url' => 'orders-details/' . $order->order_reference_id,
                    'icon' => 'solar:cart-check-bold-duotone'
                ]);

                $admins = User::where('role', '1')->get();
                foreach ($admins as $admin) {
                    if (!empty(trim((string)$admin->email))) {
                        EmailHelper::send(
                            $admin->email,
                            'New Marketplace Order - #' . $order->order_reference_id,
                            '',
                            'emails.admin-order-notification',
                            [
                                'customer_name' => $userData->name ?? 'Customer',
                                'order_id' => $order->order_reference_id,
                                'total_cost' => PriceHelper::formatPrice($order->total_cost),
                                'payment_mode' => $order->payment_mode,
                                'admin_url' => config('app.url') . '/admin/dashboard'
                            ]
                        );
                    }
                }

                $customerItems = [];
                foreach ($summary['items'] as $item) {
                    $p = Product::find($item['product_id']);
                    $v = ProductVariant::find($item['variant_id']);
                    $customerItems[] = [
                        'name' => $p->name ?? 'Product',
                        'qty' => $item['qty'],
                        'price' => PriceHelper::formatPrice($item['price_after_discounts']),
                        'image' => ImageHelper::getProductImage(($v->image ?? null) ?: ($p->thumbnail ?? null)),
                    ];
                }
                if ($order->user && !empty(trim((string)$order->user->email))) {
                    EmailHelper::send(
                        $order->user->email,
                        'Order Confirmed - #' . $order->order_reference_id,
                        '',
                        'emails.order-placed',
                        [
                            'customer_name' => $order->user->name ?? 'Customer',
                            'order_id' => $order->order_reference_id,
                            'items' => $customerItems,
                            'sub_total' => PriceHelper::formatPrice($order->sub_total),
                            'delivery_charges' => PriceHelper::formatPrice($order->delivery_charges),
                            'discount' => PriceHelper::formatPrice($order->total_discount),
                            'total_cost' => PriceHelper::formatPrice($order->total_cost),
                            'order_url' => config('app.url') . '/api/get-order-detail?user_id=' . $order->user_id . '&order_id=' . $order->id
                        ]
                    );
                }

                Cart::where('user_id', $request->user_id)->delete();

                $finalOrder = Order::with('items')->find($order->id);
                return response()->json([
                    'status' => true,
                    'message' => 'Order placed successfully',
                    'order_id' => $finalOrder->id,
                    'order_reference_id' => $finalOrder->order_reference_id,
                    'order' => $finalOrder,
                    'order_items' => $finalOrder->items,
                    'coupon_details' => [
                        'coupon_id' => $finalOrder->coupon_id,
                        'coupon_code' => $finalOrder->coupon_code,
                        'discount' => $finalOrder->coupon_discounts
                    ]
                ], 201);
            });
        }

        // For Khalti/eSewa: Store order details in cache and initiate payment
        $orderData = [
            'order_reference_id' => $orderReferenceId,
            'transaction_id' => $transactionId,
            'user_id' => $request->user_id,
            'shipping_id' => $shippingId,
            'payment_mode' => $request->payment_mode,
            'currency_code' => $currency_code,
            'sub_total' => $summary['sub_total'],
            'delivery_charges' => $summary['delivery_charges'],
            'taxes' => $summary['taxes'],
            'total_cost' => $totalCostAfterRewards,
            'product_discounts' => $summary['product_discounts'],
            'coupon_discounts' => $summary['coupon_discounts'],
            'offer_discounts' => $summary['offer_discounts'],
            'total_discount' => $summary['total_discount'],
            'coupon_id' => $summary['coupon_id'] ?? null,
            'coupon_code' => $this->resolveCouponCode($request),
            'reward_used' => $rewardUsed,
            'delivery_date' => $expectedDeliveryDate,
            'summary_items' => $summary['items'],
            'cart_items' => $cartItems->toArray(),
            'card_holder_name' => $request->card_holder_name,
            'card_number' => $request->card_number,
            'expiry_month' => $request->expiry_month,
            'expiry_year' => $request->expiry_year,
            'card_type' => $request->card_type,
        ];

        $cacheKey = 'pending_order_' . $orderReferenceId;
        Log::info('place_order: Storing order data in cache', [
            'cache_key' => $cacheKey,
            'order_data' => $orderData
        ]);
        \Illuminate\Support\Facades\Cache::put($cacheKey, $orderData, 3600);
        Log::info('place_order: Order data stored in cache successfully', ['cache_key' => $cacheKey]);

        $tempOrder = (object)[
            'id' => null,
            'order_reference_id' => $orderReferenceId,
            'transaction_id' => $transactionId,
            'user_id' => $request->user_id,
            'total_cost' => $totalCostAfterRewards,
            'payment_mode' => $request->payment_mode,
            'user' => $userData,
        ];

        try {
            if (strtoupper($request->payment_mode) === 'KHALTI') {
                Log::info("Initiating Khalti for Pending Order (Cart): {$orderReferenceId}, Amount: {$totalCostAfterRewards}");
                $khaltiResponse = KhaltiService::initiatePayment($tempOrder, $userData);
                if ($khaltiResponse['status']) {
                    $gateway = \App\Models\PaymentGateway::where('slug', 'khalti')->first();
                    return response()->json([
                        'status' => true,
                        'message' => 'Order placed. Redirecting to Khalti.',
                        'payment_url' => $khaltiResponse['payment_url'],
                        'pidx' => $khaltiResponse['pidx'],
                        'order_reference_id' => $orderReferenceId,
                        'verify_url' => $gateway?->success_url ?? config('app.url') . '/api/khalti/verify',
                        'payload' => $khaltiResponse['payload']
                    ], 201);
                }
                return response()->json(['status' => false, 'message' => 'Khalti Error: ' . $khaltiResponse['message'], 'payload' => $khaltiResponse['payload']], 400);
            }

            if (strtoupper($request->payment_mode) === 'PHONEPE') {
                $phonePeResponse = PhonePeService::initiatePayment($tempOrder);
                if ($phonePeResponse['status']) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Order placed. Redirecting to PhonePe.',
                        'payment_url' => $phonePeResponse['payment_url'],
                        'formData' => $phonePeResponse['formData'],
                        'merchantTransactionId' => $phonePeResponse['merchantTransactionId'],
                        'order_reference_id' => $orderReferenceId
                    ], 201);
                }
                return response()->json(['status' => false, 'message' => 'PhonePe Error: ' . ($phonePeResponse['message'] ?? 'Unknown error')], 400);
            }

            if (strtoupper($request->payment_mode) === 'PAYTM') {
                $paytmResponse = PaytmService::initiatePayment($tempOrder);
                if ($paytmResponse['status']) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Order placed. Redirecting to Paytm.',
                        'payment_url' => $paytmResponse['payment_url'],
                        'formData' => $paytmResponse['formData'],
                        'orderId' => $paytmResponse['orderId'],
                        'order_reference_id' => $orderReferenceId
                    ], 201);
                }
                return response()->json(['status' => false, 'message' => 'Paytm Error: ' . ($paytmResponse['message'] ?? 'Unknown error')], 400);
            }
        } catch (\Exception $e) {
            Log::error('place_order Payment Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['status' => false, 'message' => 'Internal Server Error'], 500);
        }
    }
    public function buy_now(Request $request)
    {
        Log::info('buy_now function called', ['request' => $request->all()]);
        
        // 1. Enhanced Validation (Added missing fields that you use later in the code)
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'payment_mode' => 'required|in:COD,Card,PhonePe,Paytm',
            'shipping_address_id' => 'nullable|exists:shipping_addresses,id',
            // Address fields required if no shipping_address_id is provided
            'name' => 'required_without:shipping_address_id|string|max:255',
            'email' => 'required_without:shipping_address_id|email|max:255',
            'phone' => 'required_without:shipping_address_id|string|max:20',
            'address' => 'required_without:shipping_address_id|string',
            'city_id' => 'required_without:shipping_address_id|exists:cities,id',
            'state_id' => 'required_without:shipping_address_id|exists:states,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $product = Product::find($request->product_id);
        $variant = ProductVariant::find($request->variant_id);

        if (!$variant || $variant->stock < $request->quantity) {
            return response()->json(['status' => false, 'message' => 'Insufficient stock']);
        }

        $userData = User::leftJoin('countries', 'users.country_id', '=', 'countries.id')
            ->where('users.id', $request->user_id)
            ->select('users.*', 'countries.currency_code')
            ->first();

        $currency_code = $userData->currency_code ?? 'NPR';

        try {
            /* === PRE-CALCULATION LOGIC === */
            $cityId = $request->city_id;
            if ($request->shipping_address_id) {
                $shippingAddress = ShippingAddress::find($request->shipping_address_id);
                if ($shippingAddress) {
                    $shippingId = $shippingAddress->id;
                    $cityId = $shippingAddress->city_id;
                }
            } else {
                $shipping = ShippingAddress::create([
                    'user_id' => $request->user_id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'city_id' => $request->city_id,
                    'state_id' => $request->state_id,
                    'zip' => $request->zip,
                    'country_id' => $request->country_id,
                ]);
                $shippingId = $shipping->id;
            }

            if (!$cityId && $request->user_id) {
                $lastAddress = ShippingAddress::where('user_id', $request->user_id)->latest()->first();
                if ($lastAddress) {
                    $cityId = $lastAddress->city_id;
                } else {
                    $user = User::find($request->user_id);
                    $cityId = $user->city_id ?? null;
                }
            }

            $couponCode = $this->resolveCouponCode($request);
            $tempItem = (object)['product_id' => $product->id, 'variant_id' => $variant->id, 'qty' => $request->quantity];
            $summary = PriceCalculationHelper::calculateSummary([$tempItem], $couponCode, $cityId);

            $rewardUsed = 0;
            if ($request->boolean('use_reward') && $userData) {
                $availableReward = $userData->reward_balance ?? 0;
                $maxRewardAllowed = round($summary['total_cost'] * 0.10, 2);
                $rewardUsed = min($availableReward, $maxRewardAllowed);
            }

            $totalCostAfterRewards = max(0, $summary['total_cost'] - $rewardUsed);

            $maxDeliveryDays = 3;
            $vendor = User::find($product->vendor_id);
            $days = $vendor->delivery_days ?? '2-3';
            if (preg_match_all('/\d+/', $days, $matches)) {
                $currentMax = max($matches[0]);
                if ($currentMax > $maxDeliveryDays) {
                    $maxDeliveryDays = (int)$currentMax;
                }
            }
            $expectedDeliveryDate = now()->addDays($maxDeliveryDays);

            $orderReferenceId = 'ORD-' . strtoupper(Str::random(10));
            $transactionId = 'TXN-' . strtoupper(Str::random(12));

            // For COD/Card: create order immediately
            if (in_array(strtoupper($request->payment_mode), ['COD', 'CARD'])) {
                $isCard = strtoupper($request->payment_mode) === 'CARD';
                $paymentStatus = $isCard ? 1 : 0;
                $orderStatus = 0;

                $savedCard = $isCard ? $this->saveCardIfPresent($request) : null;
                $cardId = $savedCard ? $savedCard->id : null;
                $cardType = $isCard ? ($request->card_type ?? null) : null;

                $result = DB::transaction(function () use ($request, $product, $variant, $currency_code, $userData, $cityId, $couponCode, $summary, $rewardUsed, $totalCostAfterRewards, $expectedDeliveryDate, $orderReferenceId, $transactionId, $shippingId, $vendor, $paymentStatus, $orderStatus, $cardId, $cardType) {

                    /* === CREATE ORDER === */
                    $order = Order::create([
                        'order_reference_id' => $orderReferenceId,
                        'transaction_id' => $transactionId,
                        'user_id' => $request->user_id,
                        'shipping_id' => $shippingId,
                        'status' => $orderStatus,
                        'payment_status' => $paymentStatus,
                        'payment_mode' => $request->payment_mode,
                        'card_type' => $cardType,
                        'card_id' => $cardId,
                        'currency_code' => $currency_code,
                        'sub_total' => $summary['sub_total'],
                        'delivery_charges' => $summary['delivery_charges'],
                        'taxes' => $summary['taxes'],
                        'total_cost' => $totalCostAfterRewards,
                        'product_discounts' => $summary['product_discounts'],
                        'coupon_discounts' => $summary['coupon_discounts'],
                        'offer_discounts' => $summary['offer_discounts'],
                        'total_discount' => $summary['total_discount'],
                        'coupon_id' => $summary['coupon_id'] ?? null,
                        'coupon_code' => $couponCode,
                        'reward_used' => $rewardUsed,
                        'order_date' => now(),
                        'delivery_date' => $expectedDeliveryDate,
                    ]);

                    $delivery_charges = $summary['delivery_charges'];
                    $item_count = count($summary['items']);
                    $per_item_delivery_charge = $item_count > 0 ? $delivery_charges / $item_count : 0;

                    $total_before_global_discounts = collect($summary['items'])->sum(function ($item) {
                        return $item['price_after_discounts'] * $item['qty'];
                    });
                    $coupon_discount_total = (float) ($summary['coupon_discounts'] ?? 0.0);
                    $reward_discount_total = (float) ($rewardUsed ?? 0.0);

                    /* === CREATE ITEMS & STOCK === */
                    $item = $summary['items'][0];
                    $line_subtotal = $item['price_after_discounts'] * $item['qty'];

                    $pro_rated_coupon = 0.0;
                    $pro_rated_reward = 0.0;
                    if ($total_before_global_discounts > 0) {
                        $pro_rated_coupon = round(($line_subtotal / $total_before_global_discounts) * $coupon_discount_total, 2);
                        $pro_rated_reward = round(($line_subtotal / $total_before_global_discounts) * $reward_discount_total, 2);
                    }

                    $final_line_total = max(0, $line_subtotal + $item['tax_amount'] + $per_item_delivery_charge - $pro_rated_coupon - $pro_rated_reward);

                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'vendor_id' => $product->vendor_id,
                        'campaign_id' => $item['campaign_id'] ?? null,
                        'quantity' => $request->quantity,
                        'price' => $item['unit_price'],
                        'discount' => $item['product_unit_discount'],
                        'offer_discount' => $item['offer_unit_discount'],
                        'campaign_discount' => $item['campaign_unit_discount'],
                        'actual_price' => $item['price_after_discounts'],
                        'total_actual_price' => round($final_line_total, 2),
                        'vendor_tax' => $item['vendor_tax'],
                        'tax_amount' => $item['tax_amount'],
                        'status' => $orderStatus,
                        'payment_status' => $paymentStatus,
                        'payment_mode' => $request->payment_mode,
                        'card_id' => $cardId,
                        'currency' => $currency_code,
                        'delivery_charges' => $per_item_delivery_charge,
                    ]);
                    
                    $orderItem->load(['order', 'order.user', 'order.shippingAddress', 'order.shippingAddress.city', 'vendor', 'vendor.city', 'product']);
                    Log::info('buy_now immediate: About to call NCM createShipment', [
                        'order_item_id' => $orderItem->id,
                        'order_item' => $orderItem->toArray()
                    ]);
                    try {
                        $ncmService = new NCMService();
                        $ncmResult = $ncmService->createShipment($orderItem);
                        Log::info('buy_now immediate: NCM createShipment result', ['result' => $ncmResult]);
                    } catch (\Exception $e) {
                        Log::error('buy_now immediate: NCM createShipment exception: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(), [
                            'exception' => $e,
                            'trace' => $e->getTraceAsString()
                        ]);
                    }

                    if (!empty($item['campaign_id']) && (float)$item['campaign_unit_discount'] > 0) {
                        $discountUsage = (float)$item['campaign_unit_discount'] * (int)$item['qty'];
                        CampaignBudgetHelper::applyDiscountUsage((int)$item['campaign_id'], (int)$product->vendor_id, $discountUsage);
                    }

                    $variant->decrement('stock', $request->quantity);

                    /* === NOTIFICATIONS === */
                    if ($vendor) {
                        if (!empty(trim((string)$vendor->email))) {
                            EmailHelper::send(
                                $vendor->email,
                                'New Order Received - #' . $order->order_reference_id,
                                '',
                                'emails.vendor-order-notification',
                                [
                                    'vendor_name' => $vendor->name,
                                    'customer_name' => $userData->name ?? 'Customer',
                                    'order_id' => $order->id,
                                    'product_name' => Product::where('id', $product->id)->value('name') ?? 'Product',
                                    'product_image' => ImageHelper::getProductImage(
                                        (ProductVariant::where('id', $variant->id)->value('image') ?? null)
                                            ?: (Product::where('id', $product->id)->value('thumbnail') ?? null)
                                    ),
                                    'quantity' => $orderItem->quantity,
                                    'earnings' => PriceHelper::formatPrice($orderItem->total_actual_price),
                                    'dashboard_url' => config('app.url') . '/vendor/dashboard'
                                ]
                            );
                        }

                        NotificationHelper::notifyVendor($vendor->id, [
                            'title' => 'New Order Received',
                            'message' => 'You have received a new order #' . $order->order_reference_id . ' for ' . (Product::where('id', $product->id)->value('name') ?? 'product'),
                            'type' => 'orders',
                            'url' => 'orders-details/' . $order->order_reference_id,
                            'icon' => 'solar:cart-large-minimalistic-bold-duotone'
                        ]);
                    }

                    NotificationHelper::notifyAdmins([
                        'title' => 'New Marketplace Order',
                        'message' => 'A new order #' . $order->order_reference_id . ' has been placed by ' . ($userData->name ?? 'Customer'),
                        'type' => 'orders',
                        'url' => 'orders-details/' . $order->order_reference_id,
                        'icon' => 'solar:cart-check-bold-duotone'
                    ]);

                    $admins = User::where('role', '1')->get();
                    foreach ($admins as $admin) {
                        if (!empty(trim((string)$admin->email))) {
                            EmailHelper::send(
                                $admin->email,
                                'New Marketplace Order - #' . $order->order_reference_id,
                                '',
                                'emails.admin-order-notification',
                                [
                                    'customer_name' => $userData->name ?? 'Customer',
                                    'order_id' => $order->order_reference_id,
                                    'total_cost' => PriceHelper::formatPrice($order->total_cost),
                                    'payment_mode' => $order->payment_mode,
                                    'admin_url' => config('app.url') . '/admin/dashboard'
                                ]
                            );
                        }
                    }

                    $customerItems = [];
                    $p = Product::find($product->id);
                    $v = ProductVariant::find($variant->id);
                    $customerItems[] = [
                        'name' => $p->name ?? 'Product',
                        'qty' => $request->quantity,
                        'price' => PriceHelper::formatPrice($item['price_after_discounts']),
                        'image' => ImageHelper::getProductImage(($v->image ?? null) ?: ($p->thumbnail ?? null)),
                    ];

                    if ($order->user && !empty(trim((string)$order->user->email))) {
                        EmailHelper::send(
                            $order->user->email,
                            'Order Confirmed - #' . $order->order_reference_id,
                            '',
                            'emails.order-placed',
                            [
                                'customer_name' => $order->user->name ?? 'Customer',
                                'order_id' => $order->order_reference_id,
                                'items' => $customerItems,
                                'sub_total' => PriceHelper::formatPrice($order->sub_total),
                                'delivery_charges' => PriceHelper::formatPrice($order->delivery_charges),
                                'discount' => PriceHelper::formatPrice($order->total_discount),
                                'total_cost' => PriceHelper::formatPrice($order->total_cost),
                                'order_url' => config('app.url') . '/api/get-order-detail?user_id=' . $order->user_id . '&order_id=' . $order->id
                            ]
                        );
                    }

                    return $order;
            });
            
            $order = $result;
            $this->saveCardIfPresent($request);
            $finalOrder = Order::with('items')->find($order->id);

            return response()->json(['status' => true, 'message' => 'Order placed successfully', 'order_id' => $finalOrder->id, 'order' => $finalOrder, 'order_items' => $finalOrder->items], 201);
            }

            // For Khalti/eSewa: Store order details in cache and initiate payment
            $orderData = [
                'order_reference_id' => $orderReferenceId,
                'transaction_id' => $transactionId,
                'user_id' => $request->user_id,
                'shipping_id' => $shippingId,
                'payment_mode' => $request->payment_mode,
                'currency_code' => $currency_code,
                'sub_total' => $summary['sub_total'],
                'delivery_charges' => $summary['delivery_charges'],
                'taxes' => $summary['taxes'],
                'total_cost' => $totalCostAfterRewards,
                'product_discounts' => $summary['product_discounts'],
                'coupon_discounts' => $summary['coupon_discounts'],
                'offer_discounts' => $summary['offer_discounts'],
                'total_discount' => $summary['total_discount'],
                'coupon_id' => $summary['coupon_id'] ?? null,
                'coupon_code' => $couponCode,
                'reward_used' => $rewardUsed,
                'delivery_date' => $expectedDeliveryDate,
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'quantity' => $request->quantity,
                'summary_items' => $summary['items'],
                'vendor_id' => $product->vendor_id,
                'card_holder_name' => $request->card_holder_name,
                'card_number' => $request->card_number,
                'expiry_month' => $request->expiry_month,
                'expiry_year' => $request->expiry_year,
                'card_type' => $request->card_type,
            ];

            // Store in cache for 1 hour
            $cacheKey = 'pending_order_' . $orderReferenceId;
            Log::info('buy_now: Storing order data in cache', [
                'cache_key' => $cacheKey,
                'order_data' => $orderData
            ]);
            \Illuminate\Support\Facades\Cache::put($cacheKey, $orderData, 3600);
            Log::info('buy_now: Order data stored in cache successfully', ['cache_key' => $cacheKey]);

            // Create a temporary dummy order object for payment initiation
            $tempOrder = (object)[
                'id' => null,
                'order_reference_id' => $orderReferenceId,
                'transaction_id' => $transactionId,
                'user_id' => $request->user_id,
                'total_cost' => $totalCostAfterRewards,
                'payment_mode' => $request->payment_mode,
                'user' => $userData,
            ];

            if (strtoupper($request->payment_mode) === 'KHALTI') {
                Log::info("Initiating Khalti for Pending Order: {$orderReferenceId}, Amount: {$totalCostAfterRewards}");

                $khaltiResponse = KhaltiService::initiatePayment($tempOrder, $userData);

                if ($khaltiResponse['status']) {
                    $gateway = \App\Models\PaymentGateway::where('slug', 'khalti')->first();
                    return response()->json([
                        'status' => true,
                        'message' => 'Order placed. Redirecting to Khalti.',
                        'payment_url' => $khaltiResponse['payment_url'],
                        'pidx' => $khaltiResponse['pidx'],
                        'order_reference_id' => $orderReferenceId,
                        'verify_url' => $gateway?->success_url ?? config('app.url') . '/api/khalti/verify',
                        'payload' => $khaltiResponse['payload']
                    ], 201);
                }
                return response()->json(['status' => false, 'message' => 'Khalti Error: ' . $khaltiResponse['message'], 'payload' => $khaltiResponse['payload']], 400);
            }
            // PhonePe Logic

            if (strtoupper($request->payment_mode) === 'PHONEPE') {
                $phonePeResponse = PhonePeService::initiatePayment($tempOrder);
                if ($phonePeResponse['status']) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Order placed. Redirecting to PhonePe.',
                        'payment_url' => $phonePeResponse['payment_url'],
                        'formData' => $phonePeResponse['formData'],
                        'merchantTransactionId' => $phonePeResponse['merchantTransactionId'],
                        'order_reference_id' => $orderReferenceId
                    ], 201);
                }
                return response()->json(['status' => false, 'message' => 'PhonePe Error: ' . ($phonePeResponse['message'] ?? 'Unknown error')], 400);
            }

            if (strtoupper($request->payment_mode) === 'PAYTM') {
                $paytmResponse = PaytmService::initiatePayment($tempOrder);
                if ($paytmResponse['status']) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Order placed. Redirecting to Paytm.',
                        'payment_url' => $paytmResponse['payment_url'],
                        'formData' => $paytmResponse['formData'],
                        'orderId' => $paytmResponse['orderId'],
                        'order_reference_id' => $orderReferenceId
                    ], 201);
                }
                return response()->json(['status' => false, 'message' => 'Paytm Error: ' . ($paytmResponse['message'] ?? 'Unknown error')], 400);
            }
        } catch (\Exception $e) {
            Log::error("Order Failed: " . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Internal Server Error'], 500);
        }
    }
  
    public function checkout_amount(Request $request)
    {
        $response = $this->checkout($request);
        $data = $response->getData(true);

        if (isset($data['status']) && $data['status'] === true) {
            return response()->json([
                'status' => true,
                'summary' => $data['summary']
            ], 200);
        }

        return $response;
    }

    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'    => 'nullable|exists:users,id',
            'ip_address' => 'required_without:user_id|string',
            'product_id' => 'nullable|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity'   => 'nullable|integer|min:1',
            'coupon_code' => 'nullable|string',
            'coupan_code' => 'nullable|string',
            'coupon' => 'nullable|string',
            'coupan' => 'nullable|string',
            'couponCode' => 'nullable|string',
            'city_id'    => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => __('messages.validation_error'), 'errors' => $validator->errors()], 422);
        }

        $user = null;
        if ($request->filled('user_id')) {
            $user = User::where('users.id', $request->user_id)
                ->leftJoin('countries', 'users.country_id', '=', 'countries.id')
                ->select('users.*', 'countries.currency_code')
                ->first();
        }

        $lang = $request->get('lang', 'en');
        $currency = \App\Helpers\GeneralHelper::get_currency_by_lang($lang);

        $items = [];
        $itemsCount = 0;
        $subTotalBeforeAnyDiscount = 0.0;
        $totalProductDiscount = 0.0;
        $subTotalAfterProductDiscount = 0.0;
        $totalCampaignDiscount = 0.0;
        $totalOfferDiscount = 0.0;
        $shippingFee = 0.0;
        $taxes = 0.0;
        $today = Carbon::now();
        $offerBreakdown = [];
        $campaignBreakdown = [];
        $appliedOfferDetails = null;
        $totalCouponDiscount = 0.0;
        $appliedCouponDetails = null;

        $checkoutItems = [];
        if ($request->filled('product_id') && $request->filled('variant_id')) {
            // Single Product Mode
            $qty = (int) ($request->quantity ?? 1);
            $variant = ProductVariant::find($request->variant_id);
            if ($variant) {
                $checkoutItems[] = (object)[
                    'product_id' => $request->product_id,
                    'variant_id' => $request->variant_id,
                    'qty' => $qty,
                    'price' => $variant->price,
                    'vendor_id' => Product::where('id', $request->product_id)->value('vendor_id')
                ];
            }
        } else {
            // Cart Mode
            $cartItems = Cart::when($user, function ($q) use ($user) {
                return $q->where('user_id', $user->id);
            }, function ($q) use ($request) {
                return $q->where('ip_address', $request->ip_address);
            })->get();

            foreach ($cartItems as $ci) {
                $checkoutItems[] = (object)[
                    'product_id' => $ci->product_id,
                    'variant_id' => $ci->variant_id,
                    'qty' => $ci->qty,
                    'price' => $ci->price,
                    'vendor_id' => $ci->vendor_id
                ];
            }
        }

        if (empty($checkoutItems)) {
            return response()->json(['status' => false, 'message' => __('messages.cart_empty')], 400);
        }

        $cityId = $request->city_id;
        if ($request->shipping_address_id) {
            $shippingAddress = ShippingAddress::find($request->shipping_address_id);
            if ($shippingAddress) {
                $cityId = $shippingAddress->city_id;
            }
        }

        // If still no cityId, try to get from user's last shipping address or profile
        if (!$cityId && $request->user_id) {
            $lastAddress = ShippingAddress::where('user_id', $request->user_id)->latest()->first();
            if ($lastAddress) {
                $cityId = $lastAddress->city_id;
            } else {
                $user = User::where('id', $request->user_id)->first();
                $cityId = $user->city_id ?? null;
            }
        }

        $summary = PriceCalculationHelper::calculateSummary($checkoutItems, $this->resolveCouponCode($request), $cityId);

        $items = [];
        foreach ($summary['items'] as $item) {
            $product = Product::find($item['product_id']);
            $variant = ProductVariant::find($item['variant_id']);

            $promoType = null;
            $promoLabel = null;
            if ($item['offer_unit_discount'] > 0) {
                $promoType = 'offer';
                $promoLabel = 'Offer Applied';
            } elseif ($item['campaign_unit_discount'] > 0) {
                $promoType = 'campaign';
                $promoLabel = 'Campaign Applied';
            }

            $items[] = [
                'product_id' => $item['product_id'],
                'category_id' => $product->category_id,
                'variant_id' => $item['variant_id'],
                'name' => $product->name,
                'qty' => $item['qty'],
                'original_price' => $item['unit_price'],
                'discount' => ($variant->discount_type === 'percent' || $variant->discount_type === '%' || $variant->discount_type === 'Percentage' || $variant->discount_type === 'percentage')
                    ? $variant->discount_value . ' % Off'
                    : $variant->discount_value . ' off',
                'product_discount' => $item['product_unit_discount'],
                'price_after_product_discount' => round($item['unit_price'] - $item['product_unit_discount'], 2),
                'promo_discount' => round($item['offer_unit_discount'] + $item['campaign_unit_discount'], 2),
                'promo_type' => $promoType,
                'promo_label' => $promoLabel,
                'final_unit_price' => $item['price_after_discounts'],
                'line_total' => $item['total_line_cost'],
                'vendor_tax' => $item['vendor_tax'],
                'tax_amount' => $item['tax_amount'],
                'image' => ImageHelper::getProductImage($variant->image ?? $product->thumbnail ?? null),
            ];
        }

        $rewardUsed = 0;
        if ($request->boolean('use_reward') && $user) {
            $availableReward = $user->reward_balance ?? 0;
            $maxRewardAllowed = round($summary['total_cost'] * 0.10, 2);
            $rewardUsed = min($availableReward, $maxRewardAllowed);
        }

        $totalCostAfterRewards = max(0, $summary['total_cost'] - $rewardUsed);

        // Calculate Expected Delivery Date
        $maxDeliveryDays = 3; // Default
        foreach ($summary['items'] as $item) {
            $vendor = User::find($item['vendor_id']);
            $days = $vendor->delivery_days ?? '2-3';

            if (preg_match_all('/\d+/', $days, $matches)) {
                $currentMax = max($matches[0]);
                if ($currentMax > $maxDeliveryDays) {
                    $maxDeliveryDays = (int)$currentMax;
                }
            }
        }
        $expectedDeliveryDate = now()->addDays($maxDeliveryDays);

        // Get applicable coupons
        $applicableCoupons = PriceCalculationHelper::getApplicableCoupons($checkoutItems);

        return response()->json([
            'status' => true,
            'summary' => [
                'currency' => $currency,
                'items_count' => count($items),
                'sub_total' => $summary['sub_total'],
                'product_discounts' => $summary['product_discounts'],
                'offer_discounts' => $summary['offer_discounts'],
                'campaign_discounts' => $summary['campaign_discounts'],
                'coupon_discounts' => $summary['coupon_discounts'],
                'delivery_charges' => $summary['delivery_charges'],
                'taxes' => $summary['taxes'],
                'reward_used' => $rewardUsed,
                'total_discount' => round($summary['total_discount'] + $rewardUsed, 2),
                'grand_total' => $totalCostAfterRewards,
                'coupon_id' => $summary['coupon_id'],
                'expected_delivery_date' => $expectedDeliveryDate->format('M d, Y'),
            ],
            'applicable_coupons' => $applicableCoupons,
            'items' => $items
        ]);
    }




    public function add_shipping_address(Request $request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['status' => false, 'message' => __('messages.user_not_found')], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city_id' => 'required|integer',
            'state_id' => 'required|integer',
            'country_id' => 'required|integer',
            'zip' => 'required|string|max:20',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {

            $shipping_address = ShippingAddress::where('user_id', $request->user_id)->first();

            if (empty($shipping_address)) {

                User::where('id', $request->user_id)->update([
                    'city_id' => $request->city_id,
                    'state_id' => $request->state_id,
                    'country_id' => $request->country_id,
                    'zip' => $request->zip,
                    'address' => $request->address,
                ]);
            }
            // If setting as default, unset previous default
            if ($request->is_default == 1) {
                ShippingAddress::where('user_id', $request->user_id)->where('is_default', 1)->update(['is_default' => 0]);
            }

            $address = ShippingAddress::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'zip' => $request->zip,
                'city_id' => $request->city_id,
                'state_id' => $request->state_id,
                'country_id' => $request->country_id,
                'is_default' => $request->is_default ?? false,
            ]);

            return response()->json([
                'status' => true,
                'message' => __('messages.shipping_address_added'),
                'data' => $address
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update_shipping_address(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required|exists:shipping_addresses,id',
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',

            'city_id' => 'required|integer',
            'state_id' => 'required|integer',
            'country_id' => 'required|integer',
            'zip' => 'required|string|max:20',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $address = ShippingAddress::where('id', $request->address_id)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$address) {
                return response()->json(['status' => false, 'message' => __('messages.shipping_address_not_found')], 404);
            }

            // If setting as default, unset previous default
            if ($request->is_default == 1) {
                ShippingAddress::where('user_id', $request->user_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => 0]);
            }

            $address->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'zip' => $request->zip,
                'city_id' => $request->city_id,
                'state_id' => $request->state_id,
                'country_id' => $request->country_id,
                'is_default' => $request->is_default ?? false,
            ]);

            return response()->json([
                'status' => true,
                'message' => __('messages.shipping_address_updated'),
                'data' => $address
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('messages.something_went_wrong'),
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function get_shipping_address(Request $request)
    {
        $addresses = ShippingAddress::with([
            'state:id,name',
            'city:id,name',
            'country:id,name'
        ])
            ->where('user_id', $request->user_id)
            ->get()
            ->map(function ($address) {
                return [
                    'id' => $address->id,
                    'user_id' => $address->user_id,
                    'name' => $address->name,
                    'email' => $address->email,
                    'phone' => $address->phone,
                    'address' => $address->address,
                    'city' => $address->city->name ?? null,
                    'state' => $address->state->name ?? null,
                    'country' => $address->country->name ?? null,
                    'zip' => $address->zip,
                    'is_default' => $address->is_default,
                    'created_at' => $address->created_at,
                    'updated_at' => $address->updated_at,
                ];
            });

        // $addresses = ShippingAddress::where('user_id', $request->user_id)->get();
        // echo '<pre>';print_r($addresses);die;
        return response()->json([
            'status' => true,
            'data' => $addresses
        ], 200);
    }

    public function edit_shipping_address(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required|exists:shipping_addresses,id',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            $address =   $addresses = ShippingAddress::with([
                'state:id,name',
                'city:id,name',
                'country:id,name'
            ])
                ->where('user_id', $request->user_id)
                ->get()
                ->map(function ($address) {
                    return [
                        'id'         => $address->id,
                        'user_id'    => $address->user_id,
                        'name'       => $address->name,
                        'email'      => $address->email,
                        'phone'      => $address->phone,
                        'address'    => $address->address,

                        // Names
                        'city'       => $address->city->name ?? null,
                        'state'      => $address->state->name ?? null,
                        'country'    => $address->country->name ?? null,

                        // IDs (✅ FIXED)
                        'city_id'    => $address->city_id,
                        'state_id'   => $address->state_id,
                        'country_id' => $address->country_id,

                        'zip'        => $address->zip,
                        'is_default' => $address->is_default,

                    ];
                })->where('id', $request->address_id)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$address) {
                return response()->json(['status' => false, 'message' => 'Shipping address not found or unauthorized'], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $address
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function delete_shipping_address(Request $request)
    {
        $address = ShippingAddress::where('id', $request->address_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$address) {
            return response()->json(['status' => false, 'message' => __('messages.shipping_address_not_found')], 404);
        }

        $address->delete();

        return response()->json([
            'status' => true,
            'message' => __('messages.shipping_address_deleted')
        ]);
    }

    public function my_orders(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $perPage = $request->per_page ?? 10;
            $lang = $request->get('lang', 'en');
            $currency = \App\Helpers\GeneralHelper::get_currency_by_lang($lang);

            $orderItems = OrderItem::with([
                'product:id,name,thumbnail',
                'variant:id,product_id,image,discount_type,discount_value',
                'order:id,user_id,order_reference_id,status,payment_status,order_date,delivery_date',
                'vendor:id,name,store_name,is_verified',
                'supportTicket:id,order_item_id,ticket_id,subject,status,priority,created_at',
                'refundRequest:id,order_id,order_item_id,user_id,vendor_id,refund_reason,description,amount,vendor_status,vendor_message,admin_status,admin_message,created_at,updated_at',
                'review:id,order_item_id'
            ])
                ->whereHas('order', function ($q) use ($request) {
                    $q->where('user_id', $request->user_id);
                })
                ->orderByDesc('id')
                ->paginate($perPage)->withQueryString();

            $formattedItems = $orderItems->getCollection()->map(function ($item) {

                $order  = $item->order;
                $vendor = $item->vendor;
                $ticket = $item->supportTicket;
                $refund = $item->refundRequest;
                $review = $item->review;

                /* -------------------------
               Expected Delivery
            ------------------------- */
                $expectedDeliveryDate = null;

                if ($order) {
                    if ($order->delivery_date) {
                        $expectedDeliveryDate = $order->delivery_date instanceof Carbon
                            ? $order->delivery_date->format('Y-m-d')
                            : Carbon::parse($order->delivery_date)->format('Y-m-d');
                    } elseif ($order->order_date) {
                        $orderDate = $order->order_date instanceof Carbon
                            ? $order->order_date
                            : Carbon::parse($order->order_date);

                        $expectedDeliveryDate = $orderDate->copy()->addDays(7)->format('Y-m-d');
                    }
                }

                /* -------------------------
               Refund Data
            ------------------------- */
                $refundData = null;

                if ($refund) {
                    $refundData = [
                        'id' => $refund->id,
                        'refund_reason' => $refund->refund_reason,
                        'description' => $refund->description,
                        'amount' => round($refund->amount, 2),
                        'vendor_status' => $refund->vendor_status,
                        'vendor_message' => $refund->vendor_message,
                        'admin_status' => $refund->admin_status,
                        'admin_message' => $refund->admin_message,
                        'created_at' => $refund->created_at instanceof Carbon
                            ? $refund->created_at->format('Y-m-d H:i:s')
                            : Carbon::parse($refund->created_at)->format('Y-m-d H:i:s'),
                        'updated_at' => $refund->updated_at instanceof Carbon
                            ? $refund->updated_at->format('Y-m-d H:i:s')
                            : Carbon::parse($refund->updated_at)->format('Y-m-d H:i:s'),

                        'is_rejected' => ($refund->vendor_status == 2 || $refund->admin_status == 2),
                        'is_approved' => ($refund->vendor_status == 1 && $refund->admin_status == 1),
                        'is_pending'  => ($refund->vendor_status == 0 || $refund->admin_status == 0),
                        'final_status' => $this->getRefundFinalStatus($refund)
                    ];
                }

                return [
                    'id' => $item->id,
                    'order_id' => $item->order_id,
                    'order_reference_id' => $order->order_reference_id ?? null,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product->name ?? 'N/A',
                    'quantity' => $item->quantity,
                    'price' => round($item->price, 2),
                    'actual_price' => round($item->actual_price, 2),
                    'discount' => ($item->variant?->discount_type === 'percent' || $item->variant?->discount_type === '%' || $item->variant?->discount_type === 'Percentage' || $item->variant?->discount_type === 'percentage')
                        ? $item->variant?->discount_value . ' % Off'
                        : $item->variant?->discount_value . ' off',
                    'numeric_discount' => round($item->actual_price - $item->price, 2),
                    'total' => round($item->total_actual_price, 2),

                    /* =====================================================
                   ⭐ FIXED: ORDER ITEM STATUS (CORRECT ONE)
                ===================================================== */
                    'status' => $item->status,

                    'image' => ImageHelper::getProductImage(
                        ($item->variant?->image) ?: ($item->product?->thumbnail ?? null)
                    ),

                    'payment_status' => $order->payment_status ?? null,
                    'order_date' => $order && $order->order_date
                        ? ($order->order_date instanceof Carbon
                            ? $order->order_date->format('Y-m-d')
                            : Carbon::parse($order->order_date)->format('Y-m-d'))
                        : null,

                    'expected_delivery_date' => $expectedDeliveryDate,

                    'vendor_name' => $vendor->name ?? 'N/A',
                    'store_name' => $vendor->store_name ?? 'N/A',

                    'is_verified' => $vendor
                        ? ($vendor->isAdmin() ||
                            ($vendor->hasMinimumKyc() &&
                                $vendor->areRequiredDocumentsVerified()))
                        : false,

                    'ticket_data' => is_object($ticket) ? [
                        'id' => $ticket->id,
                        'ticket_id' => $ticket->ticket_id,
                        'subject' => $ticket->subject,
                        'status' => $ticket->status,
                        'priority' => $ticket->priority,
                        'created_at' => $ticket->created_at instanceof Carbon
                            ? $ticket->created_at->format('Y-m-d H:i')
                            : Carbon::parse($ticket->created_at)->format('Y-m-d H:i'),
                    ] : null,

                    'refund_data' => $refundData,
                    'review_count' => $review ? 1 : 0,
                ];
            });

            return response()->json([
                'status' => true,
                'currency' => $currency,
                'data' => $formattedItems,
                'pagination' => [
                    'total' => $orderItems->total(),
                    'per_page' => $orderItems->perPage(),
                    'current_page' => $orderItems->currentPage(),
                    'last_page' => $orderItems->lastPage(),
                    'from' => $orderItems->firstItem(),
                    'to' => $orderItems->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }



    public function get_order_detail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:order_items,order_id', // 'order_id' here refers to the order_item_id
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $lang = $request->get('lang', 'en');
        $currency = \App\Helpers\GeneralHelper::get_currency_by_lang($lang);

        $orderItem = OrderItem::with(['product', 'variant:id,product_id,image,discount_type,discount_value', 'order.shippingAddress.state', 'order.shippingAddress.city', 'order.shippingAddress.country', 'vendor', 'supportTicket', 'review:id,order_item_id'])
            ->where('order_id', $request->order_id)
            ->whereHas('order', function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            })
            ->first();

        if (!$orderItem) {
            return response()->json(['status' => false, 'message' => 'Order item not found'], 404);
        }

        $order = $orderItem->order;
        $vendor = $orderItem->vendor;
        $ticket = $orderItem->supportTicket;
        $review = $orderItem->review;

        $itemDetails = [
            'id' => $orderItem->id,
            'product_id' => $orderItem->product_id,
            'variant_id' => $orderItem->variant_id,
            'product_name' => $orderItem->product->name ?? 'N/A',
            'quantity' => $orderItem->quantity,
            'price' => round($orderItem->price, 2),
            'discount' => ($orderItem->variant?->discount_type === 'percent' || $orderItem->variant?->discount_type === '%' || $orderItem->variant?->discount_type === 'Percentage' || $orderItem->variant?->discount_type === 'percentage')
                ? $orderItem->variant?->discount_value . ' % Off'
                : $orderItem->variant?->discount_value . ' off',
            'total' => round($orderItem->price * $orderItem->quantity, 2),
            'vendor_tax' => $orderItem->vendor_tax,
            'tax_amount' => round($orderItem->tax_amount, 2),
            'status' => $orderItem->status,
            'image' => ImageHelper::getProductImage(($orderItem->variant->image ?? null) ?: ($orderItem->product->thumbnail ?? null)),
            'vendor_name' => $vendor->name ?? 'N/A',
            'store_name' => $vendor->store_name ?? 'N/A',
            'is_verified' => $vendor ? ($vendor->isAdmin() || ($vendor->hasMinimumKyc() && $vendor->areRequiredDocumentsVerified())) : false,
            'ticket_data' => is_object($ticket) ? [
                'id' => $ticket->id,
                'ticket_id' => $ticket->ticket_id,
                'subject' => $ticket->subject,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'created_at' => $ticket->created_at->format('Y-m-d H:i'),
            ] : null,
            'review_count' => $review ? 1 : 0,
        ];

        return response()->json([
            'status' => true,
            'data' => [
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'order_reference_id' => $order->order_reference_id,
                'sub_total' => round($order->sub_total, 2),
                'product_discounts' => round($order->product_discounts, 2),
                'offer_discounts' => round($order->offer_discounts, 2),
                'coupon_discounts' => round($order->coupon_discounts ?? 0, 2),
                'total_discount' => round($order->total_discount, 2),
                'delivery_charges' => round($order->delivery_charges, 2),
                'taxes' => round($order->taxes, 2),
                'reward_used' => round($order->reward_used, 2),
                'total_cost' => round($order->total_cost, 2),
                'payment_mode' => $order->payment_mode,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
                'status' => $orderItem->status,
                'order_date' => $order->order_date ? $order->order_date->format('Y-m-d') : null,
                'currency' => $currency,
                'shipping_address' => [
                    'name' => $order->shippingAddress->name ?? null,
                    'email' => $order->shippingAddress->email ?? null,
                    'phone' => $order->shippingAddress->phone ?? null,
                    'address' => $order->shippingAddress->address ?? null,
                    'city' => $order->shippingAddress->city->name ?? null,
                    'state' => $order->shippingAddress->state->name ?? null,
                    'country' => $order->shippingAddress->country->name ?? null,
                    'zip' => $order->shippingAddress->zip ?? null,
                ],
                'item_details' => $itemDetails,
                'order_history' => $this->get_order_history($orderItem)
            ]
        ]);
    }

    private function get_order_history($orderItem)
    {
        $history = [];
        $order = $orderItem->order;

        // Order Placed
        $history[] = [
            'status' => 'Order Placed',
            'date' => $order->order_date ? $order->order_date->format('Y-m-d H:i') : ($order->created_at ? $order->created_at->format('Y-m-d H:i') : null),
            'completed' => true,
            'description' => 'Your order has been successfully placed.'
        ];

        // Dispatched
        $isDispatched = $orderItem->status >= 1 || $order->dispatched_date; // Assuming status 1 is dispatched
        $history[] = [
            'status' => 'Dispatched',
            'date' => $order->dispatched_date ? $order->dispatched_date->format('Y-m-d H:i') : null,
            'completed' => (bool)$isDispatched,
            'description' => $isDispatched ? 'Your item has been dispatched.' : 'Pending dispatch.'
        ];

        // Out for Delivery (Assuming status 2)
        $isOutForDelivery = $orderItem->status >= 2;
        $history[] = [
            'status' => 'Out for Delivery',
            'date' => null,
            'completed' => (bool)$isOutForDelivery,
            'description' => $isOutForDelivery ? 'Your item is out for delivery.' : 'Pending delivery assignment.'
        ];

        // Delivered (Assuming status 3)
        $isDelivered = $orderItem->status >= 3;
        $history[] = [
            'status' => 'Delivered',
            'date' => $order->delivery_date ? $order->delivery_date->format('Y-m-d H:i') : null,
            'completed' => (bool)$isDelivered,
            'description' => $isDelivered ? 'Item delivered successfully.' : 'Expect delivery soon.'
        ];

        return $history;
    }

    public function track_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|string',
            'user_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $order = Order::where('id', $request->order_id)
            ->where('user_id', $request->user_id)
            ->with(['items.product', 'items.variant', 'items.supportTicket', 'shippingAddress'])
            ->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $items = $order->items->map(function ($item) {
            $ticket = $item->supportTicket;
            return [
                'id' => $item->id,
                'product_name' => $item->product->name ?? 'N/A',
                'quantity' => $item->quantity,
                'status' => $item->status,
                'image' => ImageHelper::getProductImage(($item->variant->image ?? null) ?: ($item->product->thumbnail ?? null)),
                'tracking_history' => $this->get_order_history($item),
                'ticket_data' => is_object($ticket) ? [
                    'id' => $ticket->id,
                    'ticket_id' => $ticket->ticket_id,
                    'subject' => $ticket->subject,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority,
                    'created_at' => $ticket->created_at->format('Y-m-d H:i'),
                ] : null,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => [
                'order_reference_id' => $order->order_reference_id,
                'order_date' => $order->order_date ? $order->order_date->format('Y-m-d') : null,
                'status' => $order->status,
                'items' => $items
            ]
        ]);
    }

    /**
     * Get the final status of a refund request
     */
    private function getRefundFinalStatus($refund)
    {
        // If either vendor or admin rejected, it's rejected
        if ($refund->vendor_status == 2 || $refund->admin_status == 2) {
            return 'rejected';
        }

        // If both approved, it's approved
        if ($refund->vendor_status == 1 && $refund->admin_status == 1) {
            return 'approved';
        }

        // If either is still pending, it's pending
        if ($refund->vendor_status == 0 || $refund->admin_status == 0) {
            return 'pending';
        }

        // Default to pending if unclear
        return 'pending';
    }

    private function saveCardIfPresent(Request $request): ?\App\Models\UserCard
    {
        if (!$request->filled('card_holder_name') || !$request->filled('card_number')) {
            return null;
        }

        $existingCard = \App\Models\UserCard::where('user_id', $request->user_id)
            ->where('card_number', $request->card_number)
            ->first();

        if ($existingCard) {
            $existingCard->update([
                'card_holder_name' => $request->card_holder_name,
                'expiry_month' => $request->expiry_month,
                'expiry_year' => $request->expiry_year,
                'card_type' => $request->card_type,
            ]);
            return $existingCard->fresh();
        }

        $hasExisting = \App\Models\UserCard::where('user_id', $request->user_id)->exists();

        return \App\Models\UserCard::create([
            'user_id' => $request->user_id,
            'card_holder_name' => $request->card_holder_name,
            'card_number' => $request->card_number,
            'expiry_month' => $request->expiry_month,
            'expiry_year' => $request->expiry_year,
            'card_type' => $request->card_type,
            'is_default' => !$hasExisting,
        ]);
    }
}
