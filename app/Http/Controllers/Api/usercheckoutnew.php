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
use App\Models\Campaign;
use App\Models\ProductVariant;
use App\Helpers\NotificationHelper;
use App\Services\Logistics\NCMService;
use App\Helpers\PriceCalculationHelper;
use App\Helpers\CampaignBudgetHelper;
use App\Services\Payment\KhaltiService;
use App\Services\Payment\PhonePeService;
use App\Services\Payment\PaytmService;

class UserCheckout extends Controller
{

    public function sync_ncm_status(Request $request, $reference_id = null)
    {
        // 1. Handle Direct Webhook Payload from NCM (if provided)
        // NCM usually sends 'id' (tracking_id) and 'status' in their webhook
        if ($request->has('id') && $request->has('status')) {
            Log::info('NCM Webhook Received', $request->all());
            $trackingId = $request->id;
            $ncmStatus = $request->status;
            
            $item = OrderItem::where('tracking_id', $trackingId)->where('logistics_provider', 'NCM')->first();
            if ($item) {
                $ncmService = new NCMService();
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
                }

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
        $ncmService = new NCMService();
        $items = $order->items()->where('logistics_provider', 'NCM')->get();

        $updatedCount = 0;
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


    public function place_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'shipping_address_id' => 'nullable|exists:shipping_addresses,id',
            'name' => 'required_without:shipping_address_id|string|max:255',
            'phone' => 'required_without:shipping_address_id|string|max:20',
            'address' => 'required_without:shipping_address_id|string',
            'city_id' => 'required_without:shipping_address_id|exists:cities,id',
            'payment_mode' => 'required|in:COD,PayPal,Khalti,PhonePe,Paytm,Card',
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

        try {
            return DB::transaction(function () use ($request, $cartItems, $currency_code, $userData) {

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

                // If still no cityId, try to get from user's last shipping address or profile
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

                // Calculate Expected Delivery Date
                $maxDeliveryDays = 3; // Default default
                foreach ($summary['items'] as $item) {
                    $vendor = User::find($item['vendor_id']);
                    $days = $vendor->delivery_days ?? '2-3';
                    
                    // Extract the largest number from strings like "2-3" or "5"
                    if (preg_match_all('/\d+/', $days, $matches)) {
                        $currentMax = max($matches[0]);
                        if ($currentMax > $maxDeliveryDays) {
                            $maxDeliveryDays = (int)$currentMax;
                        }
                    }
                }
                $expectedDeliveryDate = now()->addDays($maxDeliveryDays);

                // CREATE ORDER
                $order = Order::create([
                    'order_reference_id' => 'ORD-' . strtoupper(Str::random(10)),
                    'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
                    'user_id' => $request->user_id,
                    'shipping_id' => $shippingId,
                    'status' => 0,
                    'payment_status' => 0,
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

                    'order_date' => now(),
                    'delivery_date' => $expectedDeliveryDate,
                ]);

                $delivery_charges = $summary['delivery_charges'];
                $item_count = count($summary['items']);
                $per_item_delivery_charge = $item_count > 0 ? $delivery_charges / $item_count : 0;

                // Calculate pro-rated coupon and reward discounts
                $total_before_global_discounts = collect($summary['items'])->sum(function($item) {
                    return $item['price_after_discounts'] * $item['qty'];
                });
                $coupon_discount_total = (float) ($summary['coupon_discounts'] ?? 0.0);
                $reward_discount_total = (float) ($rewardUsed ?? 0.0);

                // ORDER ITEMS
                $ncmService = new NCMService();
                $trackingIds = [];
                foreach ($summary['items'] as $item) {
                    $line_subtotal = $item['price_after_discounts'] * $item['qty'];
                    
                    // Pro-rate coupon and reward based on item's share of the subtotal after item-level discounts
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
                        'status' => 0,
                        'payment_status' => 0,
                        'payment_mode' => $request->payment_mode,
                        'currency' => $currency_code,
                        'delivery_charges' => $per_item_delivery_charge,
                    ]);

                    if (!empty($item['campaign_id']) && (float)$item['campaign_unit_discount'] > 0) {
                        $discountUsage = (float)$item['campaign_unit_discount'] * (int)$item['qty'];
                        CampaignBudgetHelper::applyDiscountUsage((int)$item['campaign_id'], (int)$item['vendor_id'], $discountUsage);
                    }
                    
                    // Phase 1: All orders assigned to NCM
                    $ncmResult = $ncmService->createShipment($orderItem);
                    
                    $trackingIds[] = [
                        'product_id' => $orderItem->product_id,
                        'tracking_id' => $orderItem->tracking_id,
                        'consignment_id' => $orderItem->tracking_id, 
                        'ncm_response' => $ncmResult['data'] ?? [],
                        'success' => $ncmResult['success'] ?? false,
                        'ncm_order_create_url' => ($ncmService->baseUrl . '/api/v1/order/create')
                    ];

                    // Notify Vendor
                    $vendor = User::find($item['vendor_id']);
                    if ($vendor) {
                        // 1. Email Notification
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

                        // 2. FCM/System Notification
                        NotificationHelper::notifyVendor($vendor->id, [
                            'title' => 'New Order Received',
                            'message' => 'You have received a new order #' . $order->order_reference_id . ' for ' . (Product::where('id', $item['product_id'])->value('name') ?? 'product'),
                            'type' => 'orders',
                            'url' =>'orders-details/' . $order->order_reference_id,
                            'icon' => 'solar:cart-large-minimalistic-bold-duotone'
                        ]);
                    }
                }

                // Notify Admins (FCM + Email)
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

                // Notify Customer
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
                            'customer_name' => (string)($order->user->name ?? 'Customer'),
                            'order_id' => $order->order_reference_id,
                            'items' => $customerItems,
                            'sub_total' => PriceHelper::formatPrice($order->sub_total),
                            'delivery_charges' => PriceHelper::formatPrice($order->delivery_charges),
                            'discount' => PriceHelper::formatPrice($order->total_discount),
                            'taxes' => PriceHelper::formatPrice($order->taxes),
                            'total_cost' => PriceHelper::formatPrice($order->total_cost),
                            'order_url' => config('app.url') . '/api/get-order-detail?user_id=' . $order->user_id . '&order_id=' . $order->id
                        ]
                    );
                }

