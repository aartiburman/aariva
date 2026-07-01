<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ImageHelper;
use App\Models\Product;
use Illuminate\Support\Facades\Lang;
use Kreait\Firebase\Factory;

use Illuminate\Support\Facades\Log;
use App\Helpers\PriceHelper;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\Campaign;
use Carbon\Carbon;

class HomeScreenController extends Controller
{
    public function home(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'    => 'nullable|exists:users,id',
            'ip_address' => 'required_without:user_id|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.validation_error'),
                'errors'  => $validator->errors()
            ], 422);
        }

        /* =========================
     * LANGUAGE HANDLING
     * ========================= */
        $lang = $request->get('lang', 'en'); // en | ar | hi
        $userId = $request->user_id;
        $ipAddress = $request->ip_address;

        $cartProductIds = Cart::when($userId, function($q) use ($userId) {
                return $q->where('user_id', $userId);
            }, function($q) use ($ipAddress) {
                return $q->where('ip_address', $ipAddress);
            })
            ->pluck('product_id')
            ->toArray();

        $wishlistProductIds = Wishlist::when($userId, function($q) use ($userId) {
                return $q->where('user_id', $userId);
            }, function($q) use ($ipAddress) {
                return $q->where('ip_address', $ipAddress);
            })
            ->pluck('product_id')
            ->toArray();

        $field = function ($base) use ($lang) {
            return $lang === 'ar' ? "{$base}_ar"
                : ($lang === 'ne' ? "{$base}_ne" : $base);
        };

        /* =========================
     * MENU CATEGORIES
     * ========================= */
        $categories_menu = Category::where('is_active', 1)
            ->select([
                'id',
                $field('name') . ' as name',
                $field('slug') . ' as slug',
                'image',

            ])
            ->with([
                'subCategories' => function ($q) use ($field) {
                    $q->where('is_active', 1)
                        ->select([
                            'id',
                            'category_id',
                            $field('name') . ' as name',
                            $field('slug') . ' as slug',
                            'image'
                        ])
                        ->with([
                            'childCategories' => function ($q2) use ($field) {
                                $q2->where('is_active', 1)
                                    ->select([
                                        'id',
                                        'subcategory_id',
                                        $field('name') . ' as name',
                                        $field('slug') . ' as slug',

                                    ]);
                            }
                        ]);
                }
            ])
            ->get();

        /* =========================
     * HERO BANNERS
     * ========================= */
        $hero_banner = Banner::where('status', 1)
            ->where('position', 'top')
            ->get();

        foreach ($hero_banner as $banner) {
            // Process images - Always return as an array
            $image = $banner->image;
            $decoded = json_decode($image, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $banner->image = array_map(fn($img) => ImageHelper::getBannerImage($img), $decoded);
            } else {
                $banner->image = $image ? [ImageHelper::getBannerImage($image)] : [];
            }

            // Process multilingual fields
            $banner->title = $banner[$field('title')] ?? '';
            $banner->description = $banner[$field('description')] ?? '';
        }


        /* =========================
     * DEAL BANNERS
     * ========================= */
        $deal_banner = Banner::where('status', 1)
            ->where('position', 'deal')
            ->get();

        foreach ($deal_banner as $banner) {
            $image = $banner->image;
            $decoded = json_decode($image, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $banner->image = array_map(fn($img) => ImageHelper::getBannerImage($img), $decoded);
            } else {
                $banner->image = $image ? [ImageHelper::getBannerImage($image)] : [];
            }

            $banner->title = $banner[$field('title')] ?? '';
            $banner->description = $banner[$field('description')] ?? '';
        }

        /* =========================
     * CATEGORY LIST
     * ========================= */
        $categories = Category::where('is_active', 1)
            ->select([
                'id',
                $field('name') . ' as name',
                $field('slug') . ' as slug',
                'image'
            ])
            ->get();

        foreach ($categories as $category) {
            $category->image = ImageHelper::getCategoryImage($category->image);
        }

        /* =========================
     * DEAL PRODUCTS
     * ========================= */
        $deal_products = Product::where('status', 1)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1);
            })
            ->inCustomerCountry($userId)
            ->whereJsonContains('product_in', '2')
            ->with(['firstVariant:id,product_id,price,image,discount_type,discount_value', 'approvedReviews'])
            ->limit(15)
            ->get()
            ->map(function ($product) use ($lang, $cartProductIds, $wishlistProductIds) {
                // Determine language fields
                $name = $lang === 'ar' ? $product->name_ar
                    : ($lang === 'ne' ? $product->name_ne : $product->name);

                $short_description = $lang === 'ar' ? $product->short_description_ar
                    : ($lang === 'ne' ? $product->short_description_ne : $product->short_description);

                $description = $lang === 'ar' ? $product->description_ar
                    : ($lang === 'ne' ? $product->description_ne : $product->description);

                // Format product using your helper
                return $this->formatProduct($product, $lang, $cartProductIds, $wishlistProductIds);
            });

        /* =========================
     * NORMAL PRODUCTS
     * ========================= */
        $products = Product::where('status', 1)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1);
            })
            ->inCustomerCountry($userId)
            ->with(['firstVariant:id,product_id,price,image,discount_type,discount_value', 'approvedReviews'])
            ->limit(15)
            ->get()
            ->map(function ($product) use ($lang, $cartProductIds, $wishlistProductIds) {
                // Select fields based on requested language
                $name = $lang === 'ar' ? $product->name_ar
                    : ($lang === 'ne' ? $product->name_ne : $product->name);

                $short_description = $lang === 'ar' ? $product->short_description_ar
                    : ($lang === 'ne' ? $product->short_description_ne : $product->short_description);

                $description = $lang === 'ar' ? $product->description_ar
                    : ($lang === 'ne' ? $product->description_ne : $product->description);

                // Format product using your helper
                return $this->formatProduct($product, $lang, $cartProductIds, $wishlistProductIds);
            });

        /* =========================
     * LOWEST PRICE PRODUCTS
     * ========================= */
        $lowestpriceproducts = Product::where('status', 1)
            ->whereHas('vendor', function($q) { $q->where('status', 1); })
            ->inCustomerCountry($userId)
            ->with(['lowestPriceVariant:id,product_id,price,image,discount_type,discount_value', 'approvedReviews'])
            ->limit(15)
            ->get()
            ->map(function ($product) use ($lang, $cartProductIds, $wishlistProductIds) {
                // Select fields based on requested language
                $name = $lang === 'ar' ? $product->name_ar
                    : ($lang === 'ne' ? $product->name_ne : $product->name);

                $short_description = $lang === 'ar' ? $product->short_description_ar
                    : ($lang === 'ne' ? $product->short_description_ne : $product->short_description);

                $description = $lang === 'ar' ? $product->description_ar
                    : ($lang === 'ne' ? $product->description_ne : $product->description);

                // Format product using your helper
                return $this->formatLowestPriceProduct($product, $lang, $cartProductIds, $wishlistProductIds);
            });

        /* =========================
     * TRENDING PRODUCTS
     * ========================= */
        $tranding_products = Product::where('status', 1)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1);
            })
            ->inCustomerCountry($userId)
            ->whereJsonContains('product_in', '2')
            ->with(['firstVariant:id,product_id,price,image,discount_type,discount_value', 'approvedReviews'])
            ->limit(15)
            ->get()
            ->map(function ($product) use ($lang, $cartProductIds, $wishlistProductIds) {
                // Select fields based on requested language
                $name = $lang === 'ar' ? $product->name_ar
                    : ($lang === 'ne' ? $product->name_ne : $product->name);

                $short_description = $lang === 'ar' ? $product->short_description_ar
                    : ($lang === 'ne' ? $product->short_description_ne : $product->short_description);

                $description = $lang === 'ar' ? $product->description_ar
                    : ($lang === 'ne' ? $product->description_ne : $product->description);

                // Format product using your helper
                return $this->formatProduct($product, $lang, $cartProductIds, $wishlistProductIds);
            });

        /* =========================
     * BOTTOM BANNER
     * ========================= */
        $bottom_banner = Banner::where('status', 1)
            ->where('position', 'middle')
            ->get();

        foreach ($bottom_banner as $banner) {
            $image = $banner->image;
            $decoded = json_decode($image, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $banner->image = array_map(fn($img) => ImageHelper::getBannerImage($img), $decoded);
            } else {
                $banner->image = $image ? [ImageHelper::getBannerImage($image)] : [];
            }

            $banner->title = $banner[$field('title')] ?? '';
            $banner->description = $banner[$field('description')] ?? '';
        }

        /* =========================
     * CATEGORY PRODUCT SECTION
     * ========================= */
        $category_product = Category::where('is_active', 1)
            ->select([
                'id',
                $field('name') . ' as name',
                $field('slug') . ' as slug'
            ])
            ->with([
                'products' => function ($q) use ($userId) {
                    $q->where('status', 1)
                        ->whereHas('vendor', function($q) { $q->where('status', 1); })
                        ->inCustomerCountry($userId)
                        ->with(['firstVariant:id,product_id,price,image,discount_type,discount_value', 'approvedReviews'])
                        ->limit(10);
                },
                'subCategories' => function ($q) use ($field) {
                    $q->where('is_active', 1)
                        ->select([
                            'id',
                            'category_id',
                            $field('name') . ' as name',
                            $field('slug') . ' as slug',
                            'image'
                        ])
                        ->with([
                            'childCategories' => function ($q2) use ($field) {
                                $q2->where('is_active', 1)
                                    ->select([
                                        'id',
                                        'subcategory_id',
                                        $field('name') . ' as name',
                                        $field('slug') . ' as slug',
                                    ]);
                            }
                        ]);
                }
            ])
            ->get();

        foreach ($category_product as $category) {
            $category->products = $category->products->map(function ($product) use ($lang, $cartProductIds, $wishlistProductIds) {
                return $this->formatProduct($product, $lang, $cartProductIds, $wishlistProductIds);
            });
            foreach ($category->subCategories as $sub) {
                $sub->image = $sub->image
                    ? ImageHelper::getSubCategoryImage($sub->image)
                    : null;
            }
        }

        /* =========================
     * FINAL RESPONSE
     * ========================= */




        return response()->json([
            'status'              => true,
            'message'             => 'home_api',
            'menu'                => $categories_menu,
            'hero_banner'         => $hero_banner,
            'deal_banner'         => $deal_banner,
            'deal_products'       => $deal_products,
            'categories'          => $categories,
            'products'            => $products,
            'lowestpriceproducts' => $lowestpriceproducts,
            'tranding_products'   => $tranding_products,
            'bottom_banner'       => $bottom_banner,
            'category_product'    => $category_product,
        ]);
    }


  



   private function formatProduct($product, $lang, $cartProductIds = [], $wishlistProductIds = [])
{
    $image = null;
    $price = null;
    $original_price = null;
    $discount = null;

    // Handle first variant
    if ($product->firstVariant) {
        if ($product->firstVariant->image) {
            $image = ImageHelper::getProductImage($product->firstVariant->image);
        }
        
        $original_price = $product->firstVariant->price;
        $price = PriceHelper::applyDiscount(
            $product->firstVariant->price,
            $product->firstVariant->discount_type,
            $product->firstVariant->discount_value
        );

        if ($product->firstVariant->discount_value > 0) {
            $discount = $product->firstVariant->discount_type === 'percent'
                ? $product->firstVariant->discount_value . ' % Off'
                : $product->firstVariant->discount_value . ' off';
        }
    }

    $currency = \App\Helpers\GeneralHelper::get_currency_by_lang($lang);

    // Language-specific fields
    $name = $lang === 'ar' ? $product->name_ar
          : ($lang === 'ne' ? $product->name_ne : $product->name);

    $slug = $lang === 'ar' ? $product->slug_ar
          : ($lang === 'ne' ? $product->slug_ne : $product->slug);

    $short_description = $lang === 'ar' ? $product->short_description_ar
                       : ($lang === 'ne' ? $product->short_description_ne : $product->short_description);

    $description = $lang === 'ar' ? $product->description_ar
                     : ($lang === 'ne' ? $product->description_ne : $product->description);

    // Calculate dynamic ratings
    $reviewCount = $product->approvedReviews->count();
    $avgRating = $reviewCount > 0 ? round($product->approvedReviews->avg('rating'), 1) : 0;
    $ratingStars = (int)ceil($avgRating);

    return [
        'id'                => $product->id,
        'name'              => $name,
        'slug'              => $slug,
        'short_description' => $short_description,
        'description'       => $description,
        'image'             => $image,
        'price'             => $price,
        'original_price'    => $original_price,
        'actual_price'      => $price,
        'discount'          => $discount,
        'currency'          => $currency,
        'product_variant_label_default' => $product->firstVariant ? \App\Helpers\GeneralHelper::getVariantLabel($product->firstVariant->product_variant, $lang) : null,
        'campaign'          => $this->campaignInfo($product),
        'rating'            => $avgRating,
        'rating_stars'      => $ratingStars,
        'review_count'      => $reviewCount,
        'is_in_cart'        => in_array($product->id, $cartProductIds),
        'is_in_wishlist'    => in_array($product->id, $wishlistProductIds),
    ];
}

