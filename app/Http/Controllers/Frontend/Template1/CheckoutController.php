<?php

namespace App\Http\Controllers\Frontend\Template1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingAddress;
use App\Models\UserCard;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\PaymentGateway;
use App\Models\GeneralSetting;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Helpers\PriceCalculationHelper;
use App\Helpers\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $ipAddress = $request->ip();

        $cartItems = Cart::with('product.variants', 'variant')
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($ipAddress) {
                $q->where('ip_address', $ipAddress);
            })
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('frontend.cart.index')->with('error', 'Your cart is empty');
        }

        $shippingAddresses = collect();
        $userCards = collect();
        $defaultAddress = null;

        if ($userId) {
            $shippingAddresses = ShippingAddress::where('user_id', $userId)->latest()->get();
            $userCards = UserCard::where('user_id', $userId)->latest()->get();
            $defaultAddress = $shippingAddresses->firstWhere('is_default', 1) ?? $shippingAddresses->first();
        }

        $cityId = $defaultAddress ? ($defaultAddress->city_id ?? null) : null;
        $userCityId = $defaultAddress ? ($defaultAddress->city_id ?? null) : null;

        $sessionCoupon = session('checkout_coupon');
        $cartTotals = PriceCalculationHelper::calculateSummary($cartItems, $sessionCoupon, $cityId, $userCityId);

        $availableCoupons = PriceCalculationHelper::getApplicableCoupons($cartItems);

        $paymentGateways = PaymentGateway::where('status', 1)->get();

        $deliveryDates = $this->getDeliveryDates();

        $shippingCharge = (float) (GeneralSetting::where('key', 'shipping_charge')->value('value') ?? 100);
        $freeShippingMin = (float) (GeneralSetting::where('key', 'free_shipping_min')->value('value') ?? 500);

        $selectedDelivery = session('checkout_delivery', 'standard');

        return view('frontend.checkout.index', compact(
            'cartItems',
            'shippingAddresses',
            'defaultAddress',
            'userCards',
            'cartTotals',
            'availableCoupons',
            'paymentGateways',
            'deliveryDates',
            'shippingCharge',
            'freeShippingMin',
            'selectedDelivery',
            'sessionCoupon'
        ));
    }

    public function checkPincode(Request $request)
    {
        $pincode = $request->input('pincode');
        if (!preg_match('/^\d{6}$/', $pincode)) {
            return response()->json([
                'status' => false,
                'message' => 'Please enter a valid 6-digit PIN code'
            ]);
        }

        // Check if pincode exists in cities table
        $city = \App\Models\City::where('pincode', $pincode)->first();

        if (!$city) {
            // If not found, check shipping zones
            $zone = \App\Models\ShippingZone::whereRaw("FIND_IN_SET(?, pincodes)", [$pincode])->first();
            if ($zone) {
                return response()->json([
                    'status' => true,
                    'message' => 'Delivery available to this PIN code',
                    'data' => [
                        'city' => $zone->name,
                        'delivery_days' => '3-5',
                        'cod_available' => true
                    ]
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Sorry! We do not deliver to this PIN code yet'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Delivery available to this PIN code',
            'data' => [
                'city' => $city->name,
                'city_id' => $city->id,
                'state_id' => $city->state_id,
                'delivery_days' => '3-5',
                'cod_available' => true
            ]
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $code = strtoupper(trim($request->code));
        $userId = Auth::id();
        $ipAddress = $request->ip();

        $cartItems = Cart::with('product')
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($ipAddress) {
                $q->where('ip_address', $ipAddress);
            })
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Cart is empty']);
        }

        $today = Carbon::now();
        $coupon = Coupon::where('code', $code)
            ->where('status', 1)
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $today);
            })
            ->first();

        if (!$coupon) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired coupon code']);
        }

        if ($coupon->max_uses > 0 && $coupon->used_count >= $coupon->max_uses) {
            return response()->json(['status' => false, 'message' => 'This coupon has reached its usage limit']);
        }

        $totalsWithCoupon = PriceCalculationHelper::calculateSummary($cartItems, $code);
        $couponDiscount = $totalsWithCoupon['coupon_discounts'] ?? 0;

        if ($couponDiscount <= 0) {
            return response()->json(['status' => false, 'message' => 'This coupon is not applicable to your cart items']);
        }

        session(['checkout_coupon' => $code]);

        $totals = PriceCalculationHelper::calculateSummary($cartItems, $code);
        $discountLabel = $coupon->type == 1 ? $coupon->value . '% OFF' : '₹' . $coupon->value . ' OFF';

        return response()->json([
            'status' => true,
            'message' => "Coupon {$code} applied! You saved " . PriceHelper::formatPrice($couponDiscount),
            'data' => [
                'code' => $code,
                'discount' => $couponDiscount,
                'discount_label' => $discountLabel,
                'totals' => $totals
            ]
        ]);
    }

    public function removeCoupon(Request $request)
    {
        session()->forget('checkout_coupon');

        $userId = Auth::id();
        $ipAddress = $request->ip();

        $cartItems = Cart::with('product')
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($ipAddress) {
                $q->where('ip_address', $ipAddress);
            })
            ->get();

        $totals = PriceCalculationHelper::calculateSummary($cartItems);

        return response()->json([
            'status' => true,
            'message' => 'Coupon removed',
            'data' => ['totals' => $totals]
        ]);
    }

    public function setDelivery(Request $request)
    {
        $type = $request->input('type', 'standard');
        session(['checkout_delivery' => $type]);

        return response()->json(['status' => true]);
    }

    public function placeOrder(Request $request)
    {
        $userId = Auth::id();
        $ipAddress = $request->ip();

        // Guest checkout — create a quick session user or require login
        if (!$userId) {
            $guestData = session('guest_checkout');
            if (!$guestData || empty($guestData['name'])) {
                $validator = Validator::make($request->all(), [
                    'guest_name'  => 'required|string|max:255',
                    'guest_email' => 'nullable|email|max:255',
                    'guest_phone' => 'required|string|max:20',
                ]);

                if ($validator->fails()) {
                    return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
                }

                session(['guest_checkout' => [
                    'name'  => $request->guest_name,
                    'email' => $request->guest_email,
                    'phone' => $request->guest_phone,
                ]]);
                $guestData = session('guest_checkout');
            }
        }

        $cartItems = Cart::with('product')
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($ipAddress) {
                $q->where('ip_address', $ipAddress);
            })
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Your cart is empty']);
        }

        $rules = [
            'payment_mode' => 'required|in:COD,UPI,Card,NetBanking,Wallet',
            'shipping_address_id' => 'required|exists:shipping_addresses,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $shippingAddress = ShippingAddress::find($request->shipping_address_id);

        if ($userId && $shippingAddress->user_id !== $userId) {
            return response()->json(['status' => false, 'message' => 'Invalid shipping address']);
        }

        $cityId = $shippingAddress->city_id ?? null;
        $userCityId = $cityId;

        $couponCode = session('checkout_coupon');
        $summary = PriceCalculationHelper::calculateSummary($cartItems, $couponCode, $cityId, $userCityId);

        $orderReferenceId = 'ARV' . now()->format('YmdHis') . strtoupper(Str::random(4));
        $transactionId = 'TXN' . now()->format('YmdHis') . strtoupper(Str::random(6));
        $deliveryType = session('checkout_delivery', 'standard');

        $isOnlinePayment = in_array($request->payment_mode, ['UPI', 'Card', 'NetBanking', 'Wallet']);
        $paymentStatus = $isOnlinePayment ? 0 : 0; // 0 = pending, will update after gateway confirmation
        $orderStatus = 0; // 0 = new/pending

        $estimatedDelivery = $deliveryType === 'express'
            ? now()->addDays(2)->format('Y-m-d')
            : now()->addDays(5)->format('Y-m-d');

        $deliveryCharge = $summary['delivery_charges'] ?? 0;
        if ($deliveryType === 'express') {
            $deliveryCharge = 99;
        }

        $couponId = $summary['coupon_id'] ?? null;
        $couponCodeForDb = $couponCode;

        $order = DB::transaction(function () use (
            $userId, $request, $cartItems, $summary, $orderReferenceId, $transactionId,
            $shippingAddress, $cityId, $paymentStatus, $orderStatus, $deliveryCharge,
            $estimatedDelivery, $couponId, $couponCodeForDb, $deliveryType
        ) {
            $order = Order::create([
                'order_reference_id' => $orderReferenceId,
                'transaction_id'     => $transactionId,
                'user_id'            => $userId,
                'shipping_id'        => $shippingAddress->id,
                'status'             => $orderStatus,
                'payment_status'     => $paymentStatus,
                'payment_mode'       => $request->payment_mode,
                'sub_total'          => $summary['sub_total'],
                'delivery_charges'   => $deliveryCharge,
                'taxes'              => $summary['taxes'],
                'total_cost'         => $summary['total_cost'] - $summary['delivery_charges'] + $deliveryCharge,
                'product_discounts'  => $summary['product_discounts'],
                'coupon_discounts'   => $summary['coupon_discounts'],
                'offer_discounts'    => $summary['offer_discounts'],
                'campaign_discounts' => $summary['campaign_discounts'],
                'total_discount'     => $summary['total_discount'],
                'coupon_id'          => $couponId,
                'coupon_code'        => $couponCodeForDb,
                'order_date'         => now(),
                'delivery_date'      => $estimatedDelivery,
            ]);

            $itemCount = count($summary['items']);
            $perItemDeliveryCharge = $itemCount > 0 ? $deliveryCharge / $itemCount : 0;

            foreach ($summary['items'] as $item) {
                OrderItem::create([
                    'order_id'            => $order->id,
                    'product_id'          => $item['product_id'],
                    'variant_id'          => $item['variant_id'],
                    'vendor_id'           => $item['vendor_id'],
                    'campaign_id'         => $item['campaign_id'] ?? null,
                    'quantity'            => $item['qty'],
                    'price'               => $item['unit_price'],
                    'discount'            => $item['product_unit_discount'],
                    'offer_discount'      => $item['offer_unit_discount'],
                    'campaign_discount'   => $item['campaign_unit_discount'],
                    'actual_price'        => $item['price_after_discounts'],
                    'total_actual_price'  => round($item['price_after_discounts'] * $item['qty'] + $item['tax_amount'] + $perItemDeliveryCharge, 2),
                    'vendor_tax'          => $item['vendor_tax'],
                    'tax_amount'          => $item['tax_amount'],
                    'status'              => $orderStatus,
                    'payment_status'      => $paymentStatus,
                    'payment_mode'        => $request->payment_mode,
                    'delivery_charges'    => $perItemDeliveryCharge,
                ]);
            }

            Cart::where('user_id', $userId)->orWhere('ip_address', $request->ip())->delete();

            session()->forget(['checkout_coupon', 'checkout_delivery']);

            return $order;
        });

        // Handle payment initiation for online payments
        if ($isOnlinePayment) {
            $paymentResponse = $this->initiateOnlinePayment($order, $request->payment_mode);
            if (isset($paymentResponse['redirect_url'])) {
                return response()->json([
                    'status' => true,
                    'redirect' => $paymentResponse['redirect_url'],
                    'order_id' => $order->order_reference_id,
                ]);
            }
            // Fallback: treat as placed
        }

        // For COD, redirect to success
        session(['order_success' => $order->order_reference_id]);

        return response()->json([
            'status' => true,
            'redirect' => route('frontend.checkout.success', ['reference_id' => $order->order_reference_id]),
            'order_id' => $order->order_reference_id,
        ]);
    }

    protected function initiateOnlinePayment($order, $paymentMode)
    {
        // Integrate with Razorpay or existing gateways
        // For now, return placeholder — will redirect to success
        // In real integration, call PaymentGateway service
        return ['status' => true, 'redirect_url' => route('frontend.checkout.success', ['reference_id' => $order->order_reference_id])];
    }

    public function success($reference_id)
    {
        $userId = Auth::id();

        $order = Order::with('items.product', 'shippingAddress')
            ->where('order_reference_id', $reference_id);

        if ($userId) {
            $order->where('user_id', $userId);
        }

        $order = $order->firstOrFail();

        // Get recommended/cross-sell products
        $crossSellProducts = Product::where('status', 1)
            ->whereHas('variants')
            ->inRandomOrder()
            ->take(8)
            ->get();

        // Calculate estimated delivery range
        $deliveryStart = $order->delivery_date
            ? Carbon::parse($order->delivery_date)->subDays(1)->format('d M')
            : now()->addDays(2)->format('d M');
        $deliveryEnd = $order->delivery_date
            ? Carbon::parse($order->delivery_date)->format('d M Y')
            : now()->addDays(5)->format('d M Y');

        return view('frontend.checkout.success', compact(
            'order',
            'crossSellProducts',
            'deliveryStart',
            'deliveryEnd'
        ));
    }

    public function addAddress(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['status' => false, 'message' => 'Please login first']);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
            'address'  => 'required|string',
            'city'     => 'required|string',
            'state'    => 'nullable|string',
            'city_id'  => 'nullable|exists:cities,id',
            'state_id' => 'nullable|exists:states,id',
            'zip'      => 'nullable|string',
            'type'     => 'nullable|string|in:Home,Work,Other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $isFirst = ShippingAddress::where('user_id', $userId)->count() === 0;

        $address = ShippingAddress::create([
            'user_id'    => $userId,
            'name'       => $request->name,
            'phone'      => $request->phone,
            'email'      => $request->email,
            'address'    => $request->address,
            'city'       => $request->city,
            'state'      => $request->state,
            'city_id'    => $request->city_id,
            'state_id'   => $request->state_id,
            'zip'        => $request->zip,
            'is_default' => $isFirst,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Address added successfully',
            'data'    => [
                'id'      => $address->id,
                'name'    => $address->name,
                'phone'   => $address->phone,
                'address' => $address->address,
                'city'    => $address->city,
                'state'   => $address->state,
                'zip'     => $address->zip,
                'type'    => $address->type ?? 'Home',
            ]
        ]);
    }

    public function deleteAddress($id)
    {
        $address = ShippingAddress::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Address not found'], 404);
        }

        $address->delete();

        return response()->json(['status' => true, 'message' => 'Address deleted successfully']);
    }

    public function getAddresses()
    {
        $addresses = ShippingAddress::where('user_id', Auth::id())->latest()->get();

        return response()->json([
            'status' => true,
            'data'   => $addresses->map(function ($addr) {
                return [
                    'id'      => $addr->id,
                    'name'    => $addr->name,
                    'phone'   => $addr->phone,
                    'address' => $addr->address,
                    'city'    => $addr->city,
                    'state'   => $addr->state,
                    'zip'     => $addr->zip,
                    'type'    => $addr->type ?? 'Home',
                    'is_default' => $addr->is_default,
                ];
            })
        ]);
    }

    private function getDeliveryDates()
    {
        $today = Carbon::now();

        return [
            'standard' => [
                'label' => 'Standard Delivery',
                'time'  => '3-5 Business Days',
                'start' => $today->copy()->addDays(3)->format('d M'),
                'end'   => $today->copy()->addDays(5)->format('d M'),
                'price' => 0,
            ],
            'express' => [
                'label' => 'Express Delivery',
                'time'  => '1-2 Business Days',
                'start' => $today->copy()->addDays(1)->format('d M'),
                'end'   => $today->copy()->addDays(2)->format('d M'),
                'price' => 99,
            ],
        ];
    }
}
