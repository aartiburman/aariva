<?php

namespace App\Http\Controllers\Frontend\Template1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\OrderItem;
use App\Models\Brand;
use App\Models\Blog;
use App\Models\GeneralSetting;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Helpers\PriceCalculationHelper;
use App\Helpers\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : null;
        $ipAddress = $request->ip();

        $cartQuery = Cart::when($userId, function ($q) use ($userId) {
            return $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            return $q->where('ip_address', $ipAddress);
        });

        $cartProductIds = (clone $cartQuery)->pluck('product_id')->toArray();

        $cartItems = (clone $cartQuery)->with(['product', 'variant'])->get();
        $cartCount = $cartItems->sum('quantity');
        $cartTotal = $cartItems->sum(function ($item) {
            return ($item->variant->price ?? 0) * $item->quantity;
        });

        $wishlistProductIds = Wishlist::when($userId, function ($q) use ($userId) {
            return $q->where('user_id', $userId);
        }, function ($q) use ($ipAddress) {
            return $q->where('ip_address', $ipAddress);
        })
            ->pluck('product_id')
            ->toArray();

        $categories = Category::where('is_active', 1)
            ->with(['subCategories', 'products' => function ($q) {
                $q->where('status', 1)->whereHas('vendor', fn($v) => $v->where('status', 1));
            }])
            ->select(['id', 'name', 'slug', 'image'])
            ->get();

        foreach ($categories as $category) {
            $category->image = ImageHelper::getCategoryImage($category->image);
        }

        $banners = Banner::where('status', 1)
            ->where('position', 'top')
            ->get();

        $promoBanners = Banner::where('status', 1)
            ->where('position', 'promo')
            ->orderBy('order_by', 'ASC')
            ->get();

        foreach ($banners as $banner) {
            $image = $banner->image;
            $decoded = json_decode($image, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $banner->image = array_map(fn($img) => ImageHelper::getBannerImage($img), $decoded);
            } else {
                $banner->image = $image ? [ImageHelper::getBannerImage($image)] : [];
            }
        }

        foreach ($promoBanners as $banner) {
            $image = $banner->image;
            $decoded = json_decode($image, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $banner->image = array_map(fn($img) => ImageHelper::getBannerImage($img), $decoded);
            } else {
                $banner->image = $image ? [ImageHelper::getBannerImage($image)] : [];
            }
        }

        $allProducts = Product::where('status', 1)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1);
            })
            ->with(['category:id,name,slug', 'firstVariant:id,product_id,price,image,discount_type,discount_value', 'approvedReviews'])
            ->latest()
            ->get();

        $transformProduct = function ($product) {
            $variant = $product->firstVariant;
            $product->image = $variant ? ImageHelper::getProductImage($variant->image) : asset('frontend/assets/images/products/01.png');
            $product->original_price = $variant ? $variant->price : 0;
            $product->final_price = $variant ? PriceHelper::applyDiscount($variant->price, $variant->discount_type, $variant->discount_value) : 0;
            $product->avg_rating = $product->approvedReviews->avg('rating') ?? 0;
            $product->discount_percent = $product->original_price > 0 ? round((1 - $product->final_price / $product->original_price) * 100) : 0;
            $product->formatted_price = PriceHelper::formatPrice($product->final_price);
            $product->formatted_original_price = $product->final_price < $product->original_price ? PriceHelper::formatPrice($product->original_price) : null;
            return $product;
        };

        $allProducts = $allProducts->map($transformProduct);

        $categoryProducts = $allProducts->groupBy('category_id')->map(function ($items) {
            return $items->take(8);
        });

        $newArrivals = $allProducts->take(15);

        $featured_products = Product::where('status', 1)
            ->where('is_featured', 1)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1);
            })
            ->with(['firstVariant:id,product_id,price,image,discount_type,discount_value', 'approvedReviews'])
            ->limit(15)
            ->get()
            ->map($transformProduct);

        $deal_products = Product::where('status', 1)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1);
            })
            ->whereJsonContains('product_in', '2')
            ->with(['firstVariant:id,product_id,price,image,discount_type,discount_value', 'approvedReviews'])
            ->limit(15)
            ->get()
            ->map($transformProduct);

        $bestseller_ids = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(15)
            ->pluck('product_id');

        $bestseller_products = Product::whereIn('id', $bestseller_ids)
            ->where('status', 1)
            ->whereHas('vendor', function ($q) {
                $q->where('status', 1);
            })
            ->with(['firstVariant:id,product_id,price,image,discount_type,discount_value', 'approvedReviews'])
            ->get()
            ->map($transformProduct);

        $brands = Brand::where('status', 1)->get();

        $blogPosts = Blog::where('status', 1)
            ->with('author')
            ->latest()
            ->limit(8)
            ->get();

        $generalSettings = GeneralSetting::pluck('value', 'key')->toArray();
        $generalSettings = (object) $generalSettings;

        return view('frontend.home', compact(
            'categories',
            'cartProductIds',
            'wishlistProductIds',
            'cartItems',
            'cartCount',
            'cartTotal',
            'brands',
            'blogPosts',
            'promoBanners',
            'generalSettings',
            'categoryProducts',
            'newArrivals'
        ) + [
            'heroBanners'   => $banners,
            'featuredProducts' => $featured_products,
            'dealProducts'     => $deal_products,
            'bestsellerProducts' => $bestseller_products,
        ]);
    }
}
