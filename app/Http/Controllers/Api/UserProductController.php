<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Banner;
use App\Models\Coupon;
use App\Models\Campaign;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use Carbon\Carbon;
use App\Models\ProductVariant;
use App\Models\Brand;
use  Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductReview;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\GeneralSetting;
use App\Models\ReviewReaction;
use App\Models\Offer;
use App\Helpers\PriceCalculationHelper;
use App\Models\OrderItem;
use App\Models\ProductSize;



class UserProductController extends Controller
{

 public function get_product_detail(Request $request)
{
    try {

        /* ================= VALIDATION ================= */
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.validation_error'),
                'errors'  => $validator->errors()
            ], 422);
        }

        /* ================= GET PRODUCT ================= */
        $product = Product::with([
            'variants',
            'vendor:id,name,store_name,image,status,country_id,vendor_description,is_verified',
            'vendor.country:id,name,currency,currency_code',
            'category:id,name',
            'subCategory:id,name',
            'childCategory:id,name',
            'brand:id,name',
            'approvedReviews.user:id,name'
        ])
        ->whereHas('vendor', function ($q) {
            $q->where('status', 1)->orWhere('role', '1');
        })
        ->find($request->product_id);

        if (!$product) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.product_not_found')
            ], 404);
        }

        /* ================= BANNER (FIRST) ================= */
        $bannerData = null;


            $banner = Banner::where('position', 'product_detail')->first();
            if ($banner) {
                $banner->image = ImageHelper::getBannerImage($banner->image);

                $bannerData = [
                    'id'    => $banner->id,
                    'title' => $banner->title ?? null,
                    'banner_image' => $banner->image,
                ];
            }
        

        /* ================= VENDOR / CURRENCY ================= */
        $product->currency = \App\Helpers\GeneralHelper::get_currency_by_lang($request->get('lang'));
        $product->currency_symbol = $product->currency;

        if ($product->vendor) {
            $product->vendor->image = ImageHelper::getVendorsImage($product->vendor->image);
        }

        // ✅ VENDOR DETAIL (Rating, Store Name, Image, ID)
        $vendorId = $product->vendor_id;
        $vendorRating = ProductReview::whereHas('product', function($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->where('status', 1)->avg('rating');

        $product->vendor_data = [
            'vendor_id'    => $vendorId,
            'store_name'   => $product->vendor->store_name ?? $product->vendor->name,
            'vendor_image' => ImageHelper::getVendorsImage($product->vendor->image ?? null),
            'rating'       => round($vendorRating ?? 0, 1),
            'is_verified'  => $product->vendor->is_verified ?? 0,
        ];

        /* ================= CART / WISHLIST ================= */
        $userId    = $request->user_id;
        $ipAddress = $request->ip_address;

        $product->is_in_cart = Cart::when($userId,
            fn($q) => $q->where('user_id', $userId),
            fn($q) => $q->where('ip_address', $ipAddress)
        )->where('product_id', $product->id)->exists();

        $product->is_in_wishlist = Wishlist::when($userId,
            fn($q) => $q->where('user_id', $userId),
            fn($q) => $q->where('ip_address', $ipAddress)
        )->where('product_id', $product->id)->exists();

        /* ================= CAMPAIGN ================= */
        $product->campaign = $this->campaignInfo($product);

        /* ================= OFFERS ================= */
        $offer_ids = $product->offer_id ? json_decode($product->offer_id, true) : [];
        if (!is_array($offer_ids)) $offer_ids = [$offer_ids];

        $product->offers = Offer::whereIn('id', $offer_ids)
            ->get()
            ->map(fn($o) => [
                'id'   => $o->id,
                'name' => $o->code ?? $o->name,
            ]);

        $product->offer_id   = $offer_ids;
        $product->product_in = json_decode($product->product_in, true) ?? [];

        /* ================= LANGUAGE ================= */
        $lang = $request->get('lang', app()->getLocale());

        $product->name               = $product->{"name_$lang"} ?? $product->name;
        $product->short_description  = $product->{"short_description_$lang"} ?? $product->short_description;
        $product->description        = $product->{"description_$lang"} ?? $product->description;

        /* ================= VARIANTS ================= */
        $allColors = [];
        $allSizes  = [];

        foreach ($product->variants as $variant) {

            $variant->original_price = $variant->price;

            $calc = PriceCalculationHelper::calculateItemPrice($product, $variant->id);

            /* DISCOUNT */
            $variant->discount = null;

            if ($calc['product_unit_discount'] > 0) {
                $type = strtolower($variant->discount_type);

                if (in_array($type, ['percent', 'percentage', '%'])) {
                    $variant->discount = $variant->discount_value . ' % Off';
                } else {
                    $variant->discount = $variant->discount_value . ' off';
                }
            }

            $variant->actual_price       = $calc['price_after_discounts'];
            $variant->offer_discount     = $calc['offer_unit_discount'];
            $variant->campaign_discount  = $calc['campaign_unit_discount'];

            $variant->product_variant_label =
                \App\Helpers\GeneralHelper::getVariantLabel($variant->product_variant, $lang);

            /* COLORS */
            if ($variant->color) {
                $allColors[] = $variant->color;
            }

            /* SIZES */
            $sizes = json_decode($variant->size, true);
            if (is_array($sizes)) {
                $sizeValues = ProductSize::whereIn('id', $sizes)->pluck('name')->toArray();
                $variant->size = $sizeValues;
                $allSizes = array_merge($allSizes, $sizeValues);
            }

            /* IMAGES */
            $images = [];

            if ($variant->image) {
                $decoded = json_decode($variant->image, true);

                if (is_array($decoded)) {
                    foreach ($decoded as $img) {
                        $images[] = ImageHelper::getProductImage($img);
                    }
                } else {
                    foreach (explode(',', $variant->image) as $img) {
                        $images[] = ImageHelper::getProductImage(trim($img));
                    }
                }
            }

            $variant->images = $images;

            unset($variant->image);
        }

        $product->colors = array_values(array_unique($allColors));
        $product->sizes  = array_values(array_unique($allSizes));

        /* ================= DEFAULT VARIANT ================= */
        if ($product->variants->isNotEmpty()) {
            $v = $product->variants->first();
            $product->price          = $v->actual_price;
            $product->original_price = $v->original_price;
            $product->actual_price   = $v->actual_price;
            $product->discount       = $v->discount;
        }

        /* ================= RELATED PRODUCTS ================= */
        $relatedProducts = Product::where('status', 1)
            ->where('id', '!=', $product->id)
            ->where(function($q) use ($product) {
                $q->where('category_id', $product->category_id)
                  ->orWhere('subcategory_id', $product->subcategory_id);
            })
            ->with([
                'firstVariant:id,product_id,price,image,discount_type,discount_value',
                'approvedReviews',
                'vendor:id,country_id',
                'vendor.country:id,currency,currency_code'
            ])
            ->limit(10)
            ->get()
            ->map(function($p) use ($lang) {
                $v = $p->firstVariant;
                $priceData = PriceCalculationHelper::calculateItemPrice($p, $v->id ?? null);
                
                $discount = null;
                if ($priceData['product_unit_discount'] > 0 && $v) {
                    $type = strtolower($v->discount_type);
                    if (in_array($type, ['percent', 'percentage', '%'])) {
                        $discount = $v->discount_value . ' % Off';
                    } else {
                        $discount = $v->discount_value . ' off';
                    }
                }

                // Related Product Image (Variant first, then thumbnail)
                $relImage = $p->thumbnail;
                if ($v && $v->image) {
                    $decoded = json_decode($v->image, true);
                    if (is_array($decoded) && !empty($decoded)) {
                        $relImage = $decoded[0];
                    } else {
                        $parts = explode(',', $v->image);
                        if (!empty($parts[0])) $relImage = trim($parts[0]);
                    }
                }

                return [
                    'id'              => $p->id,
                    'name'            => $p->{"name_$lang"} ?? $p->name,
                    'slug'            => $p->{"slug_$lang"} ?? $p->slug,
                    'image'           => ImageHelper::getProductImage($relImage),
                    'price'           => $priceData['price_after_discounts'],
                    'original_price'  => $v->price ?? $p->price,
                    'discount'        => $discount,
                    'currency'        => \App\Helpers\GeneralHelper::get_currency_by_lang($lang),
                    'rating'          => round($p->approvedReviews->avg('rating') ?? 0, 1),
                    'review_count'    => $p->approvedReviews->count(),
                ];
            });

        /* ================= FINAL RESPONSE ================= */
        return response()->json([
            'status' => true,

            // ✅ Banner FIRST
            'banner' => $bannerData,

            // ✅ Related Products
            'related_products' => $relatedProducts,

            // ✅ Product AFTER
            'data'   => $product
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'status'  => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}


    // public function product_list(Request $request)

    // {

    //     $locale = $request->get('lang');

    //     if (in_array($locale, ['en', 'ar', 'ne', 'zh', 'ja', 'hi', 'de', 'fr', 'ko', 'pt', 'es', 'ru', 'it', 'tr', 'th', 'vi'])) {

    //         app()->setLocale($locale);
    //     }

    //     $validator = Validator::make($request->all(), [

    //         'vendor_id'        => 'nullable',

    //         'category_id'      => 'nullable',

    //         'subcategory_id'   => 'nullable',

    //         'childcategory_id' => 'nullable',

    //         'brand_id'         => 'nullable',

    //         'search'           => 'nullable|string',

    //         'sort_by'          => 'nullable|in:low_to_high,high_to_low,1,2,3,4',

    //         'per_page'         => 'nullable|integer|min:1|max:100',

    //     ]);



    //     if ($validator->fails()) {

    //         return response()->json([

    //             'status'  => false,

    //             'message' => __('messages.validation_error'),

    //             'errors'  => $validator->errors()

    //         ], 422);
    //     }



    //     $userId = $request->user_id;



    //     $query = Product::query()

    //         ->select('products.*')

    //         ->inCustomerCountry($userId)

    //         ->with([

    //             'variants:id,product_id,price,image,color,size,discount_type,discount_value',

    //             'vendor:id,name,store_name,image,is_verified',

    //             'category:id,name,name_ar,name_ne',

    //             'subCategory:id,name,name_ar,name_ne',

    //             'childCategory:id,name,name_ar,name_ne',

    //             'brand:id,name',

    //             'approvedReviews'

    //         ])

    //         ->where('products.status', 1);



    //     /* -------------------------

    //      Filters

    //      ------------------------- */

    //     if ($request->filled('search')) {

    //         $searchTerm = trim($request->search);

    //         // 🔹 Get matching category IDs
    //         $categoryIds = Category::where(function ($q) use ($searchTerm) {
    //             $q->where('name', 'LIKE', "%{$searchTerm}%")
    //                 ->orWhere('name_ar', 'LIKE', "%{$searchTerm}%")
    //                 ->orWhere('name_ne', 'LIKE', "%{$searchTerm}%");
    //         })->pluck('id')->toArray();

    //         // 🔹 Get matching subcategory IDs
    //         $subcategoryIds = SubCategory::where(function ($q) use ($searchTerm) {
    //             $q->where('name', 'LIKE', "%{$searchTerm}%")
    //                 ->orWhere('name_ar', 'LIKE', "%{$searchTerm}%")
    //                 ->orWhere('name_ne', 'LIKE', "%{$searchTerm}%");
    //         })->pluck('id')->toArray();

    //         // 🔹 Get matching childcategory IDs
    //         $childCategoryIds = ChildCategory::where(function ($q) use ($searchTerm) {
    //             $q->where('name', 'LIKE', "%{$searchTerm}%")
    //                 ->orWhere('name_ar', 'LIKE', "%{$searchTerm}%")
    //                 ->orWhere('name_ne', 'LIKE', "%{$searchTerm}%");
    //         })->pluck('id')->toArray();

    //         // 🔹 Apply search conditions
    //         $query->where(function ($q) use (
    //             $searchTerm,
    //             $categoryIds,
    //             $subcategoryIds,
    //             $childCategoryIds
    //         ) {

    //             // Product name match
    //             $q->where('products.name', 'LIKE', "%{$searchTerm}%")
    //                 ->orWhere('products.name_ar', 'LIKE', "%{$searchTerm}%")
    //                 ->orWhere('products.name_ne', 'LIKE', "%{$searchTerm}%");

    //             // Category match → show all products of that category
    //             if (!empty($categoryIds)) {
    //                 $q->orWhereIn('products.category_id', $categoryIds);
    //             }

    //             // Subcategory match → show all products
    //             if (!empty($subcategoryIds)) {
    //                 $q->orWhereIn('products.subcategory_id', $subcategoryIds);
    //             }

    //             // Child category match → show all products
    //             if (!empty($childCategoryIds)) {
    //                 $q->orWhereIn('products.child_category_id', $childCategoryIds);
    //             }
    //         });
    //     }

    //     if ($request->filled('vendor_id')) {

    //         $vendorIds = is_array($request->vendor_id) ? $request->vendor_id : explode(',', $request->vendor_id);

    //         $query->whereIn('products.vendor_id', $vendorIds);
    //     }



    //     if ($request->filled('category_id')) {

    //         $categoryIds = is_array($request->category_id) ? $request->category_id : explode(',', $request->category_id);

    //         $query->whereIn('products.category_id', $categoryIds);
    //     }



    //     if ($request->filled('subcategory_id')) {

    //         $subcategoryIds = is_array($request->subcategory_id) ? $request->subcategory_id : explode(',', $request->subcategory_id);

    //         $query->whereIn('products.subcategory_id', $subcategoryIds);
    //     }



    //     if ($request->filled('childcategory_id')) {

    //         $childCategoryIds = is_array($request->childcategory_id) ? $request->childcategory_id : explode(',', $request->childcategory_id);

    //         $query->whereIn('products.child_category_id', $childCategoryIds);
    //     }



    //     if ($request->filled('brand_id')) {

    //         $brandIds = is_array($request->brand_id) ? $request->brand_id : explode(',', $request->brand_id);

    //         $query->whereIn('products.brand_id', $brandIds);
    //     }



    //     /* -------------------------

    //          Sorting

    //          ------------------------- */

    //     if ($request->filled('sort_by')) {

    //         switch ($request->sort_by) {

    //             case 'low_to_high':

    //                 $query->orderBy(

    //                     ProductVariant::select('final_price')

    //                         ->whereColumn('product_id', 'products.id')

    //                         ->orderBy('final_price', 'asc')

    //                         ->limit(1),

    //                     'asc'

    //                 );

    //                 break;



    //             case 'high_to_low':

    //                 $query->orderBy(

    //                     ProductVariant::select('final_price')

    //                         ->whereColumn('product_id', 'products.id')

    //                         ->orderBy('final_price', 'desc')

    //                         ->limit(1),

    //                     'desc'

    //                 );

    //                 break;



    //             case '1': // Bestseller

    //             case '2': // Trending

    //             case '3': // Popular

    //             case '4': // Deal

    //                 $query->where(function ($q) use ($request) {

    //                     $q->whereJsonContains('products.product_in', (string)$request->sort_by)

    //                         ->orWhereJsonContains('products.product_in', (int)$request->sort_by);
    //                 })

    //                     ->latest('products.id');

    //                 break;



    //             default:

    //                 $query->latest('products.id');
    //         }
    //     } else {

    //         // Default sorting by product_in sequence 1, 2, 3, 4

    //         $query->inRandomOrder();
    //     }



    //     $perPage = $request->input('per_page', 12);

    //     $paginator = $query->paginate($perPage);



    //     $userId = $request->user_id;

    //     $ipAddress = $request->ip_address;



    //     $cartProductIds = Cart::when($userId, function ($q) use ($userId) {

    //         return $q->where('user_id', $userId);
    //     }, function ($q) use ($ipAddress) {

    //         return $q->where('ip_address', $ipAddress);
    //     })

    //         ->pluck('product_id')

    //         ->toArray();



    //     $wishlistProductIds = Wishlist::when($userId, function ($q) use ($userId) {

    //         return $q->where('user_id', $userId);
    //     }, function ($q) use ($ipAddress) {

    //         return $q->where('ip_address', $ipAddress);
    //     })

    //         ->pluck('product_id')

    //         ->toArray();



    //     $products = collect($paginator->items())->map(

    //         fn($product) =>

    //         $this->formatProduct($product, $request, null, $cartProductIds, $wishlistProductIds)

    //     );



    //     /* -------------------------

    //          SIDE DATA

    //          ------------------------- */

    //     $lang = $request->get('lang', app()->getLocale());



    //     $categoryQuery = Category::where('is_active', 1);



    //     if ($request->filled('vendor_id')) {

    //         $categoryQuery->whereHas('products', function ($q) use ($request) {

    //             $q->where('vendor_id', $request->vendor_id)->where('status', 1);
    //         });
    //     }



    //     $categories = $categoryQuery->latest()->get();

    //     foreach ($categories as $cat) {

    //         $cat->image = ImageHelper::getCategoryImage($cat->image);

    //         $cat->name = $cat->{"name_$lang"} ?? $cat->name;
    //     }



    //     // Subcategories

    //     $subcategoryQuery = SubCategory::where('is_active', 1);

    //     if ($request->filled('category_id')) {

    //         $categoryIds = is_array($request->category_id) ? $request->category_id : explode(',', $request->category_id);

    //         $subcategoryQuery->whereIn('category_id', $categoryIds);
    //     }

    //     if ($request->filled('vendor_id')) {

    //         $vendorIds = is_array($request->vendor_id) ? $request->vendor_id : explode(',', $request->vendor_id);

    //         $subcategoryQuery->whereHas('products', function ($q) use ($vendorIds) {

    //             $q->whereIn('vendor_id', $vendorIds)->where('status', 1);
    //         });
    //     }

    //     $subcategories = $subcategoryQuery->latest()->get();

    //     foreach ($subcategories as $sub) {

    //         $sub->name = $sub->{"name_$lang"} ?? $sub->name;
    //     }



    //     // Child categories

    //     $childcategoryQuery = ChildCategory::where('is_active', 1);

    //     if ($request->filled('subcategory_id')) {

    //         $subcategoryIds = is_array($request->subcategory_id) ? $request->subcategory_id : explode(',', $request->subcategory_id);

    //         $childcategoryQuery->whereIn('subcategory_id', $subcategoryIds);
    //     } elseif ($request->filled('category_id')) {

    //         $categoryIds = is_array($request->category_id) ? $request->category_id : explode(',', $request->category_id);

    //         $childcategoryQuery->whereIn('category_id', $categoryIds);
    //     }

    //     if ($request->filled('vendor_id')) {

    //         $vendorIds = is_array($request->vendor_id) ? $request->vendor_id : explode(',', $request->vendor_id);

    //         $childcategoryQuery->whereHas('products', function ($q) use ($vendorIds) {

    //             $q->whereIn('vendor_id', $vendorIds)->where('status', 1);
    //         });
    //     }

    //     $childcategories = $childcategoryQuery->latest()->get();

    //     foreach ($childcategories as $child) {

    //         $child->name = $child->{"name_$lang"} ?? $child->name;
    //     }



    //     $brandsQuery = Brand::where('is_active', 1);

    //     if ($request->filled('vendor_id')) {

    //         $vendorIds = is_array($request->vendor_id) ? $request->vendor_id : explode(',', $request->vendor_id);

    //         $brandsQuery->whereHas('products', function ($q) use ($vendorIds) {

    //             $q->whereIn('vendor_id', $vendorIds)->where('status', 1);
    //         });
    //     }

    //     if ($request->filled('category_id')) {

    //         $categoryIds = is_array($request->category_id) ? $request->category_id : explode(',', $request->category_id);

    //         $brandsQuery->whereHas('products', function ($q) use ($categoryIds) {

    //             $q->whereIn('category_id', $categoryIds)->where('status', 1);
    //         });
    //     }

    //     if ($request->filled('subcategory_id')) {

    //         $subcategoryIds = is_array($request->subcategory_id) ? $request->subcategory_id : explode(',', $request->subcategory_id);

    //         $brandsQuery->whereHas('products', function ($q) use ($subcategoryIds) {

    //             $q->whereIn('subcategory_id', $subcategoryIds)->where('status', 1);
    //         });
    //     }

    //     if ($request->filled('childcategory_id')) {

    //         $childCategoryIds = is_array($request->childcategory_id) ? $request->childcategory_id : explode(',', $request->childcategory_id);

    //         $brandsQuery->whereHas('products', function ($q) use ($childCategoryIds) {

    //             $q->whereIn('child_category_id', $childCategoryIds)->where('status', 1);
    //         });
    //     }

    //     $brands = $brandsQuery->latest()->get();

    //     foreach ($brands as $brand) {

    //         $brand->logo = ImageHelper::getBrandImage($brand->logo);
    //     }



    //     /* -------------------------

    //          PRODUCT_IN GROUPS

    //          ------------------------- */

    //     $getProductIn = fn($val) =>

    //     Product::where('status', 1)

    //         ->inCustomerCountry($userId)

    //         ->whereHas('vendor', function ($q) {

    //             $q->where('status', 1);
    //         })

    //         ->when($request->vendor_id, function ($q) use ($request) {

    //             $vendorIds = is_array($request->vendor_id) ? $request->vendor_id : explode(',', $request->vendor_id);

    //             return $q->whereIn('vendor_id', $vendorIds);
    //         })

    //         ->when($request->category_id, function ($q) use ($request) {

    //             $categoryIds = is_array($request->category_id) ? $request->category_id : explode(',', $request->category_id);

    //             return $q->whereIn('category_id', $categoryIds);
    //         })

    //         ->when($request->subcategory_id, function ($q) use ($request) {

    //             $subcategoryIds = is_array($request->subcategory_id) ? $request->subcategory_id : explode(',', $request->subcategory_id);

    //             return $q->whereIn('subcategory_id', $subcategoryIds);
    //         })

    //         ->when($request->childcategory_id, function ($q) use ($request) {

    //             $childCategoryIds = is_array($request->childcategory_id) ? $request->childcategory_id : explode(',', $request->childcategory_id);

    //             return $q->whereIn('child_category_id', $childCategoryIds);
    //         })

    //         ->when($request->brand_id, function ($q) use ($request) {

    //             $brandIds = is_array($request->brand_id) ? $request->brand_id : explode(',', $request->brand_id);

    //             return $q->whereIn('brand_id', $brandIds);
    //         })

    //         ->when($request->search, function ($q) use ($request) {

    //             $searchTerm = trim($request->search);

    //             return $q->where(function ($q2) use ($searchTerm) {

    //                 $q2->where('products.name', 'LIKE', "%{$searchTerm}%")

    //                     ->orWhere('products.name_ar', 'LIKE', "%{$searchTerm}%")

    //                     ->orWhere('products.name_ne', 'LIKE', "%{$searchTerm}%")

    //                     ->orWhereHas('subCategory', function ($sub) use ($searchTerm) {

    //                         $sub->where('name', 'LIKE', "%{$searchTerm}%")

    //                             ->orWhere('name_ar', 'LIKE', "%{$searchTerm}%")

    //                             ->orWhere('name_ne', 'LIKE', "%{$searchTerm}%");
    //                     });
    //             });
    //         })

    //         ->where(function ($q) use ($val) {

    //             $q->whereJsonContains('product_in', (string)$val)

    //                 ->orWhereJsonContains('product_in', (int)$val);
    //         })

    //         ->inRandomOrder()

    //         ->with([

    //             'firstVariant:id,product_id,price,image,discount_type,discount_value',

    //             'vendor:id,name,store_name,image,is_verified',

    //             'category:id,name,name_ar,name_ne',

    //             'subCategory:id,name,name_ar,name_ne',

    //             'childCategory:id,name,name_ar,name_ne',

    //             'brand:id,name',

    //             'approvedReviews'

    //         ])

    //         ->limit(15)

    //         ->get()

    //         ->map(fn($p) => $this->formatProduct($p, $request, $val, $cartProductIds, $wishlistProductIds));



    //     return response()->json([

    //         'status'               => true,

    //         'data'                 => $products,



    //         'side_menu' => [

    //             __('messages.brands_label'),

    //             __('messages.subcategory_label'),

    //             __('messages.childcategories_label'),

    //             __('messages.bestseller_products'),

    //             __('messages.trending_products'),

    //             __('messages.popular_products'),

    //             __('messages.ondeal_products'),

    //         ],

    //         'subcategories'        => $subcategories,

    //         'childcategories'      => $childcategories,

    //         'bestseller_products'  => $getProductIn(1),

    //         'trending_products'    => $getProductIn(2),

    //         'popular_products'     => $getProductIn(3),

    //         'ondeal_products'      => $getProductIn(4),

    //         'brands'               => $brands,



    //         'pagination'           => [

    //             'current_page' => $paginator->currentPage(),

    //             'last_page'    => $paginator->lastPage(),

    //             'per_page'     => $paginator->perPage(),

    //             'total'        => $paginator->total(),

    //             'next_page_url' => $paginator->nextPageUrl(),

    //             'prev_page_url' => $paginator->previousPageUrl(),

    //         ],

    //     ]);
    // }


 public function product_list(Request $request)
{
    $locale = $request->get('lang');

    if (in_array($locale, ['en', 'ar', 'ne', 'zh', 'ja', 'hi', 'de', 'fr', 'ko', 'pt', 'es', 'ru', 'it', 'tr', 'th', 'vi'])) {
        app()->setLocale($locale);
    }

    $validator = Validator::make($request->all(), [
        'vendor_id'        => 'nullable',
        'category_id'      => 'nullable',
        'subcategory_id'   => 'nullable',
        'childcategory_id' => 'nullable',
        'brand_id'         => 'nullable',
        'search'           => 'nullable|string',
        'sort_by'          => 'nullable|in:low_to_high,high_to_low,1,2,3,4',
        'per_page'         => 'nullable|integer|min:1|max:100',
        'min_discount'     => 'nullable|numeric',
        'rating'           => 'nullable|numeric|min:1|max:5',
        'in_stock'         => 'nullable|boolean',
        'offer_type'       => 'nullable|in:1,2,3,4', // 1=bestseller,2=trending...
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => __('messages.validation_error'),
            'errors'  => $validator->errors()
        ], 422);
    }

    $userId = $request->user_id;

    /* =========================
       COMMON QUERY BUILDER
    ========================= */
    $buildQuery = function () use ($request, $userId) {

        $query = Product::query()
            ->select('products.*')
            ->inCustomerCountry($userId)
            ->where('products.status', 1);

        /* 🔍 SEARCH */
        if ($request->filled('search')) {

            $searchTerm = trim($request->search);

            $categoryIds = Category::where('name', 'LIKE', "%{$searchTerm}%")->pluck('id');
            $subcategoryIds = SubCategory::where('name', 'LIKE', "%{$searchTerm}%")->pluck('id');
            $childCategoryIds = ChildCategory::where('name', 'LIKE', "%{$searchTerm}%")->pluck('id');

            $query->where(function ($q) use ($searchTerm, $categoryIds, $subcategoryIds, $childCategoryIds) {
                $q->where('products.name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('products.name_ar', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('products.name_ne', 'LIKE', "%{$searchTerm}%")
                  ->orWhereIn('products.category_id', $categoryIds)
                  ->orWhereIn('products.subcategory_id', $subcategoryIds)
                  ->orWhereIn('products.child_category_id', $childCategoryIds);
            });
        }

        /* 🎯 BASIC FILTERS */
        foreach (['vendor_id','category_id','subcategory_id','childcategory_id','brand_id'] as $field) {

            if ($request->filled($field)) {

                $values = is_array($request->$field)
                    ? $request->$field
                    : explode(',', $request->$field);

                $column = $field === 'childcategory_id' ? 'child_category_id' : $field;

                $query->whereIn("products.$column", $values);
            }
        }


        /* 🔥 DISCOUNT FILTER */
        if ($request->filled('min_discount')) {

            $query->whereHas('variants', function ($q) use ($request) {

              $q->where(function ($q2) use ($request) {

                // Percentage type match
                $q2->where(function ($q3) use ($request) {
                    $q3->whereIn(DB::raw('LOWER(discount_type)'), [
                            'percent', 'percentage', '%'
                        ])
                    ->where('discount_value', '>=', $request->min_discount);
                });

                // Flat type match
                $q2->orWhere(function ($q3) use ($request) {
                    $q3->whereIn(DB::raw('LOWER(discount_type)'), [
                            'flat', 'amount', 'fixed'
                        ])
                    ->where('discount_value', '>=', $request->min_discount);
                });

            });

            });
        }

        /* ⭐ RATING FILTER */
        if ($request->filled('rating')) {

            $query->whereHas('approvedReviews', function ($q) use ($request) {
                $q->where('rating', '>=', $request->rating);
            });
        }

        /* 🏷️ OFFERS FILTER (product_in JSON) */
        if ($request->filled('offer_type')) {

            $query->where(function ($q) use ($request) {
                $q->whereJsonContains('products.product_in', (string)$request->offer_type)
                  ->orWhereJsonContains('products.product_in', (int)$request->offer_type);
            });
        }

        /* 📦 AVAILABILITY FILTER */
        if ($request->filled('in_stock')) {

            $query->whereHas('variants', function ($q) {
                $q->where('stock', '>', 0);
            });
        }

        return $query;
    };

    /* =========================
       MAIN QUERY
    ========================= */
    $query = $buildQuery()->with([
        'variants:id,product_id,price,image,color,size,discount_type,discount_value,stock,product_variant',
        'vendor:id,name,store_name,image,is_verified',
        'category:id,name,name_ar,name_ne',
        'subCategory:id,name,name_ar,name_ne',
        'childCategory:id,name,name_ar,name_ne',
        'brand:id,name',
        'approvedReviews'
    ]);

    /* 🔽 SORTING */
    if ($request->filled('sort_by')) {

        switch ($request->sort_by) {

            case 'low_to_high':
                $query->orderBy(
                    ProductVariant::select('final_price')
                        ->whereColumn('product_id', 'products.id')
                        ->orderBy('final_price', 'asc')
                        ->limit(1),
                    'asc'
                );
                break;

            case 'high_to_low':
                $query->orderBy(
                    ProductVariant::select('final_price')
                        ->whereColumn('product_id', 'products.id')
                        ->orderBy('final_price', 'desc')
                        ->limit(1),
                    'desc'
                );
                break;

            case '1':
            case '2':
            case '3':
            case '4':
                $query->whereJsonContains('products.product_in', (string)$request->sort_by)
                      ->latest('products.id');
                break;

            default:
                $query->latest('products.id');
        }

    } else {
        $query->inRandomOrder();
    }

    $paginator = $query->paginate($request->input('per_page', 12));

    $products = collect($paginator->items())->map(
        fn($product) => $this->formatProduct($product, $request)
    );

    /* =========================
       SIDEBAR FILTERS
    ========================= */
    $baseQuery = $buildQuery();

    $sidebar = $this->getProductFilters($request, $baseQuery);

    return response()->json([
        'status' => true,
        'sidebar' => $sidebar,
        'data'   => $products,
        'pagination' => [
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
        ],
    ]);
}
  private function formatProduct($product, $request, $product_in = null, $cartProductIds = [], $wishlistProductIds = [])
{
    $image = null;
    $price = null;
    $original_price = null;
    $discount = null;
    $lang = $request->get('lang', app()->getLocale());

    // Process Variants
    $allColors = [];
    $allSizes  = [];
    $processedVariants = [];

    if ($product->variants->isNotEmpty()) {
        foreach ($product->variants as $variant) {

            $calc = PriceCalculationHelper::calculateItemPrice($product, $variant->id);

            // ✅ DISCOUNT FORMAT LOGIC
            $formattedDiscount = null;
            if ($calc['product_unit_discount'] > 0) {

                $type = strtolower($variant->discount_type);

                if (in_array($type, ['percent', 'percentage', '%'])) {
                    $formattedDiscount = $variant->discount_value . ' % Off';
                } else {
                    $formattedDiscount = $variant->discount_value . ' off';
                }
            }

            $variantData = [
                'id' => $variant->id,
                'product_id' => $variant->product_id,
                'price' => $variant->price,
                'original_price' => $variant->price,
                'color' => $variant->{"color_$lang"} ?? $variant->color,
                'stock' => $variant->stock,
                'discount_type' => $variant->discount_type,
                'discount_value' => $variant->discount_value,
                'product_variant' => $variant->product_variant,
                'product_variant_label' => \App\Helpers\GeneralHelper::getVariantLabel($variant->product_variant, $lang),

                'actual_price' => $calc['price_after_discounts'],
                'offer_discount' => $calc['offer_unit_discount'],
                'campaign_discount' => $calc['campaign_unit_discount'],

                // ✅ FINAL DISCOUNT
                'discount' => $formattedDiscount,
                'formatted_discount' => $formattedDiscount,
            ];

            // Colors
            if ($variant->color) {
                $allColors[] = $variant->color;
            }

            // Sizes
            $sizes = json_decode($variant->size, true);
            $sizeValues = [];

            if (is_array($sizes) && !empty($sizes)) {
                $sizeValues = \App\Models\ProductSize::whereIn('id', $sizes)->pluck('name')->toArray();
                $allSizes = array_merge($allSizes, $sizeValues);
            }

            $variantData['size'] = $sizeValues;

            // Images
            $images = [];

            if ($variant->image) {
                if (str_starts_with($variant->image, '[')) {
                    foreach (json_decode($variant->image, true) ?? [] as $img) {
                        $images[] = ImageHelper::getProductImage($img);
                    }
                } elseif (str_contains($variant->image, ',')) {
                    foreach (explode(',', $variant->image) as $img) {
                        $images[] = ImageHelper::getProductImage(trim($img));
                    }
                } else {
                    $images[] = ImageHelper::getProductImage($variant->image);
                }
            }

            $variantData['images'] = $images;
            $variantData['image'] = $images[0] ?? ImageHelper::getProductImage($product->thumbnail);

            $processedVariants[] = $variantData;
        }
    }

    // Default Variant
    $variant = $product->variants->first() ?? $product->firstVariant;

    if ($variant) {

        $calc = PriceCalculationHelper::calculateItemPrice($product, $variant->id);

        $image = ImageHelper::getProductImage(($variant->image ?? null) ?: ($product->thumbnail ?? null));
        $original_price = $variant->price;
        $price = $calc['price_after_discounts'];

        // ✅ DEFAULT PRODUCT DISCOUNT
        if ($calc['product_unit_discount'] > 0) {

            $type = strtolower($variant->discount_type);

            if (in_array($type, ['percent', 'percentage', '%'])) {
                $discount = $variant->discount_value . ' % Off';
            } else {
                $discount = $variant->discount_value . ' off';
            }
        }
    }

    // Currency
    $currency = \App\Helpers\GeneralHelper::get_currency_by_lang($lang);

    return [
        'id'             => $product->id,
        'name'           => $product->{"name_$lang"} ?? $product->name,
        'slug'           => $product->{"slug_$lang"} ?? $product->slug,
        'image'          => $image,
        'price'          => $price,
        'original_price' => $original_price,
        'actual_price'   => $price,
        'discount'       => $discount,
        'currency'       => $currency,

        'product_variant_label' => \App\Helpers\GeneralHelper::getVariantLabel($variant->product_variant, $lang),

        'campaign'       => $this->campaignInfo($product),
        'rating'         => round($product->approvedReviews->avg('rating') ?? 0, 1),
        'rating_stars'   => round($product->approvedReviews->avg('rating') ?? 0, 1),
        'review_count'   => $product->approvedReviews->count(),
        'product_in'     => $product_in ?? $product->product_in,

        // Variants
        'variants'       => $processedVariants,
        'colors'         => array_values(array_unique($allColors)),
        'sizes'          => array_values(array_unique($allSizes)),

        // Vendor Policy
        'vendor_warranty' => $product->vendor_warranty ?? null,
        'vendor_payment'  => $product->vendor_payment ?? null,
        'vendor_return'   => $product->vendor_return ?? null,
        'vendor_delivery' => $product->vendor_delivery ?? null,

        // Relations
        'category_id'         => $product->category_id,
        'category_name'       => $product->category->{"name_$lang"} ?? ($product->category->name ?? null),
        'subcategory_id'      => $product->subcategory_id,
        'subcategory_name'    => $product->subCategory->{"name_$lang"} ?? ($product->subCategory->name ?? null),
        'child_category_id'   => $product->child_category_id,
        'child_category_name' => $product->childCategory->{"name_$lang"} ?? ($product->childCategory->name ?? null),
        'brand_id'            => $product->brand_id,
        'brand_name'          => $product->brand->name ?? null,

        'is_in_cart'     => in_array($product->id, $cartProductIds),
        'is_in_wishlist' => in_array($product->id, $wishlistProductIds),

        // Vendor
        'vendor_id'     => $product->vendor_id,
        'vendor_name'   => $product->vendor->name ?? null,
        'vendor_store'  => $product->vendor->store_name ?? null,
        'vendor_image'  => ImageHelper::getVendorsImage($product->vendor->image ?? null),
        'is_verify'     => $product->vendor
            ? ($product->vendor->role == '1' ? 1 : ($product->vendor->is_verified ?? 0))
            : 0,

        // Tags
        'warranty' => $product->vendor_warranty ? $product->vendor_warranty . " Warranty" : null,
        'return'   => $product->vendor_return ? "Easy & Hassle-Free Returns" : null,
        'delivery' => $product->vendor_delivery ? "Free Delivery" : null,
        'payment'  => $product->vendor_payment ? "100% Secure Payments" : null,
    ];
}

    private function campaignInfo($product)
    {
        return null;
    }



    public function get_bestsellr_product(Request $request)
    {
        $locale = $request->get('lang');
        if (in_array($locale, ['en', 'ar', 'ne', 'zh', 'ja', 'hi', 'de', 'fr', 'ko', 'pt', 'es', 'ru', 'it', 'tr', 'th', 'vi'])) {
            app()->setLocale($locale);
        }
        $product_in = 1;
        $userId = $request->user_id;
        $ipAddress = $request->ip_address;

        $cartProductIds = Cart::when($userId, function ($q) use ($userId) {
            return $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            return $q->where('ip_address', $ipAddress);
        })
            ->pluck('product_id')
            ->toArray();

        $wishlistProductIds = Wishlist::when($userId, function ($q) use ($userId) {
            return $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            return $q->where('ip_address', $ipAddress);
        })
            ->pluck('product_id')
            ->toArray();

        $bestseller_products = Product::where('status', 1)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1)->orWhere('role', '1');
            })
            ->inCustomerCountry($userId)
            ->whereJsonContains('product_in', '1')

            ->with([
                'variants',
                'vendor:id,name,store_name,image,is_verified',
                'approvedReviews',
                'category:id,name,name_ar,name_ne',
                'subCategory:id,name,name_ar,name_ne',
                'childCategory:id,name,name_ar,name_ne',
                'brand:id,name'
            ])
            ->limit(15)
            ->get()
            ->map(fn($product) => $this->formatProduct($product, $request, $product_in, $cartProductIds, $wishlistProductIds));



        return response()->json([
            'status' => true,
            'data'   => $bestseller_products
        ]);
    }

    public function get_featured_product(Request $request)
    {
        $locale = $request->get('lang');
        if (in_array($locale, ['en', 'ar', 'ne', 'zh', 'ja', 'hi', 'de', 'fr', 'ko', 'pt', 'es', 'ru', 'it', 'tr', 'th', 'vi'])) {
            app()->setLocale($locale);
        }
        $userId = $request->user_id;
        $ipAddress = $request->ip_address;

        $cartProductIds = Cart::when($userId, function ($q) use ($userId) {
            return $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            return $q->where('ip_address', $ipAddress);
        })
            ->pluck('product_id')
            ->toArray();

        $wishlistProductIds = Wishlist::when($userId, function ($q) use ($userId) {
            return $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            return $q->where('ip_address', $ipAddress);
        })
            ->pluck('product_id')
            ->toArray();

        $featured_products = Product::where('status', 1)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1)->orWhere('role', '1');
            })
            ->where('is_featured', 1)
            ->inCustomerCountry($userId)
            ->with([
                'variants',
                'vendor:id,name,store_name,image,is_verified',
                'approvedReviews',
                'category:id,name,name_ar,name_ne',
                'subCategory:id,name,name_ar,name_ne',
                'childCategory:id,name,name_ar,name_ne',
                'brand:id,name'
            ])
            ->limit(15)
            ->get()
            ->map(fn($product) => $this->formatProduct($product, $request, null, $cartProductIds, $wishlistProductIds));

        return response()->json([
            'status' => true,
            'data'   => $featured_products
        ]);
    }

    public function get_trending_product(Request $request)
    {
        $locale = $request->get('lang');
        if (in_array($locale, ['en', 'ar', 'ne', 'zh', 'ja', 'hi', 'de', 'fr', 'ko', 'pt', 'es', 'ru', 'it', 'tr', 'th', 'vi'])) {
            app()->setLocale($locale);
        }
        $product_in = 2;
        $userId = $request->user_id;
        $ipAddress = $request->ip_address;

        $cartProductIds = Cart::when($userId, function ($q) use ($userId) {
            return $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            return $q->where('ip_address', $ipAddress);
        })
            ->pluck('product_id')
            ->toArray();

        $wishlistProductIds = Wishlist::when($userId, function ($q) use ($userId) {
            return $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            return $q->where('ip_address', $ipAddress);
        })
            ->pluck('product_id')
            ->toArray();

        $trending_products = Product::where('status', 1)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1)->orWhere('role', '1');
            })
            ->inCustomerCountry($userId)
            ->whereJsonContains('product_in', '2')
            ->with([
                'variants',
                'vendor:id,name,store_name,image,is_verified',
                'approvedReviews',
                'category:id,name,name_ar,name_ne',
                'subCategory:id,name,name_ar,name_ne',
                'childCategory:id,name,name_ar,name_ne',
                'brand:id,name'
            ])
            ->limit(15)
            ->get()
            ->map(fn($product) => $this->formatProduct($product, $request, $product_in, $cartProductIds, $wishlistProductIds));




        return response()->json([
            'status' => true,
            'data'   => $trending_products
        ]);
    }

    public function get_popular_product(Request $request)
    {
        $locale = $request->get('lang');
        if (in_array($locale, ['en', 'ar', 'ne', 'zh', 'ja', 'hi', 'de', 'fr', 'ko', 'pt', 'es', 'ru', 'it', 'tr', 'th', 'vi'])) {
            app()->setLocale($locale);
        }
        $product_in = 3;
        $userId = $request->user_id;
        $ipAddress = $request->ip_address;

        $cartProductIds = Cart::when($userId, function ($q) use ($userId) {
            return $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            return $q->where('ip_address', $ipAddress);
        })
            ->pluck('product_id')
            ->toArray();

        $wishlistProductIds = Wishlist::when($userId, function ($q) use ($userId) {
            return $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            return $q->where('ip_address', $ipAddress);
        })
            ->pluck('product_id')
            ->toArray();

        $popular_products = Product::where('status', 1)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1)->orWhere('role', '1');
            })
            ->inCustomerCountry($userId)
            ->whereJsonContains('product_in', '3')
            ->with([
                'firstVariant:id,product_id,price,image',
                'vendor:id,name,store_name,image,vendor_description',
                'approvedReviews',
                'category:id,name,name_ar,name_ne',
                'subCategory:id,name,name_ar,name_ne',
                'childCategory:id,name,name_ar,name_ne',
                'brand:id,name',
                'vendor.country:id,name'
            ])
            ->limit(15)
            ->get()
            ->map(fn($product) => $this->formatProduct($product, $request, $product_in, $cartProductIds, $wishlistProductIds));



        return response()->json([
            'status' => true,
            'data'   => $popular_products
        ]);
    }

    public function product_search(Request $request)
    {
        $userId = $request->user_id;
        $query = Product::query()
            ->inCustomerCountry($userId)
            ->where('status', 1)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1)->orWhere('role', '1');
            });

        /*
    |--------------------------------------------------------------------------
    | GLOBAL SEARCH
    |--------------------------------------------------------------------------
    */
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                // PRODUCT NAME
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('name_ar', 'LIKE', "%{$search}%")
                    ->orWhere('name_ne', 'LIKE', "%{$search}%");

                // CATEGORY
                $q->orWhereHas('category', function ($cat) use ($search) {
                    $cat->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('name_ar', 'LIKE', "%{$search}%")
                        ->orWhere('name_ne', 'LIKE', "%{$search}%");
                });

                // SUBCATEGORY
                $q->orWhereHas('subCategory', function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('name_ar', 'LIKE', "%{$search}%")
                        ->orWhere('name_ne', 'LIKE', "%{$search}%");
                });

                // BRAND
                $q->orWhereHas('brand', function ($brand) use ($search) {
                    $brand->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('name_ar', 'LIKE', "%{$search}%")
                        ->orWhere('name_ne', 'LIKE', "%{$search}%");
                });

                // CHILDCATEGORY
                $q->orWhereHas('childCategory', function ($child) use ($search) {
                    $child->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('name_ar', 'LIKE', "%{$search}%")
                        ->orWhere('name_ne', 'LIKE', "%{$search}%");
                });
            });
        }

        /*
    |--------------------------------------------------------------------------
    | FETCH PRODUCTS
    |--------------------------------------------------------------------------
    */
        $products = $query->with([
            'firstVariant',
            'vendor:id,name,store_name,image,is_verified',
            'approvedReviews',
            'category',
            'subCategory',
            'childCategory',
            'brand',
        ])->get();

        $search = $request->search ? trim($request->search) : null;
        $lang = $request->lang ?? app()->getLocale();

        $formattedProducts = $products->map(function ($product) use ($search, $request, $lang) {
            // Get First Variant Image
            $image = null;
            $variant = $product->variants->first() ?? $product->firstVariant;
            $image = ImageHelper::getProductImage(($variant->image ?? null) ?: ($product->thumbnail ?? null));


            if ($search) {
                // Check Product Name
                if (
                    stripos($product->name, $search) !== false ||
                    stripos($product->name_ar, $search) !== false ||
                    stripos($product->name_ne, $search) !== false
                ) {
                    $search_product_id = $product->id;
                }

                // Check Category
                if ($product->category) {
                    if (
                        stripos($product->category->name, $search) !== false ||
                        stripos($product->category->name_ar, $search) !== false ||
                        stripos($product->category->name_ne, $search) !== false
                    ) {
                        $search_category_id = $product->category->id;
                    }
                }

                // Check SubCategory
                if ($product->subCategory) {
                    if (
                        stripos($product->subCategory->name, $search) !== false ||
                        stripos($product->subCategory->name_ar, $search) !== false ||
                        stripos($product->subCategory->name_ne, $search) !== false
                    ) {
                        $search_subcategory_id = $product->subCategory->id;
                    }
                }

                // Check ChildCategory
                if ($product->childCategory) {
                    if (
                        stripos($product->childCategory->name, $search) !== false ||
                        stripos($product->childCategory->name_ar, $search) !== false ||
                        stripos($product->childCategory->name_ne, $search) !== false
                    ) {
                        $search_child_id = $product->childCategory->id;
                    }
                }

                // Check Brand
                if ($product->brand) {
                    if (
                        stripos($product->brand->name, $search) !== false ||
                        stripos($product->brand->name_ar, $search) !== false ||
                        stripos($product->brand->name_ne, $search) !== false
                    ) {
                        $search_brand_id = $product->brand->id;
                    }
                }
            }

            return [
                'id'                => $product->id,
                'name'              => $product->{"name_$lang"} ?? $product->name,
                'image'             => $image,
                'category_id'       => $product->category_id,
                'subcategory_id'    => $product->subcategory_id,
                'child_category_id' => $product->child_category_id,
                'brand_id'          => $product->brand_id,
                'campaign'          => $this->campaignInfo($product),
                'is_verify'         => $product->vendor ? ($product->vendor->areRequiredDocumentsVerified() ? 1 : 0) : 0,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $formattedProducts,
        ]);
    }

    public function get_ondeal_product(Request $request)
    {
        $locale = $request->get('lang');
        if (in_array($locale, ['en', 'ar', 'ne', 'zh', 'ja', 'hi', 'de', 'fr', 'ko', 'pt', 'es', 'ru', 'it', 'tr', 'th', 'vi'])) {
            app()->setLocale($locale);
        }
        $product_in = 4;
        $userId = $request->user_id;
        $ipAddress = $request->ip_address;

        $cartProductIds = Cart::when($userId, function ($q) use ($userId) {
            return $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            return $q->where('ip_address', $ipAddress);
        })
            ->pluck('product_id')
            ->toArray();

        $wishlistProductIds = Wishlist::when($userId, function ($q) use ($userId) {
            return $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            return $q->where('ip_address', $ipAddress);
        })
            ->pluck('product_id')
            ->toArray();

        $ondeal_products = Product::where('status', 1)
            ->inCustomerCountry($userId)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1)->orWhere('role', '1');
            })
            ->whereJsonContains('product_in', '4')
            ->with([
                'firstVariant:id,product_id,price,image',
                'vendor:id,name,store_name,image,is_verified',
                'approvedReviews',

                'category:id,name,name_ar,name_ne',
                'subCategory:id,name,name_ar,name_ne',
                'childCategory:id,name,name_ar,name_ne',
                'brand:id,name'
            ])
            ->limit(15)
            ->get()
            ->map(fn($product) => $this->formatProduct($product, $request, $product_in, $cartProductIds, $wishlistProductIds));



        return response()->json([
            'status' => true,
            'data'   => $ondeal_products
        ]);
    }

    public function getProductCoupons(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.validation_error'),
                'errors'  => $validator->errors()
            ], 422);
        }

        $product = Product::find($request->product_id);
        $couponIds = json_decode($product->coupon_id, true) ?? [];

        if (empty($couponIds)) {
            return response()->json([
                'status'  => true,
                'message' => __('messages.no_coupons_found'),
                'data'    => []
            ]);
        }

        $today = Carbon::now();
        $coupons = Coupon::whereIn('id', $couponIds)
            ->where('status', 1)
            ->where('valid_from', '<=', $today)
            ->where('valid_until', '>=', $today)
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $coupons
        ]);
    }

    public function footer_menu(Request $request)
    {
        $lang = $request->get('lang', app()->getLocale());
        $categories_menu = Category::where('is_active', 1)
            ->with([
                'subCategories' => function ($q) {
                    $q->where('is_active', 1);
                }
            ])
            ->get();

        foreach ($categories_menu as $category) {
            $category->name = $category->{"name_$lang"} ?? $category->name;
            $category->slug = $category->{"slug_$lang"} ?? $category->slug;

            foreach ($category->subCategories as $sub) {
                $sub->name = $sub->{"name_$lang"} ?? $sub->name;
                $sub->slug = $sub->{"slug_$lang"} ?? $sub->slug;
            }
        }

        $static_pages = [
            [
                'name' => __('messages.about_us'),
                'slug' => 'about-us',
                'api_link' => url('api/get-about-us')
            ],
            [
                'name' => __('messages.blogs'),
                'slug' => 'blog',
                'api_link' => url('api/blog')
            ],
            [
                'name' => __('messages.faq'),
                'slug' => 'faq',
                'api_link' => url('api/get-faqs')
            ],
            [
                'name' => __('messages.terms_and_conditions'),
                'slug' => 'terms-and-condition',
                'api_link' => url('api/get-terms-and-condition')
            ],
            [
                'name' => __('messages.privacy_policy'),
                'slug' => 'privacy-policy',
                'api_link' => url('api/get-privacy-policy')
            ]
        ];

        return response()->json([
            'status' => true,
            'data'   => [
                'categories' => $categories_menu,
                'static_pages' => $static_pages
            ]
        ]);
    }


    public function add_rating_and_review(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'product_id' => 'required|integer',
            'rating' => 'required|integer|between:1,5',
            'review' => 'required|string',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
        $item = OrderItem::select('*')
        ->where('product_id', $request->product_id)
            ->whereHas('order', function($q) use ($request) {
                $q->where('user_id', $request->user_id);
            })
            ->first();
        if (!$item) {
            return response()->json([
                'status' => false,
                'errors' => ['product_id' => ['Product not found in your orders.']]
            ]);
        }

        $review = new ProductReview();
        $review->product_id = $item->product_id;
        $review->order_item_id = $item->id;
        $review->variant_id = $item->variant_id;
        $review->user_id = $request->user_id;
        $review->rating = $request->rating;
        $review->review = $request->review;
        $review->save();


        return response()->json([
            'status' => true,
            'message' => __('messages.product_review_added_successfully')

        ]);
    }

    public function like_dislike_review(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'review_id' => 'required|exists:product_reviews,id',
            'user_id' => 'required|exists:users,id',
            'reaction_type' => 'required|in:1,0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.validation_error'),
                'errors'  => $validator->errors()
            ], 422);
        }

        $reaction = ReviewReaction::where('review_id', $request->review_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($reaction) {
            if ($reaction->reaction_type === $request->reaction_type) {
                // If the same reaction is clicked again, remove it (toggle off)
                $reaction->delete();
                $message = __('messages.reaction_removed');
                $action = 'removed';
            } else {
                // If a different reaction is clicked, update it
                $reaction->reaction_type = $request->reaction_type;
                $reaction->save();
                $message = __('messages.reaction_updated');
                $action = 'updated';
            }
        } else {
            // Create a new reaction
            ReviewReaction::create([
                'review_id' => $request->review_id,
                'user_id' => $request->user_id,
                'reaction_type' => $request->reaction_type,
            ]);
            $message = __('messages.reaction_added');
            $action = 'added';
        }

        // Get updated counts
        $likes = ReviewReaction::where('review_id', $request->review_id)->where('reaction_type', '1')->count();
        $dislikes = ReviewReaction::where('review_id', $request->review_id)->where('reaction_type', '0')->count();

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => [
                'action' => $action,
                'likes' => $likes,
                'dislikes' => $dislikes,
            ]
        ]);
    }

    public function vendor_earning_estimate(Request $request)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:off,%',
            'discount_value' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0'
        ]);

        $price = (float) $request->price;
        $discountType = $request->discount_type;
        $discountValue = (float) ($request->discount_value ?? 0);
        $shipping = (float) ($request->shipping ?? 0);
        $tax = (float) ($request->tax ?? 0);

        $finalPrice = $price;
        if ($discountType === '%') {
            $finalPrice = $price - (($price * $discountValue) / 100);
        } elseif ($discountType === 'off') {
            $finalPrice = $price - $discountValue;
        }
        $finalPrice = max(0, $finalPrice);

        $commissionRate = (float) (GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0);
        $pgFeePercent = (float) (GeneralSetting::where('key', 'pg_fee_percent')->value('value') ?? 0);

        $commission = ($finalPrice * $commissionRate) / 100;
        $pgFee = ($finalPrice * $pgFeePercent) / 100;

        $net = max(0, $finalPrice - $commission - $pgFee - $shipping - $tax);

        return response()->json([
            'status' => true,
            'data' => [
                'selling_price' => round($finalPrice, 2),
                'commission_rate' => $commissionRate,
                'pg_fee_percent' => $pgFeePercent,
                'commission' => round($commission, 2),
                'payment_gateway_fee' => round($pgFee, 2),
                'shipping' => round($shipping, 2),
                'tax' => round($tax, 2),
                'net_payout' => round($net, 2)
            ]
        ]);
    }

    /**
     * Get filter arrays for product list sidebar
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return array
     */
    private function getProductFilters(Request $request, $query)
    {
        $products = $query->with(['category', 'brand', 'approvedReviews'])->get();
        $lang = $request->get('lang', app()->getLocale());

        return [
            'categories'   => $this->getCategoryFilters($products),
            'brands'       => $this->getBrandFilters($products),
            'price_range'  => $this->getPriceRangeFilter($products, $lang),
            'discounts'    => $this->getDiscountFilters($products),
            'ratings'      => $this->getRatingFilters($products),
            'offers'       => $this->getOfferFilters($products),
            'availability' => $this->getAvailabilityFilters($products),
        ];
    }

    /**
     * Get category filter array
     */
    private function getCategoryFilters($products)
    {
        $categoryIds = $products->pluck('category_id')->unique()->filter();
        $categories = Category::whereIn('id', $categoryIds)
            ->select('id', 'name', 'name_ar', 'name_ne')
            ->get();

        return $categories->map(function ($cat) use ($products) {
            return [
                'id' => $cat->id,
                'label' => $cat->name,
                'label_ar' => $cat->name_ar,
                'label_ne' => $cat->name_ne,
                'count' => $products->where('category_id', $cat->id)->count(),
            ];
        })->values()->toArray();
    }

    /**
     * Get brand filter array
     */
    private function getBrandFilters($products)
    {
        $brandIds = $products->pluck('brand_id')->unique()->filter();
        $brands = Brand::whereIn('id', $brandIds)
            ->select('id', 'name')
            ->get();

        return $brands->map(function ($brand) use ($products) {
            return [
                'id' => $brand->id,
                'label' => $brand->name,
                'count' => $products->where('brand_id', $brand->id)->count(),
            ];
        })->sortBy('label')->values()->toArray();
    }

    /** 
     * Get price range filter
     */
    private function getPriceRangeFilter($products, $lang)
    {
        $variantPrices = DB::table('product_variants')
            ->whereIn('product_id', $products->pluck('id'))
            ->select(DB::raw('MIN(final_price) as min_price, MAX(final_price) as max_price'))
            ->first();

        return [
            'type' => 'slider',
            'min' => (int)($variantPrices->min_price ?? 0),
            'max' => (int)($variantPrices->max_price ?? 100000),
            'step' => 100,
            'currency' => \App\Helpers\GeneralHelper::get_currency_by_lang($lang),
            'currency_symbol' => \App\Helpers\GeneralHelper::get_currency_by_lang($lang),
        ];
    }

    /**
     * Get discount filter array
     */
    private function getDiscountFilters($products)
    {
        $variants = DB::table('product_variants')
            ->whereIn('product_id', $products->pluck('id'))
            ->where('discount_type', '!=', null)
            ->select('discount_type', 'discount_value')
            ->distinct()
            ->get();

        $discountRanges = [
            ['id' => 'discount-50', 'label' => '50% or more', 'min' => 50],
            ['id' => 'discount-30', 'label' => '30% - 49%', 'min' => 30, 'max' => 49],
            ['id' => 'discount-20', 'label' => '20% - 29%', 'min' => 20, 'max' => 29],
            ['id' => 'discount-10', 'label' => '10% - 19%', 'min' => 10, 'max' => 19],
        ];

        return array_map(function ($range) use ($variants) {
            $count = 0;
            foreach ($variants as $v) {
                $discount = $v->discount_type === 'percentage' ? (int)$v->discount_value : 0;
                if (isset($range['max']) && $discount >= $range['min'] && $discount <= $range['max']) {
                    $count++;
                } elseif (!isset($range['max']) && $discount >= $range['min']) {
                    $count++;
                }
            }
            return [
                'id' => $range['id'],
                'label' => $range['label'],
                'count' => $count > 0 ? $count : 0
            ];
        }, $discountRanges);
    }

    /**
     * Get rating filter array
     */
    private function getRatingFilters($products)
    {
        return [
            [
                'id' => 'rating-4',
                'label' => '4★ & above',
                'stars' => 4,
                'count' => $products->filter(function ($p) {
                    return ($p->approvedReviews->avg('rating') ?? 0) >= 4;
                })->count()
            ],
            [
                'id' => 'rating-3',
                'label' => '3★ & above',
                'stars' => 3,
                'count' => $products->filter(function ($p) {
                    return ($p->approvedReviews->avg('rating') ?? 0) >= 3;
                })->count()
            ],
            [
                'id' => 'rating-assured',
                'label' => 'Assured',
                'icon' => 'shield-check',
                'count' => $products->where('is_assured', 1)->count()
            ]
        ];
    }

    /**
     * Get offer filter array
     */
    private function getOfferFilters($products)
    {
        return [
            [
                'id' => 'offer-special',
                'label' => 'Special Price',
                'icon' => 'tag',
                'count' => $products->filter(function ($p) {
                    return !empty($p->offer_id) && $p->offer_id !== '[]' && $p->offer_id !== null;
                })->count()
            ]
        ];
    }

    /**
     * Get availability filter array
     */
    private function getAvailabilityFilters($products)
    {
        $productIds = $products->pluck('id')->toArray();
        $inStockCount = DB::table('product_variants')
            ->whereIn('product_id', $productIds)
            ->where('stock', '>', 0)
            ->distinct('product_id')
            ->count('product_id');

        $outOfStockCount = count($productIds) - $inStockCount;

        return [
            [
                'id' => 'in-stock',
                'label' => 'In Stock',
                'count' => $inStockCount,
                'available' => true
            ],
            [
                'id' => 'out-stock',
                'label' => 'Out of Stock',
                'count' => $outOfStockCount,
                'available' => false
            ]
        ];
    }

    public function getProductCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.validation_error'),
                'errors'  => $validator->errors()
            ], 422);
        }

        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $today = now();

        $coupons = Coupon::where('status', 1)

            ->where(function ($query) use ($today) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $today);
            })

            ->where(function ($query) use ($today) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $today);
            })

            ->where(function ($query) use ($product) {
                // Global (no IDs restricted)
                $query->where(function ($q) {
                    $q->where(function ($sq) {
                        $sq->whereNull('product_ids')->orWhere('product_ids', '[]')->orWhere('product_ids', 'null');
                    })
                        ->where(function ($sq) {
                            $sq->whereNull('category_ids')->orWhere('category_ids', '[]')->orWhere('category_ids', 'null');
                        })
                        ->where(function ($sq) {
                            $sq->whereNull('vendor_ids')->orWhere('vendor_ids', '[]')->orWhere('vendor_ids', 'null');
                        });
                })
                    // OR restricted by JSON columns (supports both string and integer IDs in JSON)
                    ->orWhereJsonContains('product_ids', (string)$product->id)
                    ->orWhereJsonContains('product_ids', (int)$product->id)
                    ->orWhereJsonContains('category_ids', (string)$product->category_id)
                    ->orWhereJsonContains('category_ids', (int)$product->category_id)
                    ->orWhereJsonContains('vendor_ids', (string)$product->vendor_id)
                    ->orWhereJsonContains('vendor_ids', (int)$product->vendor_id);
            })

            ->get();

        return response()->json([
            'status' => true,
            'data' => $coupons
        ]);
    }

    public function getCoupons(Request $request)
    {
        $today = now();
        $coupons = \App\Models\Coupon::with('categories')
            ->where('status', 1)
            ->where(function ($query) use ($today) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', $today);
            })
            ->get();

        return response()->json([
            'status' => true,
            'data' => $coupons
        ]);
    }
}
