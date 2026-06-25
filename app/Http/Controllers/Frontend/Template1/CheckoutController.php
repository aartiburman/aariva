<?php

namespace App\Http\Controllers\Frontend\Template1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingAddress;
use App\Models\UserCard;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Helpers\PriceCalculationHelper;
use App\Helpers\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
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
            return redirect()->route('frontend.cart.index')->with('error', 'Your cart is empty');
        }

        $shippingAddresses = ShippingAddress::where('user_id', $userId)->latest()->get();
        $userCards = UserCard::where('user_id', $userId)->latest()->get();

        $cartTotals = PriceCalculationHelper::calculateSummary($cartItems);

        return view('frontend.checkout.index', compact('cartItems', 'shippingAddresses', 'userCards', 'cartTotals'));
    }

    public function placeOrder(Request $request)
    {
        $userId = Auth::id();
        $ipAddress = $request->ip();

        if (! $userId) {
            return redirect()->route('frontend.login')->with('error', 'Please login to checkout');
        }

        $rules = [
            'payment_mode' => 'required|in:COD,Card',
            'card_id'      => 'required_if:payment_mode,Card|exists:users_card,id',
        ];

        if ($request->shipping_address_id) {
            $rules['shipping_address_id'] = 'required|exists:shipping_addresses,id';
        } else {
            $rules['name']    = 'required|string|max:255';
            $rules['email']   = 'nullable|email|max:255';
            $rules['phone']   = 'required|string|max:20';
            $rules['address'] = 'required|string';
            $rules['city']    = 'required|string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $cartItems = Cart::where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('frontend.cart.index')->with('error', 'Your cart is empty');
        }

        if ($request->shipping_address_id) {
            $shippingAddress = ShippingAddress::find($request->shipping_address_id);
        } else {
            $shippingAddress = ShippingAddress::create([
                'user_id'    => $userId,
                'name'       => $request->name,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'address'    => $request->address,
                'city'       => $request->city,
                'state'      => $request->state,
                'country'    => $request->country,
                'zip'        => $request->zip,
            ]);
        }

        $cityId = $shippingAddress->city_id ?? null;

        $summary = PriceCalculationHelper::calculateSummary($cartItems, null, $cityId);

        $orderReferenceId = 'ORD-' . strtoupper(Str::random(10));
        $transactionId = 'TXN-' . strtoupper(Str::random(12));

        $isCard = $request->payment_mode === 'Card';
        $paymentStatus = $isCard ? 1 : 0;
        $orderStatus = 0;

        $cardId = $isCard ? $request->card_id : null;

        $order = DB::transaction(function () use ($userId, $request, $cartItems, $summary, $orderReferenceId, $transactionId, $shippingAddress, $cityId, $paymentStatus, $orderStatus, $cardId) {

            $order = Order::create([
                'order_reference_id' => $orderReferenceId,
                'transaction_id'     => $transactionId,
                'user_id'            => $userId,
                'shipping_id'        => $shippingAddress->id,
                'status'             => $orderStatus,
                'payment_status'     => $paymentStatus,
                'payment_mode'       => $request->payment_mode,
                'card_id'            => $cardId,
                'sub_total'          => $summary['sub_total'],
                'delivery_charges'   => $summary['delivery_charges'],
                'taxes'              => $summary['taxes'],
                'total_cost'         => $summary['total_cost'],
                'product_discounts'  => $summary['product_discounts'],
                'coupon_discounts'   => $summary['coupon_discounts'],
                'offer_discounts'    => $summary['offer_discounts'],
                'total_discount'     => $summary['total_discount'],
                'order_date'         => now(),
            ]);

            $delivery_charges = $summary['delivery_charges'];
            $item_count = count($summary['items']);
            $per_item_delivery_charge = $item_count > 0 ? $delivery_charges / $item_count : 0;

            foreach ($summary['items'] as $item) {
                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_id'        => $item['product_id'],
                    'variant_id'        => $item['variant_id'],
                    'vendor_id'         => $item['vendor_id'],
                    'campaign_id'       => $item['campaign_id'] ?? null,
                    'quantity'          => $item['qty'],
                    'price'             => $item['unit_price'],
                    'discount'          => $item['product_unit_discount'],
                    'offer_discount'    => $item['offer_unit_discount'],
                    'campaign_discount' => $item['campaign_unit_discount'],
                    'actual_price'      => $item['price_after_discounts'],
                    'total_actual_price' => round($item['price_after_discounts'] * $item['qty'] + $item['tax_amount'] + $per_item_delivery_charge, 2),
                    'vendor_tax'        => $item['vendor_tax'],
                    'tax_amount'        => $item['tax_amount'],
                    'status'            => $orderStatus,
                    'payment_status'    => $paymentStatus,
                    'payment_mode'      => $request->payment_mode,
                    'delivery_charges'  => $per_item_delivery_charge,
                ]);
            }

            Cart::where('user_id', $userId)->delete();

            return $order;
        });

        return redirect()->route('frontend.checkout.success', ['reference_id' => $order->order_reference_id]);
    }

    public function success($reference_id)
    {
        $order = Order::with('items.product', 'shippingAddress')
            ->where('order_reference_id', $reference_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('frontend.checkout.success', compact('order'));
    }

    public function addAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
            'address'  => 'required|string',
            'city_id'  => 'required|exists:cities,id',
            'state_id' => 'nullable|exists:states,id',
            'zip'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $address = ShippingAddress::create([
            'user_id' => Auth::id(),
            'name'    => $request->name,
            'phone'   => $request->phone,
            'address' => $request->address,
            'city_id' => $request->city_id,
            'state_id' => $request->state_id,
            'zip'     => $request->zip,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Address added successfully',
            'data'    => $address,
        ]);
    }

    public function deleteAddress($id)
    {
        $address = ShippingAddress::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$address) {
            return response()->json([
                'status'  => false,
                'message' => 'Address not found',
            ], 404);
        }

        $address->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Address deleted successfully',
        ]);
    }
}
