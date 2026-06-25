<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Wishlist;
use App\Models\Cart;
use App\Models\Banner;
use App\Models\Product;
use App\Models\ProductReview;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;


class WishlistController extends Controller
{



public function add_to_wishlist(Request $request)
{
    /* -------------------------
       LANGUAGE
    ------------------------- */
    $lang = $request->get('lang', 'en');
    app()->setLocale($lang);

    /* -------------------------
       VALIDATION
    ------------------------- */
    $rules = [
        'user_id'    => 'nullable|exists:users,id',
        'product_id' => 'required|exists:products,id',
        'qty'        => 'nullable|numeric|min:1',
        'color'      => 'nullable|string',
        'size'       => 'nullable|string',
        'action'     => 'required|in:0,1',
    ];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    /* -------------------------
       IDENTIFIER (USER / GUEST)
    ------------------------- */
    $userId = $request->user_id;
    $ip_address = $request->ip_address;
    // $ip     = $request->ip();

    /* -------------------------
       FETCH PRODUCT + VARIANT
    ------------------------- */
    $product = Product::with([
        'variants' => fn ($q) => $q->orderBy('id', 'asc'),
    ])->find($request->product_id);

    if (!$product || $product->variants->isEmpty()) {
        return response()->json([
            'status'  => false,
            'message' => __('messages.product_not_found')
        ], 404);
    }

    $variant = $product->variants->first();

    /* -------------------------
       PRICE CALCULATION
    ------------------------- */
    $originalPrice = (float) $variant->price;

    $finalPrice = PriceHelper::applyDiscount(
        $originalPrice,
        $variant->discount_type,
        $variant->discount_value
    );

    /* -------------------------
       ACTION (ADD / REMOVE)
    ------------------------- */
    $today = now();

    /* -------------------------
       IMAGE (FIRST)
    ------------------------- */
    $img = null;
    if (!empty($variant->image)) {
        $images = is_array(json_decode($variant->image, true))
            ? json_decode($variant->image, true)
            : explode(',', $variant->image);

        $img = trim($images[0] ?? null);
    }

    /* -------------------------
       ADD TO WISHLIST
    ------------------------- */
    if ($request->action == 1) {

        Wishlist::updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id'    => $userId,
                'ip_address' => $ip_address??'',
            ],
            [
                'variant_id'  => $variant->id,
                'qty'         => $request->qty ?? 1,
                'color'       => $request->color,
                'size'        => $request->size,
                'price'       => $originalPrice,
                'final_price' => $finalPrice,
                'image'       => $img,
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => __('messages.wishlist_added')
        ]);
    }

    /* -------------------------
       REMOVE FROM WISHLIST
    ------------------------- */
    Wishlist::where('product_id', $product->id)
        ->when($userId, function ($q) use ($userId) {
            $q->where('user_id', $userId);
        }, function ($q) use ($ip_address) {
            $q->where('ip_address', $ip_address??'');
        })
        ->delete();

    return response()->json([
        'status'  => true,
        'message' => __('messages.wishlist_removed')
    ]);
}


 public function get_wishlist(Request $request)
{
    $lang = $request->get('lang', 'en');
    app()->setLocale($lang);

    $validator = Validator::make($request->all(), [
        'user_id'    => 'nullable|exists:users,id',
        'ip_address' => 'required_without:user_id|string',
        // 'per_page'   => 'nullable|integer|min:1|max:100',
        'page'       => 'nullable|integer|min:1'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ]);
    }

    $userId   = $request->user_id;
    $ip       = $request->ip_address;
    $perPage  = 12;

    /* ================= CART IDS ================= */
    $cartProductIds = Cart::when($userId,
            fn($q) => $q->where('user_id', $userId),
            fn($q) => $q->where('ip_address', $ip)
        )
        ->pluck('product_id')
        ->toArray();

    /* ================= MAIN QUERY ================= */
    $paginator = Wishlist::when($userId,
            fn($q) => $q->where('wishlists.user_id', $userId),
            fn($q) => $q->where('wishlists.ip_address', $ip)
        )
        ->leftJoin('products', 'wishlists.product_id', '=', 'products.id')
        ->leftJoin('users as vendor', 'products.vendor_id', '=', 'vendor.id')
        ->leftJoin('countries', 'vendor.country_id', '=', 'countries.id')
        ->leftJoin('product_variants', 'wishlists.variant_id', '=', 'product_variants.id')
        ->select(
            'wishlists.*',
            'products.name as product_name',
            'products.name_ar as product_name_ar',
            'products.name_ne as product_name_ne',
            'products.slug as product_slug',
            'products.slug_ar as product_slug_ar',
            'products.slug_ne as product_slug_ne',
            'products.short_description',
            'products.short_description_ar',
            'products.short_description_ne',
            'countries.currency',
            'countries.currency_code',
            'product_variants.discount_type',
            'product_variants.discount_value'
        )
        ->orderBy('wishlists.id', 'desc') // IMPORTANT
        ->paginate($perPage);

    /* ================= TRANSFORM DATA ================= */
    $paginator->getCollection()->transform(function ($item) use ($lang, $cartProductIds) {

        $item->image = ImageHelper::getProductImage($item->image);
        $item->is_in_cart = in_array($item->product_id, $cartProductIds);

        // Currency
        $item->currency_symbol = ($lang === 'en')
            ? ($item->currency_code ?? 'NPR')
            : ($item->currency ?? '$');

        $item->currency_code = $item->currency_code ?? 'USD';

        // Discount display
        if (in_array($item->discount_type, ['percent', '%', 'Percentage', 'percentage'])) {
            $item->discount_display = $item->discount_value . ' % Off';
        } elseif (in_array($item->discount_type, ['flat', 'off', 'Fixed Amount', 'flate']) && $item->discount_value > 0) {
            $item->discount_display = $item->discount_value . ' off';
        } else {
            $item->discount_display = '';
        }

        // Reviews
        $reviews = ProductReview::where('product_id', $item->product_id)
            ->where('status', 1)
            ->pluck('rating');

        $item->review_count = $reviews->count();
        $item->average_rating = $reviews->count()
            ? round($reviews->avg(), 1)
            : 0;

        // Language fields
        $item->product_name  = $item->{"product_name_$lang"} ?? $item->product_name;
        $item->product_slug  = $item->{"product_slug_$lang"} ?? $item->product_slug;
        $item->short_description = $item->{"short_description_$lang"} ?? $item->short_description;

        unset(
            $item->product_name_ar,
            $item->product_name_ne,
            $item->product_slug_ar,
            $item->product_slug_ne,
            $item->short_description_ar,
            $item->short_description_ne
        );

        return $item;
    });

    /* ================= BANNER ================= */
    $banner = Banner::where('position', 'wishlist')->first();

    $bannerImages = [];
    if ($banner && $banner->image) {
        $decoded = json_decode($banner->image, true);
        $bannerImages = is_array($decoded)
            ? array_map(fn($img) => ImageHelper::getBannerImage($img), $decoded)
            : [ImageHelper::getBannerImage($banner->image)];
    }

    /* ================= RESPONSE ================= */
    return response()->json([
        'status'  => true,
        'message' => __('messages.wishlist_fetched'),
        'banner'  => $bannerImages,
        'count'   => $paginator->total(),
        'data'    => $paginator->items(),

        'pagination' => [
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'next_page'    => $paginator->nextPageUrl(),
            'prev_page'    => $paginator->previousPageUrl(),
        ]
    ]);
}
}
