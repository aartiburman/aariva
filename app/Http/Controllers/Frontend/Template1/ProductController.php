<?php

namespace App\Http\Controllers\Frontend\Template1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use App\Models\ProductVariant;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\ProductReview;
use App\Models\Offer;
use App\Models\Banner;
use App\Models\ProductSize;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Helpers\PriceCalculationHelper;
use App\Helpers\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->select('products.*')
            ->where('products.status', 1)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1);
            })
            ->with(['firstVariant:id,product_id,price,image,discount_type,discount_value', 'approvedReviews']);

        if ($request->filled('category')) {
            $cat = Category::where('slug', $request->category)->first();
            if ($cat) {
                $query->where('products.category_id', $cat->id);
            }
        } elseif ($request->filled('category_id')) {
            $query->where('products.category_id', $request->category_id);
        }

        if ($request->filled('brand')) {
            $brand = Brand::where('slug', $request->brand)->first();
            if ($brand) {
                $query->where('products.brand_id', $brand->id);
            }
        }

        if ($request->filled('subcategory')) {
            $sub = SubCategory::where('slug', $request->subcategory)->first();
            if ($sub) {
                $query->where('products.subcategory_id', $sub->id);
            }
        } elseif ($request->filled('subcategory_id')) {
            $query->where('products.subcategory_id', $request->subcategory_id);
        }

        if ($request->filled('child_category')) {
            $child = ChildCategory::where('slug', $request->child_category)->first();
            if ($child) {
                $query->where('products.child_category_id', $child->id);
            }
        } elseif ($request->filled('child_category_id')) {
            $query->where('products.child_category_id', $request->child_category_id);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $minPrice = $request->min_price ?? 0;
            $maxPrice = $request->max_price ?? 9999999;
            $query->whereHas('firstVariant', function ($q) use ($minPrice, $maxPrice) {
                $q->whereRaw('(
                    CASE 
                        WHEN discount_type IN ("percent", "%", "percentage") 
                        THEN price - (price * discount_value / 100) 
                        WHEN discount_type IN ("fixed", "flat", "amount") 
                        THEN price - discount_value 
                        ELSE price 
                    END
                ) BETWEEN ? AND ?', [$minPrice, $maxPrice]);
            });
        }

        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('products.name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('products.name_ar', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('products.name_ne', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
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
                default:
                    $query->latest('products.id');
            }
        } else {
            $query->latest('products.id');
        }

        $products = $query->paginate(12);

        $products->getCollection()->transform(function ($product) {
            $variant = $product->firstVariant;
            $product->image = $variant ? ImageHelper::getProductImage($variant->image) : asset('frontend/assets/images/products/01.png');
            $product->original_price = $variant ? $variant->price : 0;
            $product->final_price = $variant ? PriceHelper::applyDiscount($variant->price, $variant->discount_type, $variant->discount_value) : 0;
            $product->avg_rating = $product->approvedReviews->avg('rating') ?? 0;
            $product->discount_percent = $product->original_price > 0 ? round((1 - $product->final_price / $product->original_price) * 100) : 0;
            $product->formatted_price = PriceHelper::formatPrice($product->final_price);
            $product->formatted_original_price = $product->final_price < $product->original_price ? PriceHelper::formatPrice($product->original_price) : null;
            return $product;
        });

        $userId = Auth::check() ? Auth::id() : null;
        $ipAddress = $request->ip();

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

        $categories = Category::where('is_active', 1)
            ->withCount(['products' => function($q) {
                $q->where('status', 1);
            }])
            ->select(['id', 'name', 'slug', 'meta_title', 'meta_description'])
            ->get();

        $brands = Brand::where('status', 1)
            ->withCount(['products' => function($q) {
                $q->where('status', 1);
            }])
            ->select(['id', 'name', 'slug', 'meta_title', 'meta_description'])
            ->get();

        $currentCategory = null;
        if ($request->filled('category')) {
            $currentCategory = Category::where('slug', $request->category)
                ->select(['id', 'name', 'slug', 'meta_title', 'meta_description', 'description'])->first();
        }
        $currentSubCategory = null;
        if ($request->filled('subcategory')) {
            $currentSubCategory = SubCategory::where('slug', $request->subcategory)
                ->select(['id', 'name', 'slug', 'meta_title', 'meta_description', 'description'])->first();
        }
        $currentChildCategory = null;
        if ($request->filled('child_category')) {
            $currentChildCategory = ChildCategory::where('slug', $request->child_category)
                ->select(['id', 'name', 'slug', 'meta_title', 'meta_description', 'description'])->first();
        }
        $currentBrand = null;
        if ($request->filled('brand')) {
            $currentBrand = Brand::where('slug', $request->brand)
                ->select(['id', 'name', 'slug', 'meta_title', 'meta_description', 'description'])->first();
        }

        return view('frontend.products.index', compact('products', 'categories', 'brands', 'cartProductIds', 'wishlistProductIds', 'currentCategory', 'currentSubCategory', 'currentChildCategory', 'currentBrand'));
    }

    public function show($slug)
    {
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
                $q->where('status', 1);
            })
            ->where(function($q) use ($slug) {
                $q->where('slug', $slug)->orWhere('id', $slug);
            })
            ->firstOrFail();

        $vendorRating = ProductReview::whereHas('product', function ($q) use ($product) {
            $q->where('vendor_id', $product->vendor_id);
        })->where('status', 1)->avg('rating');

        $product->vendor_data = [
            'vendor_id'    => $product->vendor_id,
            'store_name'   => $product->vendor->store_name ?? $product->vendor->name,
            'vendor_image' => ImageHelper::getVendorsImage($product->vendor->image ?? null),
            'rating'       => round($vendorRating ?? 0, 1),
            'is_verified'  => $product->vendor->is_verified ?? 0,
        ];

        $allColors = [];
        $allSizes = [];

        $allSizeIds = [];
        foreach ($product->variants as $variant) {
            $sizes = json_decode($variant->size, true);
            if (is_array($sizes)) {
                array_push($allSizeIds, ...$sizes);
            }
        }
        $allSizeNames = ProductSize::whereIn('id', array_unique($allSizeIds))->pluck('name', 'id');

        foreach ($product->variants as $variant) {
            $variant->original_price = $variant->price;
            $calc = PriceCalculationHelper::calculateItemPrice($product, $variant->id);

            $variant->discount = null;
            if ($calc['product_unit_discount'] > 0) {
                $type = strtolower($variant->discount_type);
                if (in_array($type, ['percent', 'percentage', '%'])) {
                    $variant->discount = $variant->discount_value . ' % Off';
                } else {
                    $variant->discount = $variant->discount_value . ' off';
                }
            }

            $variant->actual_price = $calc['price_after_discounts'];
            $variant->offer_discount = $calc['offer_unit_discount'];
            $variant->campaign_discount = $calc['campaign_unit_discount'];
            $variant->product_variant_label = GeneralHelper::getVariantLabel($variant->product_variant, app()->getLocale());

            if ($variant->color) {
                $allColors[] = $variant->color;
            }

            $sizes = json_decode($variant->size, true);
            if (is_array($sizes)) {
                $sizeValues = array_values(array_filter(array_map(fn($id) => $allSizeNames[$id] ?? null, $sizes)));
                $variant->size = $sizeValues;
                array_push($allSizes, ...$sizeValues);
            }

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
        $product->sizes = array_values(array_unique($allSizes));

        if ($product->variants->isNotEmpty()) {
            $v = $product->variants->first();
            $product->price = $v->actual_price;
            $product->original_price = $v->original_price;
            $product->actual_price = $v->actual_price;
            $product->discount = $v->discount;
        }

        $offer_ids = $product->offer_id ? json_decode($product->offer_id, true) : [];
        if (!is_array($offer_ids)) {
            $offer_ids = [$offer_ids];
        }
        $product->offers = Offer::whereIn('id', $offer_ids)->get()->map(fn($o) => [
            'id'   => $o->id,
            'name' => $o->code ?? $o->name,
        ]);

        $userId = Auth::check() ? Auth::id() : null;
        $ipAddress = request()->ip();

        $product->is_in_cart = Cart::when($userId,
            fn($q) => $q->where('user_id', $userId),
            fn($q) => $q->where('ip_address', $ipAddress)
        )->where('product_id', $product->id)->exists();

        $product->is_in_wishlist = Wishlist::when($userId,
            fn($q) => $q->where('user_id', $userId),
            fn($q) => $q->where('ip_address', $ipAddress)
        )->where('product_id', $product->id)->exists();

        $relatedProducts = Product::where('status', 1)
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                $q->where('category_id', $product->category_id)
                    ->orWhere('subcategory_id', $product->subcategory_id);
            })
            ->with(['firstVariant:id,product_id,price,image,discount_type,discount_value', 'approvedReviews'])
            ->limit(10)
            ->get();

        return view('frontend.products.show', compact('product', 'relatedProducts'));
    }

    public function search(Request $request)
    {
        $searchTerm = trim($request->get('q', ''));

        $results = [];

        if (strlen($searchTerm) < 2) {
            return response()->json(['status' => true, 'data' => []]);
        }

        // Search products
        $products = Product::where('status', 1)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1);
            })
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%");
            })
            ->with(['firstVariant:id,product_id,price,image,discount_type,discount_value'])
            ->limit(5)
            ->get();

        foreach ($products as $product) {
            $image = null;
            $price = null;
            if ($product->firstVariant) {
                $rawImg = $product->firstVariant->image;
                if ($rawImg) {
                    $decoded = json_decode($rawImg, true);
                    $firstImg = is_array($decoded) ? ($decoded[0] ?? null) : $rawImg;
                    $image = $firstImg ? ImageHelper::getProductImage($firstImg) : null;
                }
                $price = PriceHelper::formatPrice(PriceHelper::applyDiscount(
                    $product->firstVariant->price,
                    $product->firstVariant->discount_type,
                    $product->firstVariant->discount_value
                ));
            }
            $results[] = [
                'type'  => 'product',
                'id'    => $product->id,
                'name'  => $product->name,
                'slug'  => $product->slug,
                'image' => $image,
                'price' => $price,
            ];
        }

        // Search categories
        $categories = \App\Models\Category::where('is_active', 1)
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%");
            })
            ->limit(3)
            ->get();

        foreach ($categories as $cat) {
            $results[] = [
                'type'  => 'category',
                'id'    => $cat->id,
                'name'  => $cat->name,
                'slug'  => $cat->slug,
                'image' => null,
                'price' => null,
            ];
        }

        // Search subcategories
        $subcategories = \App\Models\SubCategory::where('is_active', 1)
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%");
            })
            ->limit(3)
            ->get();

        foreach ($subcategories as $sub) {
            $results[] = [
                'type'  => 'subcategory',
                'id'    => $sub->id,
                'name'  => $sub->name,
                'slug'  => $sub->slug,
                'image' => null,
                'price' => null,
            ];
        }

        // Search child categories
        $childCategories = \App\Models\ChildCategory::where('is_active', 1)
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%");
            })
            ->limit(3)
            ->get();

        foreach ($childCategories as $child) {
            $results[] = [
                'type'  => 'child_category',
                'id'    => $child->id,
                'name'  => $child->name,
                'slug'  => $child->slug,
                'image' => null,
                'price' => null,
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => $results,
        ]);
    }
}