                // CLEAR CART
                Cart::where('user_id', $request->user_id)->delete();

                // KHALTI INITIATION
                if ($request->payment_mode === 'Khalti') {
                    $khaltiResponse = KhaltiService::initiatePayment($order, $order->user);

                    if ($khaltiResponse['status']) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Order placed successfully. Please complete the Khalti payment.',
                            'order_id' => $order->id,
                            'order_reference_id' => $order->order_reference_id,
                            'payment_url' => $khaltiResponse['payment_url'],
                            'pidx' => $khaltiResponse['pidx'],
                            'tracking_details' => $trackingIds
                        ], 201);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Khalti initiation failed: ' . ($khaltiResponse['message'] ?? 'Unknown error'),
                            'order_id' => $order->id
                        ], 400);
                    }
                }

                // PHONEPE INITIATION
                if ($request->payment_mode === 'PhonePe') {
                    $phonePeResponse = PhonePeService::initiatePayment($order);

                    if ($phonePeResponse['status']) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Order placed successfully. Please complete the PhonePe payment.',
                            'order_id' => $order->id,
                            'order_reference_id' => $order->order_reference_id,
                            'payment_url' => $phonePeResponse['payment_url'],
                            'formData' => $phonePeResponse['formData'],
                            'merchantTransactionId' => $phonePeResponse['merchantTransactionId'],
                            'tracking_details' => $trackingIds
                        ], 201);
                    } else {
                        throw new \Exception('PhonePe initiation failed.');
                    }
                }

                // PAYTM INITIATION
                if ($request->payment_mode === 'Paytm') {
                    $paytmResponse = PaytmService::initiatePayment($order);

                    if ($paytmResponse['status']) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Order placed successfully. Please complete the Paytm payment.',
                            'order_id' => $order->id,
                            'order_reference_id' => $order->order_reference_id,
                            'payment_url' => $paytmResponse['payment_url'],
                            'formData' => $paytmResponse['formData'],
                            'orderId' => $paytmResponse['orderId'],
                            'tracking_details' => $trackingIds
                        ], 201);
                    } else {
                        throw new \Exception('Paytm initiation failed.');
                    }
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Order placed successfully',
                    'order_id' => $order->id,
                    'order_reference_id' => $order->order_reference_id,
                    'coupon_details' => [
                        'coupon_id' => $order->coupon_id,
                        'coupon_code' => $order->coupon_code,
                        'discount' => $order->coupon_discounts
                    ],
                    'tracking_details' => $trackingIds
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error('Order Placement Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while placing your order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function buy_now(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'payment_mode' => 'required|in:COD,PayPal,Khalti,PhonePe,Paytm,Card',
            'shipping_address_id' => 'nullable|exists:shipping_addresses,id',
            'name' => 'required_without:shipping_address_id|string|max:255',
            'phone' => 'required_without:shipping_address_id|string|max:20',
            'address' => 'required_without:shipping_address_id|string',
            'city_id' => 'required_without:shipping_address_id|exists:cities,id',
            'coupon_code' => 'nullable|string',
            'coupan_code' => 'nullable|string',
            'coupon' => 'nullable|string',
            'coupan' => 'nullable|string',
            'couponCode' => 'nullable|string',
            'offer_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::find($request->product_id);
        $variant = ProductVariant::find($request->variant_id);

        if (!$variant || $variant->stock < $request->quantity) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient stock'
            ]);
        }

        $userData = User::leftJoin('countries', 'users.country_id', '=', 'countries.id')
            ->where('users.id', $request->user_id)
            ->select('users.*', 'countries.currency_code')
            ->first();

        $currency_code = $userData->currency_code ?? 'NPR';

        try {
            return DB::transaction(function () use ($request, $product, $variant, $currency_code, $userData) {

                /* ================= SHIPPING ================= */
                $shippingId = null;
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
                    $cityId = $request->city_id;
                }

                // If still no cityId, try to get from user's last shipping address or profile
                if (!$cityId && $request->user_id) {
                    $lastAddress = ShippingAddress::where('user_id', $request->user_id)->latest()->first();
                    if ($lastAddress) {
                        $cityId = $lastAddress->city_id;
                    } else {
                        $user = User::find($request->user_id);
                        $cityId = $user->city_id ?? null;
                    }
                }

                $checkoutItems = [
                    (object)[
                        'product_id' => $request->product_id,
                        'variant_id' => $request->variant_id,
                        'qty' => $request->quantity,
                        'price' => $variant->price,
                        'vendor_id' => $product->vendor_id
                    ]
                ];

                $summary = PriceCalculationHelper::calculateSummary($checkoutItems, $this->resolveCouponCode($request), $cityId);

                $rewardUsed = 0;
                if ($request->boolean('use_reward')) {
                    $user = User::find($request->user_id);
                    $availableReward = $user->reward_balance ?? 0;
                    $maxRewardAllowed = round($summary['total_cost'] * 0.10, 2);
                    $rewardUsed = min($availableReward, $maxRewardAllowed);
                }

                $totalCostAfterRewards = max(0, $summary['total_cost'] - $rewardUsed);

                // Calculate Expected Delivery Date
                $vendor = User::find($product->vendor_id);
                $days = $vendor->delivery_days ?? '2-3';
                $maxDeliveryDays = 3;
                if (preg_match_all('/\d+/', $days, $matches)) {
                    $maxDeliveryDays = (int)max($matches[0]);
                }
                $expectedDeliveryDate = now()->addDays($maxDeliveryDays);

                // CREATE ORDER
                $order = Order::create([
                    'order_reference_id' => 'ORD-' . strtoupper(Str::random(10)),
                    'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
                    'user_id' => $request->user_id,
                    'shipping_id' => $shippingId,
                    'status' => 0,
                    'payment_status' => 0,
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

                    'order_date' => now(),
                    'delivery_date' => $expectedDeliveryDate,
                ]);

                $delivery_charges = $summary['delivery_charges'];
                $item_count = count($summary['items']);
                $per_item_delivery_charge = $item_count > 0 ? $delivery_charges / $item_count : 0;

                // Calculate pro-rated coupon and reward discounts
                $total_before_global_discounts = collect($summary['items'])->sum(function ($item) {
                    return $item['price_after_discounts'] * $item['qty'];
                });
                $coupon_discount_total = (float) ($summary['coupon_discounts'] ?? 0.0);
                $reward_discount_total = (float) ($rewardUsed ?? 0.0);

                // ORDER ITEMS
                $ncmService = new NCMService();
                $trackingIds = [];
                foreach ($summary['items'] as $item) {
                    $line_subtotal = $item['price_after_discounts'] * $item['qty'];

                    // Pro-rate coupon and reward based on item's share of the subtotal after item-level discounts
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
                        'status' => 0,
                        'payment_status' => 0,
                        'payment_mode' => $request->payment_mode,
                        'currency' => $currency_code,
                        'delivery_charges' => $per_item_delivery_charge,
                    ]);

                    if (!empty($item['campaign_id']) && (float)$item['campaign_unit_discount'] > 0) {
                        CampaignBudgetHelper::applyDiscountUsage(
                            (int)$item['campaign_id'],
                            (int)$item['vendor_id'],
                            (float)$item['campaign_unit_discount'] * (int)$item['qty']
                        );
                    }

                    // Phase 1: All orders assigned to NCM
                    $ncmResult = $ncmService->createShipment($orderItem);

                    $trackingIds[] = [
                        'product_id' => $orderItem->product_id,
                        'tracking_id' => $orderItem->tracking_id,
                        'consignment_id' => $orderItem->tracking_id,
                        'ncm_response' => $ncmResult['data'] ?? [],
                        'success' => $ncmResult['success'] ?? false,
                        'ncm_order_create_url' => ($ncmService->baseUrl . '/api/v1/order/create')
                    ];

                    // Notify Vendor
                    $vendor = User::find($item['vendor_id']);
                    if ($vendor) {
                        // 1. Email Notification
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
                                    'product_name' => $product->name ?? 'Product',
                                    'product_image' => ImageHelper::getProductImage(
                                        ($variant->image ?? null) ?: ($product->thumbnail ?? null)
                                    ),
                                    'quantity' => $orderItem->quantity,
                                    'earnings' => PriceHelper::formatPrice($orderItem->total_actual_price),
                                    'dashboard_url' => config('app.url') . '/vendor/dashboard'
                                ]
                            );
                        }


                        // 2. Database & FCM/System Notification
                        NotificationHelper::notifyVendor($vendor->id, [
                            'title' => 'New Order Received',
                            'message' => 'You have received a new order #' . $order->order_reference_id . ' for ' . ($product->name ?? 'product'),
                            'type' => 'orders',
                            'url' => 'orders-details/' . $order->order_reference_id,
                            'icon' => 'solar:cart-large-minimalistic-bold-duotone'
                        ]);
                    }
                }

                // Notify Admins (Database, FCM + Email)
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

                // Notify Customer
                $customerItems = [
                    [
                        'name' => $product->name ?? 'Product',
                        'qty' => $request->quantity,
                        'price' => PriceHelper::formatPrice($summary['total_cost'] / $request->quantity), // Approximate unit price after item discounts
                        'image' => ImageHelper::getProductImage(($variant->image ?? null) ?: ($product->thumbnail ?? null)),
                    ]
                ];

                if ($order->user && !empty(trim((string)$order->user->email))) {
                    EmailHelper::send(
                        $order->user->email,
                        'Order Confirmed - #' . $order->order_reference_id,
                        '',
                        'emails.order-placed',
                        [
                            'customer_name' => (string)($order->user->name ?? 'Customer'),
                            'order_id' => $order->order_reference_id,
                            'items' => $customerItems,
                            'sub_total' => PriceHelper::formatPrice($order->sub_total),
                            'delivery_charges' => PriceHelper::formatPrice($order->delivery_charges),
                            'discount' => PriceHelper::formatPrice($order->total_discount),
                            'taxes' => PriceHelper::formatPrice($order->taxes),
                            'total_cost' => PriceHelper::formatPrice($order->total_cost),
                            'order_url' => config('app.url') . '/api/get-order-detail?user_id=' . $order->user_id . '&order_id=' . $order->id
                        ]
                    );
                }

                /* ================= STOCK ================= */
                $variant->decrement('stock', $request->quantity);

                /* ================= PHONEPE ================= */
                if ($request->payment_mode === 'PhonePe') {
                    $phonePeResponse = PhonePeService::initiatePayment($order);

                    if ($phonePeResponse['status']) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Order placed successfully. Please complete the PhonePe payment.',
                            'order_id' => $order->id,
                            'order_reference_id' => $order->order_reference_id,
                            'payment_url' => $phonePeResponse['payment_url'],
                            'formData' => $phonePeResponse['formData'],
                            'merchantTransactionId' => $phonePeResponse['merchantTransactionId'],
                            'tracking_details' => $trackingIds
                        ], 201);
                    }

                    throw new \Exception('PhonePe initiation failed.');
                }

                /* ================= PAYTM ================= */
                if ($request->payment_mode === 'Paytm') {
                    $paytmResponse = PaytmService::initiatePayment($order);

                    if ($paytmResponse['status']) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Order placed successfully. Please complete the Paytm payment.',
                            'order_id' => $order->id,
                            'order_reference_id' => $order->order_reference_id,
                            'payment_url' => $paytmResponse['payment_url'],
                            'formData' => $paytmResponse['formData'],
                            'orderId' => $paytmResponse['orderId'],
                            'tracking_details' => $trackingIds
                        ], 201);
                    }

                    throw new \Exception('Paytm initiation failed.');
                }

                /* ================= KHALTI ================= */
                if ($request->payment_mode === 'Khalti') {

                    $khaltiResponse = KhaltiService::initiatePayment($order, $order->user);

                    if ($khaltiResponse['status']) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Order placed successfully. Please complete the Khalti payment.',
                            'order_id' => $order->id,
                            'order_reference_id' => $order->order_reference_id,
                            'payment_url' => $khaltiResponse['payment_url'],
                            'pidx' => $khaltiResponse['pidx'],
                            'tracking_details' => $trackingIds
                        ], 201);
                    }

                    // Return error response instead of throwing exception to keep JSON format
                    return response()->json([
                        'status' => false,
                        'message' => 'Khalti initiation failed: ' . ($khaltiResponse['message'] ?? 'Unknown error'),
                        'order_id' => $order->id
                    ], 400);
                }



                return response()->json([
                    'status' => true,
                    'message' => 'Order placed successfully',
                    'order_id' => $order->id,
                    'order_reference_id' => $order->order_reference_id,
                    'coupon_details' => [
                        'coupon_id' => $order->coupon_id,
                        'coupon_code' => $order->coupon_code,
                        'discount' => $order->coupon_discounts
                    ],
                    'tracking_details' => $trackingIds
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error('Buy Now Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'An error occurred during Buy Now: ' . $e->getMessage()
            ], 500);
        }
    }

    
    private function resolveCouponCode(Request $request)
    {
        return $request->coupon_code 
            ?? $request->coupan_code 
            ?? $request->coupon 
            ?? $request->coupan 
            ?? $request->couponCode 
            ?? null;
    }

    public function get_shipping_address(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $shippingAddresses = ShippingAddress::where('user_id', $request->user_id)->get();

        return response()->json([
            'status' => true,
            'data' => $shippingAddresses
        ]);
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
}