private function formatLowestPriceProduct($product, $lang, $cartProductIds = [], $wishlistProductIds = [])
{
    $image = null;
    $price = null;
    $original_price = null;
    $discount = null;

    // Handle lowest price variant
    if ($product->lowestPriceVariant) {
        if ($product->lowestPriceVariant->image) {
            $image = ImageHelper::getProductImage($product->lowestPriceVariant->image);
        }
        
        $original_price = $product->lowestPriceVariant->price;
        $price = PriceHelper::applyDiscount(
            $product->lowestPriceVariant->price,
            $product->lowestPriceVariant->discount_type,
            $product->lowestPriceVariant->discount_value
        );

        if ($product->lowestPriceVariant->discount_value > 0) {
            $discount = $product->lowestPriceVariant->discount_type === 'percent'
                ? $product->lowestPriceVariant->discount_value . ' % Off'
                : $product->lowestPriceVariant->discount_value . ' off';
        }
    }

    $currency = \App\Helpers\GeneralHelper::get_currency_by_lang($lang);

    // Language-specific fields
    $name = $lang === 'ar' ? $product->name_ar
          : ($lang === 'ne' ? $product->name_ne : $product->name);

    $slug = $lang === 'ar' ? $product->slug_ar
          : ($lang === 'ne' ? $product->slug_ne : $product->slug);

    $short_description = $lang === 'ar' ? $product->short_description_ar
                       : ($lang === 'ne' ? $product->short_description_ne : $product->short_description);

    $description = $lang === 'ar' ? $product->description_ar
                     : ($lang === 'ne' ? $product->description_ne : $product->description);

    // Calculate dynamic ratings
    $reviewCount = $product->approvedReviews->count();
    $avgRating = $reviewCount > 0 ? round($product->approvedReviews->avg('rating'), 1) : 0;
    $ratingStars = (int)ceil($avgRating);

    return [
        'id'                => $product->id,
        'name'              => $name,
        'slug'              => $slug,
        'short_description' => $short_description,
        'description'       => $description,
        'image'             => $image,
        'price'             => $price,
        'original_price'    => $original_price,
        'actual_price'      => $price,
        'discount'          => $discount,
        'currency'          => $currency,
        'product_variant_label' => $product->lowestPriceVariant ? \App\Helpers\GeneralHelper::getVariantLabel($product->lowestPriceVariant->product_variant, $lang) : null,
        'campaign'          => $this->campaignInfo($product),
        'rating'            => $avgRating,
        'rating_stars'      => $ratingStars,
        'review_count'      => $reviewCount,
        'is_in_cart'        => in_array($product->id, $cartProductIds),
        'is_in_wishlist'    => in_array($product->id, $wishlistProductIds),
    ];
}

private function campaignInfo($product)
{
    try {
        $today = Carbon::now();
        
        // Remove direct coupon access if relationship is not defined
        $hasActiveCoupon = false;
        
        $activeCampaign = Campaign::where('status', 1)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where(function ($q) use ($product) {
                $q->whereHas('products', function ($qp) use ($product) {
                    $qp->where('products.id', $product->id);
                })->orWhereHas('vendors', function ($qv) use ($product) {
                    $qv->where('users.id', $product->vendor_id)
                       ->where('campaign_vendors.active', true);
                });
            })
            ->first();

        if ($activeCampaign) {
            $percent = (float) $activeCampaign->discount_percent;
            return [
                'id' => $activeCampaign->id,
                'name' => $activeCampaign->name,
                'label' => $percent > 0 ? ($percent . ' % Off') : null,
                'percent' => $percent,
                'is_active' => (int) ($activeCampaign->is_active ? 1 : 0),
            ];
        }
    } catch (\Throwable $e) {
        Log::error("Campaign error for product {$product->id}: " . $e->getMessage());
    }
    return null;
}

}
