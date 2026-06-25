<?php

namespace App\Http\Controllers\Frontend\Template1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingAddress;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Helpers\PriceCalculationHelper;
use App\Helpers\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    protected function getCartItems()
    {
        $userId = Auth::check() ? Auth::id() : null;
        $ipAddress = request()->ip();

        return Cart::with('product')
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($ipAddress) {
                $q->where('ip_address', $ipAddress);
            })
            ->get();
    }

    protected function getUserCityId()
    {
        return Auth::check() ? Auth::user()->city_id : null;
    }

    public function index(Request $request)
    {
        $cartItems = $this->getCartItems();
        $userCityId = $this->getUserCityId();
        $cartTotals = PriceCalculationHelper::calculateSummary($cartItems, null, null, $userCityId);

        return view('frontend.cart.index', compact('cartItems', 'cartTotals'));
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'qty'        => 'required|numeric|min:1',
            'variant_id' => 'nullable|exists:product_variants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $product = Product::with('variants')->find($request->product_id);

        if (!$product || $product->variants->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'Product not found',
            ], 404);
        }

        $variantId = $request->variant_id ?? $product->variants->first()->id;
        $variant = $product->variants->where('id', $variantId)->first();

        if (!$variant) {
            $variant = $product->variants->first();
        }

        $calc = PriceCalculationHelper::calculateItemPrice($product, $variant->id, $request->qty);

        $img = null;
        if (!empty($variant->image)) {
            $imgs = str_starts_with($variant->image, '[')
                ? json_decode($variant->image, true)
                : explode(',', $variant->image);
            $img = trim($imgs[0] ?? null);
        } else {
            $img = $product->thumbnail;
        }

        $userId = Auth::check() ? Auth::id() : null;
        $ipAddress = $request->ip();

        Cart::updateOrCreate(
            [
                'user_id'    => $userId,
                'ip_address' => $userId ? null : $ipAddress,
                'product_id' => $product->id,
                'variant_id' => $variant->id,
            ],
            [
                'vendor_id'  => $product->vendor_id,
                'qty'        => $request->qty,
                'price'      => $calc['unit_price'],
                'discount'   => ($variant->discount_type === 'percent' || $variant->discount_type === '%' || $variant->discount_type === 'Percentage' || $variant->discount_type === 'percentage')
                    ? $variant->discount_value . ' % Off'
                    : $variant->discount_value . ' off',
                'product_discount'  => $calc['product_unit_discount'],
                'offer_discount'    => $calc['offer_unit_discount'],
                'campaign_id'      => $calc['campaign_id'] ?? null,
                'campaign_discount' => $calc['campaign_unit_discount'],
                'total_price'       => $calc['total_line_cost'],
                'image'             => $img,
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Item added to cart successfully',
        ]);
    }

    public function remove($id)
    {
        $userId = Auth::check() ? Auth::id() : null;
        $ipAddress = request()->ip();

        Cart::when($userId, function ($q) use ($userId) {
            $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            $q->where('ip_address', $ipAddress);
        })
            ->where('id', $id)
            ->delete();

        if (request()->ajax()) {
            $cartItems = $this->getCartItems();
            $userCityId = $this->getUserCityId();
            $cartTotals = PriceCalculationHelper::calculateSummary($cartItems, null, null, $userCityId);

            return response()->json([
                'status'       => true,
                'message'      => 'Item removed from cart',
                'totals'       => $cartTotals,
                'cart_count'   => $cartItems->count(),
                'items_html'   => $cartItems->isEmpty() ? view('frontend.cart.partials.empty')->render() : null,
            ]);
        }

        return redirect()->back()->with('success', 'Item removed from cart');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'  => 'required|exists:carts,id',
            'qty' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $userId = Auth::check() ? Auth::id() : null;
        $ipAddress = $request->ip();

        $cartItem = Cart::when($userId, function ($q) use ($userId) {
            $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            $q->where('ip_address', $ipAddress);
        })
            ->where('id', $request->id)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'status'  => false,
                'message' => 'Cart item not found',
            ], 404);
        }

        $product = Product::find($cartItem->product_id);
        $calc = PriceCalculationHelper::calculateItemPrice($product, $cartItem->variant_id, $request->qty);

        $cartItem->update([
            'qty'             => $request->qty,
            'total_price'     => $calc['total_line_cost'],
            'product_discount' => $calc['product_unit_discount'],
            'offer_discount'   => $calc['offer_unit_discount'],
            'campaign_discount' => $calc['campaign_unit_discount'],
        ]);

        $cartItems = $this->getCartItems();
        $userCityId = $this->getUserCityId();
        $cartTotals = PriceCalculationHelper::calculateSummary($cartItems, null, null, $userCityId);

        return response()->json([
            'status'     => true,
            'message'    => 'Cart updated successfully',
            'totals'     => $cartTotals,
            'cart_count' => $cartItems->count(),
        ]);
    }

    public function removeByProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $userId = Auth::check() ? Auth::id() : null;
        $ipAddress = $request->ip();

        $deleted = Cart::when($userId, function ($q) use ($userId) {
            $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            $q->where('ip_address', $ipAddress);
        })
            ->where('product_id', $request->product_id)
            ->delete();

        if ($request->ajax()) {
            $cartItems = $this->getCartItems();
            $userCityId = $this->getUserCityId();
            $cartTotals = PriceCalculationHelper::calculateSummary($cartItems, null, null, $userCityId);

            return response()->json([
                'status'       => true,
                'message'      => 'Item removed from cart',
                'totals'       => $cartTotals,
                'cart_count'   => $cartItems->count(),
                'items_html'   => $cartItems->isEmpty() ? view('frontend.cart.partials.empty')->render() : null,
            ]);
        }

        return redirect()->back()->with('success', 'Item removed from cart');
    }

    public function count()
    {
        $userId = Auth::check() ? Auth::id() : null;
        $ipAddress = request()->ip();

        $count = Cart::when($userId, function ($q) use ($userId) {
            $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            $q->where('ip_address', $ipAddress);
        })
            ->count();

        return response()->json([
            'status' => true,
            'count'  => $count,
        ]);
    }
}
