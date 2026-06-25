<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\ProductReview;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Models\Banner;
use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\Offer;
use Illuminate\Support\Facades\Validator;
use App\Models\GeneralSetting;
use App\Helpers\PriceCalculationHelper;
use App\Models\ShippingAddress;



class CartController extends Controller
{
    private function resolveCouponCode(Request $request)
    {
        return $request->coupon_code
            ?? $request->coupon
            ?? $request->coupan_code
            ?? $request->coupan
            ?? $request->input('couponCode');
    }
  
    public function get_cart_detail(Request $request)
    {
        try {

            $lang = $request->get('lang', 'en');
            app()->setLocale($lang);

            if (!$request->user_id && !$request->ip_address) {
                return response()->json([
                    'status' => false,
                    'message' => 'User ID or IP address required'
                ], 400);
            }

            $query = Cart::with(['product.offer']);

            // 🔹 Filter by user or IP
            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            } else {
                $query->where('ip_address', $request->ip_address);
            }

            // 🔹 Filter by variant if provided
            if ($request->has('variant_id') && $request->variant_id != '') {
                $query->where('variant_id', $request->variant_id);
            }

            // 🔹 Pagination
            $perPage = $request->get('per_page', 10);
            $cartItems = $query->paginate($perPage)->withQueryString();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.cart_empty')
                ], 200);
            }

            // 🔹 Calculate totals safely
            $couponCode = $this->resolveCouponCode($request);

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
                    $user = \App\Models\User::find($request->user_id);
                    $cityId = $user->city_id ?? null;
                }
            }

            $allItems = Cart::with(['product.offer'])
                ->where(function($q) use ($request) {
                    if ($request->user_id) $q->where('user_id', $request->user_id);
                    else $q->where('ip_address', $request->ip_address);
                })->get();

            $cartTotals = PriceCalculationHelper::calculateSummary($allItems, $couponCode, $cityId);

            // 🔹 Map calculated items back to the paginated collection
            $processedItemsMap = collect($cartTotals['items'])->keyBy(function ($item) {
                return $item['product_id'] . '-' . ($item['variant_id'] ?? '0');
            });

            $cartItems->getCollection()->transform(function ($item) use ($processedItemsMap) {
                $key = $item->product_id . '-' . ($item->variant_id ?? '0');
                $calc = $processedItemsMap->get($key);

                if ($calc) {
                    $item->offer_unit_discount = $calc['offer_unit_discount'];
                    $item->campaign_unit_discount = $calc['campaign_unit_discount'];
                    $item->product_unit_discount = $calc['product_unit_discount'];
                    $item->price_after_discounts = $calc['price_after_discounts'];
                    $item->total_price = $calc['total_line_cost'];
                    $item->campaign_id = $calc['campaign_id'] ?? null;
                    
                    // Add string versions for consistency with user provided JSON
                    $item->offer_discount = number_format($calc['offer_unit_discount'], 2, '.', '');
                    $item->campaign_discount = number_format($calc['campaign_unit_discount'], 2, '.', '');
                    
                    // Add offer value inside the product array if a product exists
                    if ($item->product) {
                        $item->product->offer_value = number_format($calc['offer_unit_discount'], 2, '.', '');
                    }
                }

                $item->image = ImageHelper::getProductImage($item->image ?? null);
                return $item;
            });

            return response()->json([
                'status' => true,
                'count'  => $allItems->count(),
                'currency' => \App\Helpers\GeneralHelper::get_currency_by_lang($lang),
                'order_total' => [
                    'subtotal'          => $cartTotals['sub_total'] ?? 0,
                    'offer_discount'    => $cartTotals['offer_discounts'] ?? 0,
                    'campaign_discount' => $cartTotals['campaign_discounts'] ?? 0,
                    'coupon_discount'   => $cartTotals['coupon_discounts'] ?? 0,
                    'total_discount'    => $cartTotals['total_discount'] ?? 0,
                    'estimated_vat'     => $cartTotals['taxes'] ?? 0,
                    'shipping_fee'      => $cartTotals['delivery_charges'] ?? 0,
                    'grand_total'       => $cartTotals['total_cost'] ?? 0
                ],
                'data' => $cartItems,
                'pagination' => [
                    'current_page' => $cartItems->currentPage(),
                    'last_page'    => $cartItems->lastPage(),
                    'per_page'     => $cartItems->perPage(),
                    'total'        => $cartItems->total(),
                    'next_page_url' => $cartItems->nextPageUrl(),
                    'prev_page_url' => $cartItems->previousPageUrl(),
                ]
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Server Error',
                'error'   => $e->getMessage() // 👈 shows real problem
            ], 500);
        }
    }
 public function add_remove_cart(Request $request)
{
    $lang = $request->get('lang', 'en');
    app()->setLocale($lang);

    /* -------------------------
       VALIDATION
    ------------------------- */
    $validator = Validator::make($request->all(), [
        'user_id'    => 'nullable|exists:users,id',
        'ip_address' => 'required_without:user_id|string',
        'product_id' => 'required|exists:products,id',
        'qty'        => 'required|numeric|min:1',
        'action'     => 'required|in:0,1',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => __('messages.validation_error'),
            'errors'  => $validator->errors()
        ], 422);
    }

    /* -------------------------
       FETCH PRODUCT
    ------------------------- */
    try {
    $product = Product::with('variants')->find($request->product_id);

    if (!$product || $product->variants->isEmpty()) {
        return response()->json([
            'status'  => false,
            'message' => __('messages.product_not_found')
        ], 404);
    }

    $variantId = $request->variant_id ?? $product->variants->first()->id;
    $variant = $product->variants->where('id', $variantId)->first();
    
    if (!$variant) {
        $variant = $product->variants->first();
    }
    
    /* -------------------------
       PRICE CALCULATION (Unified)
    ------------------------- */
    $calc = PriceCalculationHelper::calculateItemPrice($product, $variant->id, $request->qty);
    $finalPrice = $calc['price_after_discounts'];

    /* -------------------------
       IMAGE
    ------------------------- */

    $img = null;

    if (!empty($variant->image)) {
        $imgs = str_starts_with($variant->image, '[')
            ? json_decode($variant->image, true)
            : explode(',', $variant->image);
        $img = trim($imgs[0] ?? null);
    } else {
        $img = $product->thumbnail;
    }

    /* -------------------------
       ADD TO CART
    ------------------------- */

    if ($request->action == 1) {
// echo '<pre>';print_r($variant->discount_type);die;
        Cart::updateOrCreate(
            [
                'user_id'    => $request->user_id,
                'ip_address' => $request->user_id ? null : $request->ip_address,
                'product_id' => $product->id,
                'variant_id' => $variant->id,
            ],
            [
                'vendor_id'  => $product->vendor_id,
                'qty'        => $request->qty,
                'price'      => $calc['unit_price'],
                'discount'   =>  ($variant->discount_type === 'percent' || $variant->discount_type === '%' || $variant->discount_type === 'Percentage' || $variant->discount_type === 'percentage') 
                                 ? $variant->discount_value . ' % Off' 
                                 : $variant->discount_value . ' off',
                'product_discount' => $calc['product_unit_discount'],
                'offer_discount' => $calc['offer_unit_discount'],
                'campaign_id' => $calc['campaign_id'] ?? null,
                'campaign_discount' => $calc['campaign_unit_discount'],
                'total_price' => $calc['total_line_cost'],
                'image'      => $img
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => __('messages.cart_added')
        ],200);
    }

    /* -------------------------
       REMOVE FROM CART
    ------------------------- */

    Cart::when($request->user_id, function ($q) use ($request) {
            $q->where('user_id', $request->user_id);
        }, function ($q) use ($request) {
            $q->where('ip_address', $request->ip_address);
        })
        ->where('product_id', $product->id)
        ->where('variant_id', $variant->id)
        ->delete();

    return response()->json([
        'status'  => true,
        'message' => __('messages.cart_removed')
    ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Add/Remove Cart Error: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
}

    public function applyOffer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'     => 'nullable|exists:users,id',
            'ip_address'  => 'required_without:user_id|string',
            'offer_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.validation_error'),
                'errors'  => $validator->errors()
            ], 422);
        }

        $cartItems = Cart::when($request->user_id, function($q) use ($request) {
            return $q->where('user_id', $request->user_id);
        }, function($q) use ($request) {
            return $q->where('ip_address', $request->ip_address);
        })->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['status' => false, 'message' => __('messages.cart_empty')], 200);
        }

        $checkoutController = new UserCheckout( );
        $checkoutData = $checkoutController->checkout($request)->getData(true);

        if (!$checkoutData['status']) {
            return response()->json($checkoutData, 200);
        }

        foreach ($checkoutData['summary']['items'] as $itemData) {
            $cartItem = $cartItems->where('product_id', $itemData['product_id'])->where('variant_id', $itemData['variant_id'])->first();
            if ($cartItem) {
                $cartItem->update([
                    'product_discount'  => $itemData['product_discount'],
                    'offer_code'       => $checkoutData['summary']['applied_offer_details']['code'] ?? null,
                    'offer_discount'   => $itemData['promo_type'] == 'offer' ? $itemData['promo_discount'] : 0,
                    'total_price'       => $itemData['line_total'],
                ]);
            }
        }

        return response()->json([
            'status'  => true,
            'message' => __('messages.offer_applied'),
            'data'    => $checkoutData
        ]);
    }

    private function formatProduct($product, $request)
    {
        $image = null;
        $price = null;

        if ($product->firstVariant) {
            $image = ImageHelper::getProductImage(($product->firstVariant->image ?? null) ?: ($product->thumbnail ?? null));
            $price = $product->firstVariant->price;
        }

        $currency = \App\Helpers\GeneralHelper::get_currency_by_lang($request->lang ?? 'en');

        return [
            'id'            => $product->id,
            'name'          => $product->name,
            'slug'          => $product->slug,
            'image'         => $image,
            'price'         => $price,
            'currency'      => $currency,
            'product_variant_label_defult' => $product->firstVariant ? \App\Helpers\GeneralHelper::getVariantLabel($product->firstVariant->product_variant, $request->lang ?? 'en') : null,
            'rating'        => 4.5,
            'rating_stars'  => 5,
            'review_count'  => 128,
        ];
    }


}
