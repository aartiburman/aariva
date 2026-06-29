<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use App\Models\Brand;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductSize;
use App\Models\ProductSizeCategory;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ImageHelper;
use App\Helpers\SlugHelper;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;
use App\Helpers\EmailHelper;
use App\Models\Offer;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Log;
use App\Models\ProductVariantLabel;

class ProductController extends Controller
{
    public function product_list(Request $request)
    {
        $user = Auth::user();

        /* ===============================
       ROLE BASED VENDOR IDS
       =============================== */
        if ($user->role == '1') {
            // Admin → can see all vendors (role 2) plus themselves (admin products)
            $vendorsId = User::whereIn('role', ['1', '2'])->pluck('id')->toArray();
            $showroles = array_merge($vendorsId, [$user->id]);
        } else {
            // Vendor (Role 2) → strictly restricted to their own ID
            $showroles = [$user->id];
        }

        /* ===============================
       STATUS COUNTS (Restricted by Role)
       =============================== */
        $statusCounts = Product::whereIn('vendor_id', $showroles)
            ->selectRaw("
            COUNT(*) as total,
            COUNT(CASE WHEN status = 0 THEN 1 END) as pending,
            COUNT(CASE WHEN status = 1 THEN 1 END) as approved,
            COUNT(CASE WHEN status = 2 THEN 1 END) as rejected
        ")
            ->first();

        /* ===============================
       BASE PRODUCT QUERY
       =============================== */
        $query = Product::with(['variants'])
            ->leftJoin('users as vendor', 'vendor.id', '=', 'products.vendor_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('sub_categories', 'sub_categories.id', '=', 'products.subcategory_id')
            ->leftJoin('child_categories', 'child_categories.id', '=', 'products.child_category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->select(
                'products.*',
                'vendor.name as vendor_name',
                'vendor.store_name',
                'categories.name as category_name',
                'categories.name_ar as category_name_ar',
                'categories.name_ne as category_name_ne',
                'sub_categories.name as subcategory_name',
                'sub_categories.name_ar as subcategory_name_ar',
                'sub_categories.name_ne as subcategory_name_ne',
                'child_categories.name as child_category_name',
                'child_categories.name_ar as child_category_name_ar',
                'child_categories.name_ne as child_category_name_ne',
                'brands.name as brand_name'
            )
            // MANDATORY ROLE FILTER:
            ->whereIn('products.vendor_id', $showroles);

        /* ===============================
       STATUS FILTER
       =============================== */
        if ($request->filled('status')) {
            $query->where('products.status', $request->status);
        } elseif ($user->role != '1') {
            // Vendors see all their own statuses by default
            $query->whereIn('products.status', [0, 1, 2]);
        }

        /* ===============================
       SEARCH & FILTERS
       =============================== */
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('products.name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('products.name_ar', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('products.name_ne', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('categories.name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('sub_categories.name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('child_categories.name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('vendor.name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('vendor.store_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('brands.name', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('brand_id')) {
            $query->where('products.brand_id', $request->brand_id);
        }

        // Security check for vendor_id filter
        if ($request->filled('vendor_id')) {
            $requestedVendor = (int) $request->vendor_id;
            if ($user->role == '1') {
                $query->where('products.vendor_id', $requestedVendor);
            } else {
                // If a vendor tries to filter by another ID, force it back to their own
                $query->where('products.vendor_id', $user->id);
            }
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('products.created_at', '>=', $dates[0])
                    ->whereDate('products.created_at', '<=', $dates[1]);
            } else {
                $query->whereDate('products.created_at', $dates[0]);
            }
        }

        /* ===============================
       SORTING
       =============================== */
        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'low_to_high':
                    $query->orderBy(
                        ProductVariant::select('final_price')
                            ->whereColumn('product_id', 'products.id')
                            ->orderBy('final_price', 'asc')
                            ->limit(1)
                    );
                    break;
                case 'high_to_low':
                    $query->orderBy(
                        ProductVariant::select('final_price')
                            ->whereColumn('product_id', 'products.id')
                            ->orderBy('final_price', 'desc')
                            ->limit(1)
                    );
                    break;
                case '1':
                case '2':
                case '3':
                case '4':
                    $query->whereJsonContains('products.product_in', (string) $request->sort_by)
                        ->latest('products.id');
                    break;
                default:
                    $query->latest('products.id');
            }
        } else {
            $query->orderByRaw("
            CASE 
                WHEN JSON_CONTAINS(products.product_in, '\"1\"') THEN 1
                WHEN JSON_CONTAINS(products.product_in, '\"2\"') THEN 2
                WHEN JSON_CONTAINS(products.product_in, '\"3\"') THEN 3
                WHEN JSON_CONTAINS(products.product_in, '\"4\"') THEN 4
                ELSE 5
            END
        ")->latest('products.id');
        }

        /* ===============================
       PAGINATION & DATA PROCESSING
       =============================== */
        $isFiltered = $request->filled('search') || 
                      $request->filled('status') || 
                      $request->filled('brand_id') || 
                      $request->filled('vendor_id') || 
                      $request->filled('date_range');

        $perPage = $isFiltered ? 1000 : 20;
        $products = $query->paginate($perPage)->withQueryString();

        foreach ($products as $product) {
            // Variant Sizes
            foreach ($product->variants as $variant) {
                $sizeIds = json_decode($variant->size, true) ?? [];
                $variant->sizes_list = ProductSize::whereIn('product_sizes.id', $sizeIds)
                    ->where('product_sizes.status', 1)
                    ->join('product_size_category', 'product_size_category.id', '=', 'product_sizes.size_cat_id')
                    ->where('product_size_category.status', 1)
                    ->get(['product_sizes.*']);
            }

            // Available Offers
            $offerIds = json_decode($product->offer_id, true) ?? [];
            if (!empty($offerIds)) {
                $product->available_offers = Offer::whereIn('id', $offerIds)
                    ->where('status', 1)
                    ->where(function ($q) {
                        $now = now();
                        $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                    })
                    ->where(function ($q) {
                        $now = now();
                        $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
                    })
                    ->get();
            } else {
                $product->available_offers = collect();
            }
        }

        if ($request->ajax()) {
            return view('backend.admin.product.product-table', compact('products'))->render();
        }

        /* ===============================
       VIEW DATA
       =============================== */
        $brands = Brand::where('status', 1)->orderBy('name', 'asc')->get();
        $commissionPercent = (float) (GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0);
        $pgFeePercent = (float) (GeneralSetting::where('key', 'pg_fee_percent')->value('value') ?? 0);

        // Only show relevant vendors in the dropdown
        $all_vendors = ($user->role == '1')
            ? User::whereIn('role', ['1', '2'])->get()->map(function($vendor) {
                if ($vendor->role == '1') {
                    $vendor->store_name = 'Nepoora';
                }
                return $vendor;
            })
            : User::where('id', $user->id)->get();

        return view('backend/admin/product/product-list', compact(
            'products',
            'statusCounts',
            'brands',
            'all_vendors',
            'commissionPercent',
            'pgFeePercent'
        ));
    }

    public function approve_product(Request $request)
    {
        $checkAuth = Auth::user()->role;
        if (Auth::user()->role == '1') {

            $vendorsid = User::where('role', '2')->pluck('id')->toArray();
            $showroles = array_merge([Auth::user()->id], $vendorsid);
        } else {

            $showroles = [Auth::user()->id];
        }

        $products = Product::with('variants')
            ->select(
                'products.*',
                'vendor.name as vendor_name',
                'categories.name as category_name',
                'sub_categories.name as subcategory_name',
                'child_categories.name as child_category_name',
                'brands.name as brand_name'
            )
            ->leftJoin('users as vendor', 'vendor.id', 'products.vendor_id')
            ->leftJoin('categories', 'categories.id', 'products.category_id')
            ->leftJoin('sub_categories', 'sub_categories.id', 'products.subcategory_id')
            ->leftJoin('child_categories', 'child_categories.id', 'products.child_category_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->orderBy('products.updated_at', 'DESC')
            ->where('products.status', 1)
            ->whereIn('products.vendor_id', $showroles)
            ->distinct('products.id')

            ->get();

        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                $sizeIds = json_decode($variant->size, true) ?? [];

                $variant->sizes_list = ProductSize::whereIn('product_sizes.id', $sizeIds)
                    ->where('product_sizes.status', 1)
                    ->join('product_size_category', 'product_size_category.id', '=', 'product_sizes.size_cat_id')
                    ->where('product_size_category.status', 1)
                    ->get(['product_sizes.*']);
            }

            // Fetch available coupons
            $couponIds = json_decode($product->coupon_id, true) ?? [];
            if (!empty($couponIds)) {
                $product->available_offers = Offer::whereIn('id', $couponIds)
                    ->where('status', 1)
                    ->where(function ($q) {
                        $now = now();
                        $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                    })
                    ->where(function ($q) {
                        $now = now();
                        $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
                    })
                    ->get();
            } else {
                $product->available_offers = collect();
            }
        }

        return view('backend/admin/product/approve-product', compact('products'));
    }

    public function rejected_product(Request $request)
    {
        $checkAuth = Auth::user()->role;
        if (Auth::user()->role == '1') {
            $vendorsid = User::where('role', '2')->pluck('id')->toArray();
            $showroles = array_merge([Auth::user()->id], $vendorsid);
        } else {

            $showroles = [Auth::user()->id];
        }

        $products = Product::with('variants')
            ->select(
                'products.*',
                'vendor.name as vendor_name',
                'categories.name as category_name',
                'sub_categories.name as subcategory_name',
                'child_categories.name as child_category_name',
                'brands.name as brand_name'
            )
            ->leftJoin('users as vendor', 'vendor.id', 'products.vendor_id')
            ->leftJoin('categories', 'categories.id', 'products.category_id')
            ->leftJoin('sub_categories', 'sub_categories.id', 'products.subcategory_id')
            ->leftJoin('child_categories', 'child_categories.id', 'products.child_category_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->orderBy('products.updated_at', 'DESC')
            ->where('products.status', 2)
            ->whereIn('products.vendor_id', $showroles)
            ->distinct('products.id')

            ->get();

        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                $sizeIds = json_decode($variant->size, true) ?? [];

                $variant->sizes_list = ProductSize::whereIn('product_sizes.id', $sizeIds)
                    ->where('product_sizes.status', 1)
                    ->join('product_size_category', 'product_size_category.id', '=', 'product_sizes.size_cat_id')
                    ->where('product_size_category.status', 1)
                    ->get(['product_sizes.*']);
            }

            // Fetch available coupons
            $couponIds = json_decode($product->coupon_id, true) ?? [];
            if (!empty($couponIds)) {
                $product->available_offers = Offer::whereIn('id', $couponIds)
                    ->where('status', 1)
                    ->where(function ($q) {
                        $now = now();
                        $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                    })
                    ->where(function ($q) {
                        $now = now();
                        $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
                    })
                    ->get();
            } else {
                $product->available_offers = collect();
            }
        }
        return view('backend/admin/product/rejected-product', compact('products'));
    }

    public function pending_product(Request $request)
    {
        $checkAuth = Auth::user()->role;
        if (Auth::user()->role == '1') {
            $vendorsid = User::where('role', '2')->pluck('id')->toArray();
            $showroles = array_merge([Auth::user()->id], $vendorsid);
        } else {

            $showroles = [Auth::user()->id];
        }

        $products = Product::with('variants')
            ->select(
                'products.*',
                'vendor.name as vendor_name',
                'categories.name as category_name',
                'sub_categories.name as subcategory_name',
                'child_categories.name as child_category_name',
                'brands.name as brand_name'
            )
            ->leftJoin('users as vendor', 'vendor.id', 'products.vendor_id')
            ->leftJoin('categories', 'categories.id', 'products.category_id')
            ->leftJoin('sub_categories', 'sub_categories.id', 'products.subcategory_id')
            ->leftJoin('child_categories', 'child_categories.id', 'products.child_category_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->orderBy('products.updated_at', 'DESC')
            ->whereIn('products.vendor_id', $showroles)
            ->where('products.status', 0)
            ->distinct('products.id')

            ->get();

        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                $sizeIds = json_decode($variant->size, true) ?? [];

                $variant->sizes_list = ProductSize::whereIn('product_sizes.id', $sizeIds)
                    ->where('product_sizes.status', 1)
                    ->join('product_size_category', 'product_size_category.id', '=', 'product_sizes.size_cat_id')
                    ->where('product_size_category.status', 1)
                    ->get(['product_sizes.*']);
            }

            // Fetch available coupons
            $couponIds = json_decode($product->coupon_id, true) ?? [];
            if (!empty($couponIds)) {
                $product->available_offers = Offer::whereIn('id', $couponIds)
                    ->where('status', 1)
                    ->where(function ($q) {
                        $now = now();
                        $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                    })
                    ->where(function ($q) {
                        $now = now();
                        $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
                    })
                    ->get();
            } else {
                $product->available_offers = collect();
            }
        }
        return view('backend/admin/product/pending-product', compact('products'));
    }


    public function product_detail($id)
    {
        $user = Auth::user();
        $query = Product::with(['variants', 'vendor', 'category', 'subCategory', 'childCategory', 'brand', 'reviews.user']);

        // Vendor restriction
        if ($user->role == 2) {
            $query->where('vendor_id', $user->id);
        }

        $product = $query->find($id);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found or access denied');
        }

        $commissionPercent = (float) (GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0);
        $pgFeePercent = (float) (GeneralSetting::where('key', 'pg_fee_percent')->value('value') ?? 0);
        
        // Fetch all variant labels and map them by ID for easy access in Blade
        $variantLabels = DB::table('product_variant_labels')->get()->keyBy('id')->map(function($label) {
            return $label->{"name_" . app()->getLocale()} ?? $label->name;
        })->toArray();

        return view('backend.admin.product.product-detail', compact('product', 'commissionPercent', 'pgFeePercent', 'variantLabels'));
    }

    public function add_product(Request $request)
    {
        // echo '<pre>';print_r(Auth::user()->category_ids);die;

        if (Auth::user()->role == '2') {
            $categories_data = Category::select('*')->where('is_active', 1)->whereIn('id', Auth::user()->category_ids)->get();
        } else {
            $categories_data = Category::select('*')->where('is_active', 1)->get();
        }

        $subcategories = SubCategory::select('*')->where('is_active', 1)->get();
        $childcategory = ChildCategory::select('*')->where('is_active', 1)->get();
        $brand = Brand::select('*')->where('is_active', 1)->get();
        $sizecategory = ProductSizeCategory::select('*')->where('status', 1)->get();
        $offers = Offer::where('status', 1)->get();
        $product_variant_labels = DB::table('product_variant_labels')->get();

        $commissionPercent = (float) (GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0);
        $pgFeePercent = (float) (GeneralSetting::where('key', 'pg_fee_percent')->value('value') ?? 0);

        return view('backend/admin/product/add-product', compact('categories_data', 'subcategories', 'childcategory', 'brand', 'sizecategory', 'offers', 'commissionPercent', 'pgFeePercent', 'product_variant_labels'));
    }


    public function store_product(Request $request)
    {
        $rules = [

            'name'              => 'required|string|max:255',
            'category_id'       => 'required',
            'sku.*'           => 'required|string',
            'color.*'         => 'required|string',
            'stock.*'           => 'required|numeric',
            'product_variant.*' => 'nullable',
            'price.*'           => 'required|integer',
            'discount_type.*'   => 'nullable|in:%,off,Percentage,percentage,flate,Fixed Amount',
            'discount_value.*'  => 'nullable|integer',
        ];

        $request->validate($rules);

    
        try {
            $trAr = new GoogleTranslate('ar');
            $trNe = new GoogleTranslate('ne');

            /* ================= MAIN PRODUCT ================= */
            $slug = $request->slug ? SlugHelper::uniqueProductSlug($request->slug) : SlugHelper::uniqueProductSlug($request->name);

            // Translation Helper (closure to prevent crash if API is down)
            $translate = function ($text, $lang) use ($trAr, $trNe) {
                try {
                    return ($lang == 'ar') ? $trAr->translate($text) : $trNe->translate($text);
                } catch (\Exception $e) {
                    return $text; // Fallback to English if API fails
                }
            };

            $product = Product::create([
                'name'                 => $request->name,
                'name_ar'              => $translate($request->name, 'ar'),
                'name_ne'              => $translate($request->name, 'ne'),
                'slug'                 => $slug,
                'slug_ar'              => Str::slug($slug),
                'slug_ne'              => Str::slug($slug),
                'category_id'          => $request->category_id,
                'subcategory_id'       => $request->subcategory_id,
                'child_category_id'    => $request->child_category_id,
                'brand_id'             => $request->brand_id,
                'short_description'    => $request->short_description,
                'short_description_ar' => $request->short_description ? $translate($request->short_description, 'ar') : null,
                'short_description_ne' => $request->short_description ? $translate($request->short_description, 'ne') : null,
                'description'          => $request->description,
                'description_ar'       => $request->description ? $translate($request->description, 'ar') : null,
                'description_ne'       => $request->description ? $translate($request->description, 'ne') : null,
                'vendor_id'            => Auth::id(),
                'offer_id'             => isset($request->offers) ? json_encode($request->offers) : null,
                'product_in'           => isset($request->product_in) ? json_encode($request->product_in) : null,
                'is_featured'          => $request->is_featured ?? 0,
                'vendor_warranty'      => $request->vendor_warranty,
                'vendor_payment'       => $request->vendor_payment ?? 0,
                'vendor_return'        => $request->vendor_return ?? 0,
                'vendor_delivery'      => $request->vendor_delivery ?? 0,
            ]);

            /* ================= VARIANTS ================= */
            foreach ($request->variant as $index => $variantValue) {
                $price         = $request->price[$index] ?? 0;
                $discountType  = $request->discount_type[$index] ?? null;
                $discountValue = $request->discount_value[$index] ?? 0;
                $finalPrice    = $price; // Default to full price

                // Price Calculation Logic
                if (in_array($discountType, ['%', 'Percentage', 'percentage'])) {
                    $finalPrice = $price - (($price * $discountValue) / 100);
                } elseif (in_array($discountType, ['off', 'flate', 'Fixed Amount'])) {
                    $finalPrice = $price - $discountValue;
                }

                // Sizes
                $sizes = $request->size[$index] ?? [];
                $sizes = array_values(array_filter($sizes));

                // Images
                $variantImages = [];
                if ($request->hasFile("product_image.$index")) {
                    foreach ($request->file("product_image.$index") as $file) {
                        $variantImages[] = ImageHelper::compressImage($file, 'uploads/products');
                    }
                }

                ProductVariant::create([
                    'product_id'       => $product->id,
                    'sku'              => $request->sku[$index],
                    'color'            => $request->color[$index],
                    'color_ar'         => $translate($request->color[$index], 'ar'),
                    'color_ne'         => $translate($request->color[$index], 'ne'),
                    'product_variant'  => $request->product_variant[$index] ?? null,
                    'material'         => $request->material[$index] ?? null,
                    'package_weight'   => $request->package_weight[$index] ?? null,
                    'package_length'   => $request->package_length[$index] ?? null,
                    'package_width'    => $request->package_width[$index] ?? null,
                    'package_height'   => $request->package_height[$index] ?? null,
                    'package_type'     => $request->package_type[$index] ?? null,
                    'size_cat_id'      => $request->size_category_id[$index] ?? null,
                    'size'             => json_encode($sizes),
                    'size_ar'          => json_encode($sizes),
                    'size_ne'          => json_encode($sizes),
                    'stock'            => $request->stock[$index],
                    'price'            => $price,
                    'price_ar'         => $translate($price, 'ar'),
                    'price_ne'         => $translate($price, 'ne'),
                    'discount_type'    => $discountType,
                    'discount_value'   => $discountValue,
                    'final_price'      => $finalPrice,
                    'final_price_ar'   => $translate($finalPrice, 'ar'),
                    'final_price_ne'   => $translate($finalPrice, 'ne'),
                    'image'            => json_encode($variantImages),
                    'vendor_id'        => $product->vendor_id,
                ]);
            }

            // Commit changes to Database
            DB::commit();

            // Notify Admins
            NotificationHelper::notifyAdmins([
                'title'    => 'New Product Added',
                'message'  => 'Vendor ' . Auth::user()->name . ' added: ' . $product->name,
                'type'     => 'product',
                'url'      => route('product.list'),
                'icon'     => 'solar:box-bold-duotone',
                'priority' => 'medium'
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'status'  => true,
                    'message' => 'Product & variants added successfully!',
                    'redirect' => route('product.list')
                ]);
            }

            return redirect()->route('product.list')->with('success', 'Product & variants added successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); // Undo any partial saves
            Log::error("Product Store Error: " . $e->getMessage());

            if (request()->ajax()) {
                return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

   public function store_similar_product(Request $request)
{
    /* ================= VALIDATION ================= */
    $request->validate([
        /* PRODUCT */
        'name'              => 'required|string|max:255',
        'slug'              => 'nullable|string|max:255|unique:products,slug',

        'category_id'       => 'required|exists:categories,id',
        'subcategory_id'    => 'nullable|exists:sub_categories,id',
        'child_category_id' => 'nullable|exists:child_categories,id',
        'brand_id'          => 'nullable|exists:brands,id',

        'short_description' => 'nullable|string|max:500',
        'description'       => 'nullable|string',

        'is_featured'       => 'nullable|boolean',
        'status'            => 'nullable|boolean',

        /* ARRAYS */
        'variant'           => 'required|array|min:1',
        'variant.*'         => 'required',

        'sku'               => 'required|array|min:1',
        'sku.*'             => 'required|string|max:100|distinct|unique:product_variants,sku',

        'color'             => 'required|array|min:1',
        'color.*'           => 'required|string|max:50',

        'stock'             => 'required|array|min:1',
        'stock.*'           => 'required|integer|min:0',

        'price'             => 'required|array|min:1',
        'price.*'           => 'required|numeric|min:0',

        /* OPTIONAL */
        'product_variant'   => 'nullable|array',
        'product_variant.*' => 'nullable|string|max:100',

        'size'              => 'nullable|array',
        'size.*'            => 'nullable|array',
        'size.*.*'          => 'nullable|string|max:50',

        'size_cat_id'       => 'nullable|array',
        'size_cat_id.*'     => 'nullable|exists:product_size_category,id',

        'material'          => 'nullable|array',
        'material.*'        => 'nullable|string|max:100',

        /* DISCOUNT */
        'discount_type'     => 'nullable|array',
        'discount_type.*'   => 'nullable|in:percent,%,flat,off',

        'discount_value'    => 'nullable|array',
        'discount_value.*'  => 'nullable|numeric|min:0|max:100',

        /* IMAGES */
        'product_image'     => 'nullable|array',
        'product_image.*'   => 'nullable|array',
        'product_image.*.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

        /* OTHER */
        'variant_id'        => 'nullable|array',
        'variant_id.*'      => 'nullable|exists:product_variants,id',

        'coupons'           => 'nullable|array',
        'coupons.*'         => 'exists:coupons,id',

        'product_in'        => 'nullable|array',
    ]);

    /* ================= BUSINESS VALIDATION ================= */
    foreach ($request->price as $i => $price) {
        $discountType  = $request->discount_type[$i] ?? null;
        $discountValue = $request->discount_value[$i] ?? 0;

        if (in_array($discountType, ['%', 'percent']) && $discountValue > 100) {
            return back()->withErrors("Discount % cannot be more than 100 at row " . ($i + 1));
        }

        if (in_array($discountType, ['flat', 'off']) && $discountValue > $price) {
            return back()->withErrors("Flat discount cannot exceed price at row " . ($i + 1));
        }
    }

    /* ================= SLUG ================= */
    $slug = $request->slug
        ? SlugHelper::uniqueProductSlug($request->slug)
        : SlugHelper::uniqueProductSlug($request->name);

    $trAr = new GoogleTranslate('ar');
    $trNe = new GoogleTranslate('ne');

    /* ================= CREATE PRODUCT ================= */
    $product = Product::create([
        'name'              => $request->name,
        'name_ar'           => $trAr->translate($request->name),
        'name_ne'           => $trNe->translate($request->name),

        'slug'              => $slug,
        'slug_ar'           => Str::slug($slug),
        'slug_ne'           => Str::slug($slug),

        'category_id'       => $request->category_id,
        'subcategory_id'    => $request->subcategory_id,
        'child_category_id' => $request->child_category_id,
        'brand_id'          => $request->brand_id,

        'short_description' => $request->short_description,
        'short_description_ar' => $request->short_description ? $trAr->translate($request->short_description) : null,
        'short_description_ne' => $request->short_description ? $trNe->translate($request->short_description) : null,

        'description'       => $request->description,
        'description_ar'    => $request->description ? $trAr->translate($request->description) : null,
        'description_ne'    => $request->description ? $trNe->translate($request->description) : null,

        'vendor_id'         => Auth::id(),
        'coupon_id'         => $request->coupons ? json_encode($request->coupons) : null,
        'product_in'        => $request->product_in ? json_encode($request->product_in) : null,

        'is_featured'       => $request->is_featured ?? 0,
        'status'            => $request->status ?? 0,
        're_added'          => 1,

        'vendor_warranty'   => $request->vendor_warranty ?? 0,
        'vendor_payment'    => $request->vendor_payment ?? 0,
        'vendor_return'     => $request->vendor_return ?? 0,
        'vendor_delivery'   => $request->vendor_delivery ?? 0,
    ]);

    /* ================= NOTIFICATION ================= */
    NotificationHelper::notifyAdmins([
        'title' => 'New Similar Product Added',
        'message' => 'Vendor ' . Auth::user()->name . ' added product: ' . $product->name,
        'type' => 'product',
        'url' => route('product.list'),
        'icon' => 'solar:box-bold-duotone',
        'priority' => 'medium'
    ]);

    /* ================= VARIANTS ================= */
    foreach ($request->variant as $index => $v_val) {

        $price         = (float) ($request->price[$index] ?? 0);
        $discountType  = $request->discount_type[$index] ?? null;
        $discountValue = (float) ($request->discount_value[$index] ?? 0);

        /* FINAL PRICE */
        $finalPrice = $price;

        if (in_array($discountType, ['%', 'percent'])) {
            $finalPrice = max(0, $price - ($price * $discountValue / 100));
        } elseif (in_array($discountType, ['flat', 'off'])) {
            $finalPrice = max(0, $price - $discountValue);
        }

        /* SIZE */
        $sizes = $request->size[$index] ?? [];
        $sizes = array_values(array_filter($sizes));

        /* IMAGE HANDLING */
        $variantImages = [];

        if ($request->hasFile("product_image.$index")) {
            foreach ($request->file("product_image.$index") as $file) {
                $filename = ImageHelper::compressImage($file, 'uploads/products');
                $variantImages[] = $filename;
            }
        } elseif (!empty($request->variant_id[$index])) {

            $sourceVariant = ProductVariant::find($request->variant_id[$index]);

            if ($sourceVariant) {
                $oldImages = json_decode($sourceVariant->image, true) ?? [];

                foreach ($oldImages as $img) {
                    $oldPath = public_path('uploads/products/' . $img);

                    if (file_exists($oldPath)) {
                        $newName = time() . '_' . uniqid() . '_' . $img;
                        copy($oldPath, public_path('uploads/products/' . $newName));
                        $variantImages[] = $newName;
                    }
                }
            }
        }

        /* CREATE VARIANT */
        ProductVariant::create([
            'product_id'      => $product->id,
            'sku'             => $request->sku[$index],
            'color'           => $request->color[$index],
            'color_ar'        => $trAr->translate($request->color[$index]),
            'color_ne'        => $trNe->translate($request->color[$index]),

            'product_variant' => $request->product_variant[$index] ?? null,
            'size_cat_id'     => $request->size_cat_id[$index] ?? null,

            'size'            => json_encode($sizes),
            'size_ar'         => json_encode($sizes),
            'size_ne'         => json_encode($sizes),

            'stock'           => (int) $request->stock[$index],
            'price'           => $price,
            'price_ar'        => $trAr->translate($price),
            'price_ne'        => $trNe->translate($price),

            'discount_type'   => $discountType,
            'discount_value'  => $discountValue,

            'final_price'     => $finalPrice,
            'final_price_ar'  => $trAr->translate($finalPrice),
            'final_price_ne'  => $trNe->translate($finalPrice),

            'vendor_id'       => $product->vendor_id,
            'image'           => json_encode($variantImages),
            'material'        => $request->material[$index] ?? null,
        ]);
    }

    /* ================= RESPONSE ================= */
    if ($request->ajax()) {
        return response()->json([
            'status'  => true,
            'message' => 'Similar product created successfully!'
        ]);
    }

    return redirect()->route('product.list')
        ->with('success', 'Similar product created successfully!');
}


    public function change_product_status(Request $request)
    {
        // Validate request
        $request->validate([
            'id'         => 'required|exists:products,id',
            'status'     => 'required|in:0,1,2',
            'rejection_reason' => 'required_if:status,2'
        ]);

        // Update product status
        $product = Product::findOrFail($request->id);
        $product->status = $request->status;
        if ($request->status == 2) {
            $product->rejection_reason = $request->rejection_reason;
        } else {
            $product->rejection_reason = null;
        }
        $product->save();

        // Notify Vendor
        $statusText = $request->status == 1 ? 'Approved' : ($request->status == 2 ? 'Rejected' : 'Pending');
        $priority = $request->status == 1 ? 'medium' : ($request->status == 2 ? 'critical' : 'low');
        $message = "Your product '{$product->name}' has been " . strtolower($statusText) . ".";
        if ($request->status == 2) {
            $message .= " Reason: " . $request->rejection_reason;
        }

        NotificationHelper::notifyVendor($product->vendor_id, [
            'title' => 'Product Status Update',
            'message' => $message,
            'type' => 'product',
            'url' => route('product.list'), // Adjust to actual route if needed
            'icon' => $request->status == 1 ? 'solar:check-circle-bold-duotone' : 'solar:close-circle-bold-duotone',
            'priority' => $priority
        ]);

        // Email Notification
        $vendor = User::find($product->vendor_id);
        if ($vendor) {
            $statusText = 'Pending';
            if ($request->status == 1) $statusText = 'Approved';
            elseif ($request->status == 2) $statusText = 'Rejected';

            $appUrl = config('app.url');
            $message = "
                <div style='text-align: center;'>
                    <h2 style='color: #6c5ce7;'>Product Update</h2>
                    <p>Hello <strong>{$vendor->name}</strong>,</p>
                    <p>The status of your product \"<strong>{$product->name}</strong>\" has been updated to: <strong style='color: #6c5ce7;'>{$statusText}</strong>.</p>
            ";

            if ($request->status == 2 && $request->rejection_reason) {
                $message .= "
                    <div style='background: #fff5f5; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #fed7d7; text-align: left;'>
                        <p style='margin: 0; color: #c53030;'><strong>Reason for Rejection:</strong><br>{$request->rejection_reason}</p>
                    </div>
                ";
            }

            $message .= "
                    <p>You can view and manage your products by logging into your vendor dashboard.</p>
                </div>
            ";

            EmailHelper::send(
                $vendor->email,
                'Product Status Update - ' . $statusText,
                $message,
                'emails.common',
                [
                    'action_url' => $appUrl . '/product-list',
                    'action_text' => 'View My Products'
                ]
            );
        }

        return response()->json([
            'status'  => true,
            'message' => 'Product status updated successfully',
            'statusCounts' => $this->getStatusCounts()
        ]);
    }


    public function edit_product(Request $request, $id)
    {
        $user = Auth::user();
        $query = Product::where('id', $id);


        $product_data = $query->first();

        if (!$product_data) {
            return redirect()->back()->with('error', 'Product not found or access denied');
        }

        $categories = Category::select('*')->where('is_active', 1)->get();

        $subcategories = SubCategory::select('*')
            ->where('is_active', 1)
            ->where('category_id', $product_data->category_id)
            ->get();

        $childcategory = ChildCategory::select('*')
            ->where('is_active', 1)
            ->where('subcategory_id', $product_data->subcategory_id)
            ->get();

        // Brand filtering logic matching BrandController
        $brandQuery = Brand::select('*')->where('status', 1);

        if ($product_data->child_category_id) {
            $brandQuery->where('childcategory_id', $product_data->child_category_id);
        } elseif ($product_data->subcategory_id) {
            $brandQuery->where('subcategory_id', $product_data->subcategory_id);
        } elseif ($product_data->category_id) {
            $brandQuery->where('category_id', $product_data->category_id);
        }

        $brand = $brandQuery->get();

        $offers = Offer::where('status', 1)->get();
        $sizecategory = ProductSizeCategory::select('*')->where('status', 1)->get();

        // echo '<pre>';print_r($product_data->product_in);die;



        return view('backend/admin/product/edit-product', compact('categories', 'subcategories', 'childcategory', 'product_data', 'brand', 'sizecategory', 'offers'));
    }



    public function edit_variant(Request $request, $id)
    {
        $user = Auth::user();
        $productQuery = Product::where('id', $id);

        // Vendor restriction


        $product = $productQuery->first();

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found or access denied');
        }

        $variant = ProductVariant::select('*')->where('product_id', $id)->get();
        $sizecategory = ProductSizeCategory::select('*')->where('status', 1)->get();


        $sizes = ProductSize::join('product_size_category', 'product_size_category.id', '=', 'product_sizes.size_cat_id')
            ->where('product_sizes.status', 1)
            ->where('product_size_category.status', 1)
            ->get(['product_sizes.*']);

        foreach ($variant as $value) {
            $sizeIds = json_decode($value->size, true) ?? [];

            $value->sizes_list = ProductSize::whereIn('product_sizes.id', $sizeIds)
                ->where('product_sizes.status', 1)
                ->join('product_size_category', 'product_size_category.id', '=', 'product_sizes.size_cat_id')
                ->where('product_size_category.status', 1)
                ->get(['product_sizes.*']);
        }

        $commissionPercent = (float) (GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0);
        $pgFeePercent = (float) (GeneralSetting::where('key', 'pg_fee_percent')->value('value') ?? 0);
        $product_variant_labels = DB::table('product_variant_labels')->get();
        return view('backend/admin/product/edit-variant', compact('variant', 'sizecategory', 'sizes', 'commissionPercent', 'pgFeePercent', 'product_variant_labels'));
    }




    public function update_variant(Request $request)
    {
        $user = Auth::user();
        if (empty($request->sku) || !is_array($request->sku)) {
            return redirect()->back()->with('error', 'No variants found to update.');
        }

        if (empty($request->product_id)) {
            return redirect()->back()->with('error', 'Product ID is missing.');
        }

        // Vendor restriction
        $productQuery = Product::where('id', $request->product_id);

        $product = $productQuery->first();

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found or access denied');
        }

        $request->validate([
            'sku.*'            => 'required|string',
            'color.*'          => 'required|string',
            'stock.*'          => 'required|numeric',
            'price.*'          => 'required|integer',
        ]);

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('ne');

        foreach ($request->sku as $index => $sku) {
            if (empty($sku)) continue;

            /* ================= FIND OR CREATE VARIANT ================= */
            $variantId = $request->variant_id[$index] ?? null;


            if (!empty($variantId) && is_numeric($variantId)) {
                $variant = ProductVariant::find($variantId);
                if (!$variant) continue;
            } else {
                $variant = new ProductVariant();
                $variant->product_id = (int) $request->product_id;
            }

            /* ================= BASIC DATA ================= */
            $material = $request->material[$index] ?? null;
            $color    = $request->color[$index] ?? null;

            $variant->sku            = $sku;
            $variant->color          = $color;
            $variant->stock          = (int) ($request->stock[$index] ?? 0);
            $variant->material       = $material;
            $variant->package_weight = $request->package_weight[$index] ?? null;
            $variant->package_length = $request->package_length[$index] ?? null;
            $variant->package_width  = $request->package_width[$index] ?? null;
            $variant->package_height = $request->package_height[$index] ?? null;
            $variant->package_type   = $request->package_type[$index] ?? null;

            /* ================= TRANSLATIONS ================= */
            try {
                $variant->material_ar = $material ? $trAr->translate($material) : null;
                $variant->material_ne = $material ? $trNe->translate($material) : null;
                $variant->color_ar    = $color ? $trAr->translate($color) : null;
                $variant->color_ne    = $color ? $trNe->translate($color) : null;
            } catch (\Exception $e) {
                $variant->material_ar = $material;
                $variant->material_ne = $material;
                $variant->color_ar    = $color;
                $variant->color_ne    = $color;
                $variant->price_ar    = $price ? $trAr->translate($price) : null;
                $variant->price_ne    = $price ? $trNe->translate($price) : null;
            }

            /* ================= PRICE & DISCOUNT ================= */
            $price         = (float) ($request->price[$index] ?? 0);
            $discountType  = $request->discount_type[$index] ?? null;
            $discountValue = (float) ($request->discount_value[$index] ?? 0);

            $finalPrice = $price;
            if ($discountType === '%') {
                $finalPrice = $price - (($price * $discountValue) / 100);
            } elseif ($discountType === 'off') {
                $finalPrice = $price - $discountValue;
            }

            $variant->price          = $price;
            $variant->discount_type  = $discountType;
            $variant->discount_value = $discountValue;
            $variant->final_price    = max(0, $finalPrice);

            /* ================= SIZE CATEGORY & SIZES ================= */
            $variant->size_cat_id = $request->size_category_id[$index] ?? null;
            $variant->product_variant = $request->product_variant[$index] ?? null;
            $sizes = $request->size[$index] ?? [];
            $variant->size = json_encode(array_values(array_filter($sizes)));
            $variant->vendor_id = $product->vendor_id;

            /* ================= IMAGES ================= */
            $images = [];

            // 1. Start with existing images if updating
            if (!empty($variant->id)) {
                $images = json_decode($variant->image, true) ?? [];

                // Apply sorting order if provided for this index or variant ID
                $orderedJson = $request->image_order[$index] ?? ($request->image_order[$variant->id] ?? null);

                if ($orderedJson !== null) {
                    $ordered = json_decode($orderedJson, true) ?? [];

                    // Physical deletion of removed images
                    $removedImages = array_diff($images, $ordered);
                    foreach ($removedImages as $removedImg) {
                        $oldPath = public_path('uploads/products/' . $removedImg);
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }

                    // Keep only images that still exist in the variant's current image array
                    $images = array_values(array_intersect($ordered, $images));
                }
            }

            // 2. Add newly uploaded images
            if ($request->hasFile("product_image.$index")) {
                foreach ($request->file("product_image.$index") as $file) {
                    $filename = ImageHelper::compressImage($file, 'uploads/products');
                    $images[] = $filename;
                }
            }

            $variant->image = json_encode($images);

            /* ================= SAVE ================= */


            try {
                $variant->save();
            } catch (\Exception $e) {
                // Log error if necessary
            }
        }

        return redirect()->back()->with('success', 'Product variants updated successfully!');
    }

    public function delete_product_image(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|integer|exists:product_variants,id',
            'image_name' => 'required|string',
        ]);

        $variant = ProductVariant::find($request->variant_id);
        if (!$variant) {
            return response()->json(['success' => false, 'message' => 'Variant not found.']);
        }

        $images = json_decode($variant->image, true) ?? [];
        $imageName = $request->image_name;

        if (in_array($imageName, $images)) {
            // Remove from array
            $newImages = array_diff($images, [$imageName]);
            $variant->image = json_encode(array_values($newImages));
            $variant->save();

            // Delete file
            $path = public_path('uploads/products/' . $imageName);
            if (File::exists($path)) {
                File::delete($path);
            }

            return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Image not found in this variant.']);
        }
    }



    public function store_edit_variants(Request $request)
    {
        if (empty($request->sku) || !is_array($request->sku)) {
            return response()->json(['success' => false, 'message' => 'No variants found to update.']);
        }

        if (empty($request->product_id)) {
            return response()->json(['success' => false, 'message' => 'Product ID is missing.']);
        }

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('ne');

        $variantIds = [];
        foreach ($request->sku as $index => $sku) {
            if (empty($sku)) continue;

            $variantId = $request->variant_id[$index] ?? null;

            if (!empty($variantId) && is_numeric($variantId)) {
                $variant = ProductVariant::find($variantId);
                if (!$variant) continue;
            } else {
                $variant = new ProductVariant();
                $variant->product_id = (int) $request->product_id;
            }

            $material = $request->material[$index] ?? null;
            $color    = $request->color[$index] ?? null;

            $variant->sku            = $sku;
            $variant->color          = $color;
            $variant->stock          = (int) ($request->stock[$index] ?? 0);
            $variant->material       = $material;
            $variant->package_weight = $request->package_weight[$index] ?? null;
            $variant->package_length = $request->package_length[$index] ?? null;
            $variant->package_width  = $request->package_width[$index] ?? null;
            $variant->package_height = $request->package_height[$index] ?? null;
            $variant->package_type   = $request->package_type[$index] ?? null;

            try {
                $variant->material_ar = $material ? $trAr->translate($material) : null;
                $variant->material_ne = $material ? $trNe->translate($material) : null;
                $variant->color_ar    = $color ? $trAr->translate($color) : null;
                $variant->color_ne    = $color ? $trNe->translate($color) : null;
                $variant->price_ar    = $price ? $trAr->translate($price) : null;
                $variant->price_ne    = $price ? $trNe->translate($price) : null;
            } catch (\Exception $e) {
                $variant->material_ar = $material;
                $variant->material_ne = $material;
                $variant->color_ar    = $color ? $trAr->translate($color) : null;
                $variant->color_ne    = $color ? $trNe->translate($color) : null;
            }

            $price         = (float) ($request->price[$index] ?? 0);
            $discountType  = $request->discount_type[$index] ?? null;
            $discountValue = (float) ($request->discount_value[$index] ?? 0);

            $finalPrice = $price;
            if ($discountType === '%') {
                $finalPrice = $price - (($price * $discountValue) / 100);
            } elseif ($discountType === 'off') {
                $finalPrice = $price - $discountValue;
            }

            $variant->price          = $price;
            $variant->discount_type  = $discountType;
            $variant->discount_value = $discountValue;
            $variant->final_price    = max(0, $finalPrice);
            $variant->final_price_ar = $trAr->translate(max(0, $finalPrice));
            $variant->final_price_ne = $trNe->translate(max(0, $finalPrice));

            $variant->size_cat_id = $request->size_category_id[$index] ?? null;
            $sizes = $request->size[$index] ?? [];
            $variant->size = json_encode(array_values(array_filter($sizes)));

            $images = [];
            if (!empty($variant->id)) {
                $images = json_decode($variant->image, true) ?? [];

                // Apply sorting order if provided for this index or variant ID
                $orderedJson = $request->image_order[$index] ?? ($request->image_order[$variant->id] ?? null);

                if ($orderedJson !== null) {
                    $ordered = json_decode($orderedJson, true) ?? [];

                    // Physical deletion of removed images
                    $removedImages = array_diff($images, $ordered);
                    foreach ($removedImages as $removedImg) {
                        $oldPath = public_path('uploads/products/' . $removedImg);
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }

                    // Keep only images that still exist in the variant's current image array
                    $images = array_values(array_intersect($ordered, $images));
                }
            }

            if ($request->hasFile("product_image.$index")) {
                foreach ($request->file("product_image.$index") as $file) {
                    $filename = time() . '_' . Str::slug($color ?? 'variant') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/products'), $filename);
                    $images[] = $filename;
                }
            }

            $variant->image = json_encode($images);

            try {
                $variant->save();
                $variantIds[$index] = $variant->id;
            } catch (\Exception $e) {
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product variants updated successfully!',
            'variant_ids' => $variantIds
        ]);
    }

    public function delete_variant_image(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|integer',
            'image' => 'required|string',
        ]);

        $variant = ProductVariant::findOrFail($request->variant_id);
        $images = json_decode($variant->image, true) ?? [];

        // Remove the image from array
        if (($key = array_search($request->image, $images)) !== false) {
            unset($images[$key]);

            // Save updated images
            $variant->image = json_encode(array_values($images));
            $variant->save();

            // Delete the physical file
            $filePath = public_path('uploads/products/' . $request->image);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function delete_variant(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|integer|exists:product_variants,id',
        ]);

        $variant = ProductVariant::findOrFail($request->variant_id);

        // Delete variant images from disk
        $images = json_decode($variant->image, true) ?? [];
        foreach ($images as $img) {
            $path = public_path('uploads/products/' . $img);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        // Delete variant record
        try {
            $variant->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unable to delete variant'], 500);
        }
    }


    public function update_product(Request $request)
    {
        // Validate request
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'category_id' => 'required|integer',
            'subcategory_id' => 'nullable|integer',
            'child_category_id' => 'nullable|integer',
            'brand_id' => 'nullable|integer',
            // 'price' => 'required|numeric',
            // 'discount_price' => 'nullable|numeric',
            'status' => 'required|integer',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'product_image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Find the product
        $user = Auth::user();
        $query = Product::where('id', $request->product_id);

        // Vendor restriction

        $product = $query->first();

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found or access denied');
        }

        $status = $request->status;
        if (Auth::user()->role == 2) {
            $status = $product->status;
        }

        $price = $request->price;
        $discountPercentage = $request->discount;

        $discountedPrice = null;
        if ($discountPercentage) {
            $discountAmount = ($price * $discountPercentage) / 100;
            $discountedPrice = $price - $discountAmount;
        }

        // If discount_price is explicitly provided, use it instead
        $finalDiscountPrice = $request->discount_price ?? $discountedPrice;

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('ne');

        // Update product details
        $product->update([
            'name' => $request->name,
            'name_ar' => $trAr->translate($request->name),
            'name_ne' => $trNe->translate($request->name),

            'slug' => $request->slug ?: SlugHelper::uniqueProductSlug($request->name),
            'slug_ar' => Str::slug($request->slug ?: $request->name),
            'slug_ne' => Str::slug($request->slug ?: $request->name),

            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id ?? null,
            'child_category_id' => $request->child_category_id ?? null,
            'brand_id' => $request->brand_id ?? null,

            'status' => $status,

            'short_description' => $request->short_description ?? null,
            'short_description_ar' => $request->short_description ? $trAr->translate($request->short_description) : null,
            'short_description_ne' => $request->short_description ? $trNe->translate($request->short_description) : null,

            'description' => $request->description ?? null,
            'description_ar' => $request->description ? $trAr->translate($request->description) : null,
            'description_ne' => $request->description ? $trNe->translate($request->description) : null,

            'offer_id' =>    isset($request->offers) ? json_encode($request->offers) : null,
            'product_in' =>  isset($request->product_in) ? json_encode($request->product_in) : null,
            'is_featured'       => $request->is_featured ?? 0,
            'vendor_warranty'   => $request->vendor_warranty ?? 0,
            'vendor_payment'    => $request->vendor_payment ?? 0,
            'vendor_return'     => $request->vendor_return ?? 0,
            'vendor_delivery'   => $request->vendor_delivery ?? 0,
        ]);

        // Handle new product images if uploaded
        if ($request->hasFile('product_image')) {
            $images = [];
            foreach ($request->file('product_image') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/products/'), $filename);
                $images[] = $filename;
            }


            $existingImages = $product->images ? json_decode($product->images, true) : [];
            $allImages = array_merge($existingImages, $images);
            $product->images = json_encode($allImages);
        }

        $product->save();

        return redirect()->route('product.list')->with('success', 'Product updated successfully!');
    }


    private function getStatusCounts()
    {
        $user = Auth::user();
        if ($user->role == '1') {
            $vendorsId = User::where('role', '2')->pluck('id')->toArray();
            $showroles = array_merge($vendorsId, [$user->id]);
        } else {
            $showroles = [$user->id];
        }

        return Product::whereIn('vendor_id', $showroles)
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN status = 0 THEN 1 END) as pending,
                COUNT(CASE WHEN status = 1 THEN 1 END) as approved,
                COUNT(CASE WHEN status = 2 THEN 1 END) as rejected
            ")
            ->first();
    }

    public function bulk_delete_product(Request $request)
    {
        $user = Auth::user();
        $ids = $request->ids;
        if (!empty($ids)) {
            $query = Product::whereIn('id', $ids);


            $products = $query->get();
            foreach ($products as $product) {
                // Get variants
                $variants = ProductVariant::where('product_id', $product->id)->get();

                // Delete variant images
                foreach ($variants as $variant) {
                    $images = json_decode($variant->image, true) ?? [];
                    foreach ($images as $img) {
                        $path = public_path('uploads/products/' . $img);
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                }

                // Delete variants
                ProductVariant::where('product_id', $product->id)->delete();

                // Delete product images if exists
                if (!empty($product->images)) {
                    $productImages = json_decode($product->images, true) ?? [];
                    foreach ($productImages as $img) {
                        $path = public_path('uploads/products/' . $img);
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                }

                // Delete product
                Product::whereIn('id', $ids)->delete();

                $product->delete();
            }

            $statusCounts = $this->getStatusCounts();

            return response()->json([
                'status' => true,
                'message' => 'Selected products deleted successfully',
                'statusCounts' => $statusCounts
            ]);
        }
        return response()->json(['status' => false, 'message' => 'No products selected']);
    }

    public function delete_product(Request $request)
    {
        try {
            $user = Auth::user();
            $productId = $request->id;

            $query = Product::where('id', $productId);

            // Vendor restriction

            $product = $query->first();

            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found or access denied'
                ], 404);
            }

            // Get variants
            $variants = ProductVariant::where('product_id', $productId)->get();

            // Delete variant images
            foreach ($variants as $variant) {
                $images = json_decode($variant->image, true) ?? [];

                foreach ($images as $img) {
                    $path = public_path('uploads/products/' . $img);
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                }
            }

            // Delete variants
            ProductVariant::where('product_id', $productId)->delete();

            // Delete product images if exists
            if (!empty($product->images)) {
                $productImages = json_decode($product->images, true) ?? [];
                foreach ($productImages as $img) {
                    $path = public_path('uploads/products/' . $img);
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                }
            }

            // Delete product
            $product->delete();

            $statusCounts = $this->getStatusCounts();

            return response()->json([
                'status'  => true,
                'message' => 'Product and variants deleted successfully',
                'statusCounts' => $statusCounts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while deleting'
            ], 500);
        }
    }

    public function bulk_product_status(Request $request)
    {
        $user = Auth::user();
        $ids = $request->ids;
        $status = $request->status;

        if (!empty($ids)) {
            $query = Product::whereIn('id', $ids);

            $query->update(['status' => $status]);

            // Notify vendors if admin updated status
            if ($user->role == 1) {
                $products = Product::whereIn('id', $ids)->get();
                $statusText = $status == 1 ? 'Approved' : ($status == 2 ? 'Rejected' : 'Pending');
                foreach ($products as $product) {
                    NotificationHelper::notifyVendor($product->vendor_id, [
                        'title' => 'Product Status Update',
                        'message' => "Your product '{$product->name}' has been {$statusText} via bulk update.",
                        'type' => 'product',
                        'url' => route('product.list'),
                        'icon' => $status == 1 ? 'solar:check-circle-bold-duotone' : 'solar:close-circle-bold-duotone',
                        'priority' => $status == 1 ? 'medium' : 'critical'
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Products status updated successfully',
                'statusCounts' => $this->getStatusCounts()
            ]);
        }
        return response()->json(['status' => false, 'message' => 'No products selected']);
    }

    public function export_products(Request $request)
    {
        $user = Auth::user();

        if ((string)$user->role === '1') {
            $vendorsId = User::where('role', '2')->pluck('id')->toArray();
            $showroles = array_merge($vendorsId, [$user->id]);
        } else {
            $showroles = [$user->id];
        }

        $query = Product::with('variants')->select(
            'products.*',
            'vendor.name as vendor_name',
            'categories.name as category_name',
            'sub_categories.name as subcategory_name',
            'child_categories.name as child_category_name',
            'brands.name as brand_name'
        )
            ->leftJoin('users as vendor', 'vendor.id', 'products.vendor_id')
            ->leftJoin('categories', 'categories.id', 'products.category_id')
            ->leftJoin('sub_categories', 'sub_categories.id', 'products.subcategory_id')
            ->leftJoin('child_categories', 'child_categories.id', 'products.child_category_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->whereIn('products.vendor_id', $showroles)
            ->orderBy('products.id', 'DESC');

        // Handle bulk selection
        if ($request->filled('ids')) {
            $query->whereIn('products.id', $request->ids);
        }

        if ($request->filled('status')) {
            $query->where('products.status', $request->status);
        }

        if ($request->filled('is_active')) {
            $query->where('products.status', $request->is_active);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('products.name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('categories.name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('vendor.name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('brands.name', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('brand_id')) {
            $query->where('products.brand_id', $request->brand_id);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('products.created_at', '>=', $dates[0])
                    ->whereDate('products.created_at', '<=', $dates[1]);
            } else {
                $query->whereDate('products.created_at', $dates[0]);
            }
        }

        $filename = "products_export_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            // Headers including variant details
            fputcsv($file, [
                'Product ID',
                'Product Name',
                'Vendor',
                'Category',
                'Subcategory',
                'Brand',
                'Status',
                'Variant SKU',
                'Variant Color',
                'Variant Stock',
                'Variant Price',
                'Variant Final Price'
            ]);

            $query->chunk(100, function ($products) use ($file) {
                foreach ($products as $product) {
                    $status = 'Pending';
                    if ($product->status == 1) $status = 'Approved';
                    elseif ($product->status == 2) $status = 'Rejected';

                    if ($product->variants->isNotEmpty()) {
                        foreach ($product->variants as $variant) {
                            fputcsv($file, [
                                $product->id,
                                $product->name,
                                $product->vendor_name,
                                $product->category_name,
                                $product->subcategory_name,
                                $product->brand_name,
                                $status,
                                $variant->sku,
                                $variant->color,
                                $variant->stock,
                                $variant->price,
                                $variant->final_price
                            ]);
                        }
                    } else {
                        // Product without variants
                        fputcsv($file, [
                            $product->id,
                            $product->name,
                            $product->vendor_name,
                            $product->category_name,
                            $product->subcategory_name,
                            $product->brand_name,
                            $status,
                            'N/A',
                            'N/A',
                            '0',
                            '0',
                            '0'
                        ]);
                    }
                }
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    // product size
    public function add_product_size_category(Request $request)
    {

        $sizeType = ProductSizeCategory::select(

            'product_size_category.id',
            'product_size_category.name as category_name',
            'product_size_category.status',
            'product_sizes.name as size_name'
        )
            ->leftJoin('product_sizes', 'product_size_category.id', '=', 'product_sizes.size_cat_id')
            ->get()
            ->groupBy('category_name');
        // echo '<pre>';print_r($sizeType);die;
        return view('backend/admin/product/add-product-size-category', compact('sizeType'));
    }


    public function store_product_size_category(Request $request)
    {

        $input = $request->all();
        $request->validate([
            'name'        => 'required',

        ]);

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('ne');

        $data = [
            'name'         => strtoupper($input['name']),
            'name_ar'      => $trAr->translate($input['name']),
            'name_ne'      => $trNe->translate($input['name']),
            'status'       => $input['status'] ?? 1,
        ];

        $ProductSizeType =   ProductSizeCategory::create($data);
        // print_r($ProductSizeType);die;
        return redirect('add-product-size-category')
            ->with('status', 'success')
            ->with('message', 'Sizes added successfully');
    }


    public function edit_product_size_category(Request $request, $id)
    {
        $sizeType = ProductSizeCategory::select('*')->where('id', $id)->first();
        $allsize = ProductSize::select('*')->where('size_cat_id', $id)->get();

        return view('backend/admin/product/edit-product-size-category', compact('sizeType', 'allsize'));
    }



    public function update_product_size_category(Request $request)
    {
        // ✅ Validate FIRST
        $request->validate([
            'size_id' => 'required|exists:product_size_category,id',
            'name'    => 'required|string|max:100',
        ]);

        // ✅ Fetch ID safely
        $size_id = $request->input('size_id');

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('ne');

        // ✅ Find record
        $data  = [
            'name'   => strtoupper(trim($request->name)),
            'name_ar' => $trAr->translate($request->name),
            'name_ne' => $trNe->translate($request->name),
        ];

        if ($request->has('status')) {
            $data['status'] = $request->status;
        }

        $productSize = ProductSizeCategory::where('id', $size_id)->update($data);



        return redirect('add-product-size-category')->with('success', 'Product size updated successfully.');
    }


    public function change_product_size_category_status(Request $request)
    {
        $category = ProductSizeCategory::find($request->id);
        if ($category) {
            $category->status = $request->status;
            $category->save();
            return response()->json(['status' => true, 'message' => 'Status updated successfully']);
        }
        return response()->json(['status' => false, 'message' => 'Product size category not found']);
    }

    public function change_product_size_status(Request $request)
    {
        $size = ProductSize::find($request->id);
        if ($size) {
            $size->status = $request->status;
            $size->save();
            return response()->json(['status' => true, 'message' => 'Status updated successfully']);
        }
        return response()->json(['status' => false, 'message' => 'Product size not found']);
    }

    public function delete_product_size_category(Request $request)
    {
        try {
            $id = $request->id;

            // Delete associated sizes first to avoid foreign key constraints or orphaned records
            ProductSize::where('size_cat_id', $id)->delete();

            $sizedelete = ProductSizeCategory::findOrFail($id);
            $sizedelete->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Product size category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while deleting'
            ], 500);
        }
    }

    // size

    public function add_product_size(Request $request, $id)
    {

        $allsize = ProductSize::select('*')->where('size_cat_id', $id)->get();
        $sizecategory = ProductSizeCategory::select('*')->where('id', $id)->first();
        return view('backend/admin/product/add-product-size', compact('sizecategory', 'allsize'));
    }


    public function store_product_size(Request $request)
    {

        $input = $request->all();
        $request->validate([
            'name'        => 'required',

        ]);

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('ne');

        $data = [
            'name'         => strtoupper($input['name']),
            'name_ar'      => $trAr->translate($input['name']),
            'name_ne'      => $trNe->translate($input['name']),
            'status'       => $input['status'] ?? 1,
            'size_cat_id'   => $input['size_cat_id'],
        ];

        $ProductSizeType =   ProductSize::create($data);
        // print_r($ProductSizeType);die;
        return redirect('add-product-size-category')
            ->with('status', 'success')
            ->with('message', 'Sizes added successfully');
    }


    public function edit_product_size(Request $request, $id)
    {
        $sizes = ProductSize::select('product_sizes.*', 'product_size_category.name as product_size_category_name')
            ->leftJoin('product_size_category', 'product_size_category.id', 'product_sizes.size_cat_id')
            ->where('product_sizes.id', $id)
            ->first();

        return view('backend/admin/product/edit-product-size', compact('sizes'));
    }

    public function update_product_size(Request $request)
    {
        $size_id = $request->size_id;
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('ne');

        $productSize = ProductSize::findOrFail($size_id);
        $productSize->name = $request->name;
        $productSize->name_ar = $trAr->translate($request->name);
        $productSize->name_ne = $trNe->translate($request->name);
        $productSize->status = $request->status;
        $productSize->save();

        return redirect('edit-product-size-category/'.$productSize->size_cat_id)->with('success', 'Product size updated successfully.');
    }

    public function delete_product_size(Request $request)
    {
        try {
            $id = $request->id;
            $sizedelete = ProductSize::findOrFail($id);
            $sizedelete->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Product size deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while deleting'
            ], 500);
        }
    }


    public function get_sizes($categoryId)
    {
        $sizes = ProductSize::where('size_cat_id', $categoryId)
            ->where('product_sizes.status', 1)
            ->join('product_size_category', 'product_size_category.id', '=', 'product_sizes.size_cat_id')
            ->where('product_size_category.status', 1)
            ->orderBy('product_sizes.name')
            ->get(['product_sizes.id', 'product_sizes.name']);

        return response()->json($sizes);
    }


    public function find_similar_product(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login');
            }

            if ($user->role == '2') {
                $allowedCats = (array)($user->category_ids ?? []);
                $productCats = Product::where('vendor_id', $user->id)->distinct()->pluck('category_id')->toArray();
                $mergedCats = array_unique(array_merge($allowedCats, $productCats));
                $mergedCats = array_filter($mergedCats);

                if (!empty($mergedCats)) {
                    $categories_data = Category::select('*')->where('is_active', 1)->whereIn('id', $mergedCats)->get();
                } else {
                    $categories_data = collect();
                }
            } else {
                $categories_data = Category::select('*')->where('is_active', 1)->get();
            }
            $subcategories = SubCategory::select('*')->where('is_active', 1)->get();
            $childcategory = ChildCategory::select('*')->where('is_active', 1)->get();
            $brand = Brand::select('*')->where('is_active', 1)->get();
            $sizecategory = ProductSizeCategory::select('*')->where('status', 1)->get();

            return view('backend/admin/product/find-similar-product', compact('categories_data', 'subcategories', 'childcategory', 'brand', 'sizecategory'));
        } catch (\Exception $e) {
            Log::error("Error in find_similar_product: " . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Error opening find similar product: ' . $e->getMessage());
        }
    }

    public function ajax_find_similar(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $query = Product::query()
                ->select(
                    'products.id',
                    'products.name',
                    'products.category_id',
                    'products.subcategory_id',
                    'products.child_category_id',
                    'products.brand_id',
                    'categories.name as category_name',
                    'sub_categories.name as subcategory_name',
                    'child_categories.name as child_category_name',
                    'brands.name as brand_name'
                )
                ->leftJoin('categories', 'categories.id', 'products.category_id')
                ->leftJoin('sub_categories', 'sub_categories.id', 'products.subcategory_id')
                ->leftJoin('child_categories', 'child_categories.id', 'products.child_category_id')
                ->leftJoin('brands', 'brands.id', 'products.brand_id')
                ->where('products.status', 1); // Only show active/approved products

            if ($user->role === '2') {
                $query->where('products.vendor_id', '!=', $user->id);
        
                // Get categories assigned to the vendor
                $allowedCats = (array) ($user->category_ids ?? []);
                
                // Also get categories from vendor's existing products
                $productCats = Product::where('vendor_id', $user->id)->distinct()->pluck('category_id')->toArray();
                
                $mergedCats = array_unique(array_merge($allowedCats, $productCats));
                $mergedCats = array_filter($mergedCats);

                if (!empty($mergedCats)) {
                    $query->whereIn('products.category_id', $mergedCats);
                } else {
                    // If no categories assigned and no products, show nothing
                    return response()->json(['success' => true, 'data' => []]);
                }
            }

            if ($request->filled('q')) {
                $term = $request->input('q');
                $query->where('products.name', 'like', "%{$term}%");
            }

            if ($request->filled('category_id')) {
                $query->where('products.category_id', $request->category_id);
            }

            if ($request->filled('subcategory_id')) {
                $query->where('products.subcategory_id', $request->subcategory_id);
            }

            if ($request->filled('child_category_id')) {
                $query->where('products.child_category_id', $request->child_category_id);
            }

            if ($request->filled('brand_id')) {
                $query->where('products.brand_id', $request->brand_id);
            }

            $results = $query->orderBy('products.id', 'desc')->limit(50)->get();

            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // AJAX: fetch a single product record by id and return details
    public function ajax_fetch_product(Request $request)
    {
        try {
            $request->validate(['id' => 'required|integer|exists:products,id']);

            $id = $request->input('id');

            // fetch product with category/brand names
            $product = Product::select(
                'products.id',
                'products.name',
                'products.slug',
                'products.category_id',
                'products.subcategory_id',
                'products.child_category_id',
                'products.brand_id',
                'products.short_description',
                'products.description',
                'categories.name as category_name',
                'sub_categories.name as subcategory_name',
                'child_categories.name as child_category_name',
                'brands.name as brand_name'
            )
                ->leftJoin('categories', 'categories.id', 'products.category_id')
                ->leftJoin('sub_categories', 'sub_categories.id', 'products.subcategory_id')
                ->leftJoin('child_categories', 'child_categories.id', 'products.child_category_id')
                ->leftJoin('brands', 'brands.id', 'products.brand_id')
                ->where('products.id', $id)
                ->first();

            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Product not found'], 404);
            }

            // fetch variants and enrich with decoded images and sizes list
            $variant = ProductVariant::where('product_id', $id)->get()->map(function ($v) {
                $v->images = json_decode($v->image, true) ?? [];
                $sizeIds = json_decode($v->size, true) ?? [];
                $v->sizes_list = ProductSize::whereIn('product_sizes.id', $sizeIds)
                    ->where('product_sizes.status', 1)
                    ->join('product_size_category', 'product_size_category.id', '=', 'product_sizes.size_cat_id')
                    ->where('product_size_category.status', 1)
                    ->get(['product_sizes.*']);
                return $v;
            });

            $sizecategory = ProductSizeCategory::select('*')->where('status', 1)->get();


            $categories = Category::select('*')->where('is_active', 1)->get();
            $subcategories = SubCategory::select('*')->where('is_active', 1)->get();
            $childcategory = ChildCategory::select('*')->where('is_active', 1)->get();
            $brand = Brand::select('*')->where('is_active', 1)->get();


            return view('backend/admin/product/fetch-product-data', compact('product', 'variant', 'sizecategory', 'categories', 'subcategories', 'childcategory', 'brand'));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Return rendered blade partial for the selected product
    public function ajax_render_product(Request $request)
    {
        try {
            $request->validate(['id' => 'required|integer|exists:products,id']);
            $id = $request->input('id');

            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            if ($user->role == '2') {
                $allowedCats = (array)($user->category_ids ?? []);
                $productCats = Product::where('vendor_id', $user->id)->distinct()->pluck('category_id')->toArray();
                $mergedCats = array_unique(array_merge($allowedCats, $productCats));
                $mergedCats = array_filter($mergedCats);

                if (!empty($mergedCats)) {
                    $categories_data = Category::select('*')->where('is_active', 1)->whereIn('id', $mergedCats)->get();
                } else {
                    $categories_data = collect();
                }
            } else {
                $categories_data = Category::select('*')->where('is_active', 1)->get();
            }

            $subcategories = SubCategory::select('*')->where('is_active', 1)->get();
            $childcategory = ChildCategory::select('*')->where('is_active', 1)->get();
            $brand = Brand::select('*')->where('is_active', 1)->get();
            $offers = Offer::where('status', 1)->get();
            $product = Product::select(
                'products.id',
                'products.name',
                'products.slug',
                'products.category_id',
                'products.subcategory_id',
                'products.child_category_id',
                'products.brand_id',
                'products.vendor_id',
                'products.short_description',
                'products.description',
                'categories.name as category_name',
                'sub_categories.name as subcategory_name',
                'child_categories.name as child_category_name',
                'brands.name as brand_name'
            )
                ->leftJoin('categories', 'categories.id', 'products.category_id')
                ->leftJoin('sub_categories', 'sub_categories.id', 'products.subcategory_id')
                ->leftJoin('child_categories', 'child_categories.id', 'products.child_category_id')
                ->leftJoin('brands', 'brands.id', 'products.brand_id')
                ->where('products.id', $id)
                ->first();

            if ($user->role == '2') {
              

                // Get allowed categories (assigned and existing products)
                $allowedCats = (array)($user->category_ids ?? []);
                $productCats = Product::where('vendor_id', $user->id)->distinct()->pluck('category_id')->toArray();
                $mergedCats = array_unique(array_merge($allowedCats, $productCats));
                $mergedCats = array_filter($mergedCats);

                $isCategoryAllowed = in_array($product->category_id, $mergedCats);
             
            }

            $variant = ProductVariant::where('product_id', $id)->get()->map(function ($v) {
                $v->images = json_decode($v->image, true) ?? [];
                $sizeIds = json_decode($v->size, true) ?? [];
                $v->sizes_list = ProductSize::whereIn('product_sizes.id', $sizeIds)
                    ->where('product_sizes.status', 1)
                    ->join('product_size_category', 'product_size_category.id', '=', 'product_sizes.size_cat_id')
                    ->where('product_size_category.status', 1)
                    ->get(['product_sizes.*']);
                return $v;
            });

            $sizecategory = ProductSizeCategory::select('*')->where('status', 1)->get();
            $product_variant_labels = DB::table('product_variant_labels')->get();

            $html = view('backend.admin.product.partials.similar-product-detail', compact(
                'product',
                'variant',
                'sizecategory',
                'categories_data',
                'subcategories',
                'childcategory',
                'brand',
                'offers',
                'product_variant_labels'
            ))->render();

            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to render product details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generateUniqueSlug($name)
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $count = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function bulk_upload_product(Request $request)
    {
        $user = Auth::user();
        $query = Product::with(['variants' => function ($q) {
            $q->orderBy('id')->limit(1);
        }])->where('is_upload', 1);

        if ($user->role == 2) {
            $query->where('vendor_id', $user->id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('is_active')) {
            $query->where('status', $request->is_active);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                    ->whereDate('created_at', '<=', $dates[1]);
            }
        }

        $products = $query->orderBy('id', 'DESC')->get();

        if ($user->role == 2) {
            $vendors = User::where('id', $user->id)->get();
        } else {
            $vendors = User::whereIn('role', ['1', '2'])->get()->map(function($vendor) {
                if ($vendor->role == '1') {
                    $vendor->store_name = 'Nepoora';
                }
                return $vendor;
            });
        }

        if (Auth::user()->role == '2') {
            $categories_data = Category::select('*')->where('is_active', 1)->whereIn('id', Auth::user()->category_ids)->get();
        } else {
            $categories_data = Category::select('*')->where('is_active', 1)->get();
        }
        $subcategories = SubCategory::select('*')->where('is_active', 1)->get();
        $childcategory = ChildCategory::select('*')->where('is_active', 1)->get();
        $brand = Brand::select('*')->where('is_active', 1)->get();
        $sizecategory = ProductSizeCategory::select('*')->where('status', 1)->get();
        $offers = Offer::where('status', 1)->get();


        return view('backend/admin/product/bulk-upload-product', compact('products', 'categories_data', 'subcategories', 'childcategory', 'brand', 'sizecategory', 'offers', 'vendors'));
    }

    public function export_bulk_products(Request $request)
    {
        $user = Auth::user();
        $query = Product::select(
            'products.*',
            'vendor.name as vendor_name',
            'categories.name as category_name',
            'sub_categories.name as subcategory_name',
            'child_categories.name as child_category_name',
            'brands.name as brand_name'
        )
            ->leftJoin('users as vendor', 'vendor.id', 'products.vendor_id')
            ->leftJoin('categories', 'categories.id', 'products.category_id')
            ->leftJoin('sub_categories', 'sub_categories.id', 'products.subcategory_id')
            ->leftJoin('child_categories', 'child_categories.id', 'products.child_category_id')
            ->leftJoin('brands', 'brands.id', 'products.brand_id')
            ->where('products.is_upload', 1)
            ->orderBy('products.id', 'DESC');

        if ($user->role == 2) {
            $query->where('products.vendor_id', $user->id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('products.vendor_id', $request->vendor_id);
        }

        if ($request->filled('ids')) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $query->whereIn('products.id', $ids);
        }

        if ($request->filled('is_active')) {
            $query->where('products.status', $request->is_active);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('products.created_at', '>=', $dates[0])
                    ->whereDate('products.created_at', '<=', $dates[1]);
            } else {
                $query->whereDate('products.created_at', $dates[0]);
            }
        }

        $filename = "bulk_products_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Product Name', 'Vendor', 'Category', 'Subcategory', 'Brand', 'Status', 'Created At']);

            $query->chunk(100, function ($products) use ($file) {
                foreach ($products as $product) {
                    $status = 'Pending';
                    if ($product->status == 1) $status = 'Approved';
                    elseif ($product->status == 2) $status = 'Rejected';
                    elseif ($product->status == 3) $status = 'Re-added';

                    fputcsv($file, [
                        $product->id,
                        $product->name,
                        $product->vendor_name,
                        $product->category_name,
                        $product->subcategory_name,
                        $product->brand_name,
                        $status,
                        $product->created_at
                    ]);
                }
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function download_bulk_upload_product_csv()
    {
        $headers = [
            'Product Name',
            'Short Description',
            'Description',
            'SKU',
            'Stock',
            'Color',
            'Material',
            'Price',
            'Discount Type',
            'Discount Value',

        ];

        $filename = 'bulk_upload_products_template.csv';

        return response()->streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }



    public function store_bulk_upload_product(Request $request)
    {
        ini_set('max_execution_time', 600); // 10 minutes
        ini_set('memory_limit', '512M');

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
            'category_id' => 'nullable',
            'subcategory_id' => 'nullable',
            'child_category_id' => 'nullable',
        ]);

        $mandatoryHeaders = [
            'product name',
            'sku',
            'color',
            'price',
            'stock',
        ];

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->with('error', 'Unable to read CSV file.');
        }

        /* ===== READ HEADER ===== */
        $headerRow = fgetcsv($handle);
        $headerRow[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headerRow[0]);

        $headers = array_map(fn($h) => strtolower(trim($h)), $headerRow);

        $missing = array_diff($mandatoryHeaders, $headers);
        if (!empty($missing)) {
            fclose($handle);
            return back()->with('error', 'Missing mandatory columns: ' . implode(', ', $missing));
        }

        $vendorId = Auth::user()->id;

        // Capture form defaults
        $formCategoryId = $request->category_id;
        $formSubcategoryId = $request->subcategory_id;
        $formChildCategoryId = $request->child_category_id;
        $formBrandId = $request->brand_id;
        $formOffers = $request->has('offers') ? json_encode($request->offers) : null;
        $formProductIn = $request->has('product_in') ? json_encode($request->product_in) : null;
        $formIsFeatured = $request->is_featured ?? 0;

        $createdProducts = 0;
        $createdVariants = 0;
        $errors = [];
        $rowNumber = 1;

        $trAr = new GoogleTranslate('ar');
        $trNe = new GoogleTranslate('ne');

        /* ===== PROCESS ROWS ===== */
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            $row = array_pad($row, count($headers), null);
            $data = array_combine($headers, $row);

            $productName = trim($data['product name'] ?? '');
            $shortDesc   = trim($data['short description'] ?? '');
            $description = trim($data['description'] ?? '');
            $sku         = trim($data['sku'] ?? '');
            $color       = trim($data['color'] ?? '');
            $material    = trim($data['material'] ?? '');
            $stock       = (int) ($data['stock'] ?? 0);
            $price       = (float) ($data['price'] ?? 0);
            $discountType = trim($data['discount type'] ?? '');
            $discountValue = (float) ($data['discount value'] ?? 0);

            // Merge CSV data with form defaults
            $categoryId    = !empty($data['category id']) ? (int)$data['category id'] : $formCategoryId;
            $subcategoryId = !empty($data['subcategory id']) ? (int)$data['subcategory id'] : $formSubcategoryId;
            $childCategoryId = !empty($data['child category id']) ? (int)$data['child category id'] : $formChildCategoryId;
            $brandId       = !empty($data['brand id']) ? (int)$data['brand id'] : $formBrandId;
            $offersRaw    = trim($data['offers'] ?? '');
            $offers       = !empty($offersRaw) ? json_encode(explode(',', $offersRaw)) : $formOffers;

            $productInRaw  = trim($data['product in'] ?? '');
            $productIn     = !empty($productInRaw) ? json_encode(explode(',', $productInRaw)) : $formProductIn;

            $isFeatured    = (isset($data['is featured']) && $data['is featured'] !== '') ? (int)$data['is featured'] : $formIsFeatured;

            if (!$productName || !$sku || !$color || !$price) {
                $errors[] = "Row {$rowNumber}: Required fields missing (Product Name, SKU, Color, or Price).";
                continue;
            }

            if (!$categoryId) {
                $errors[] = "Row {$rowNumber}: Category ID is required (must be in CSV or selected in form).";
                continue;
            }

            /* ----- PRICE CALCULATION ----- */
            $finalPrice = $price;
            if ($discountType === '%') {
                $finalPrice = $price - (($price * $discountValue) / 100);
            } elseif ($discountType === 'off') {
                $finalPrice = $price - $discountValue;
            }
            $finalPrice = max(0, $finalPrice);

            $slugBase = Str::slug($productName);

            try {
                DB::transaction(function () use (
                    $vendorId,
                    $productName,
                    $slugBase,
                    $shortDesc,
                    $description,
                    $sku,
                    $color,
                    $material,
                    $stock,
                    $price,
                    $discountType,
                    $discountValue,
                    $finalPrice,
                    $categoryId,
                    $subcategoryId,
                    $childCategoryId,
                    $brandId,
                    $offers,
                    $productIn,
                    $isFeatured,
                    &$createdProducts,
                    &$createdVariants,
                    $trAr,
                    $trNe
                ) {
                    /* ===== CREATE / GET PRODUCT ===== */
                    $product = Product::where('slug', $slugBase)
                        ->where('vendor_id', $vendorId)
                        ->first();

                    if (!$product) {
                        $productData = [
                            'name'              => $productName,
                            'name_ar'           => $trAr->translate($productName),
                            'name_ne'           => $trNe->translate($productName),
                            'slug'              => SlugHelper::uniqueProductSlug($productName),
                            'slug_ar'           => Str::slug($productName),
                            'slug_ne'           => Str::slug($productName),
                            'category_id'       => $categoryId,
                            'subcategory_id'    => $subcategoryId,
                            'child_category_id' => $childCategoryId,
                            'brand_id'          => $brandId,
                            'offer_id'          => $offers,
                            'product_in'        => $productIn,
                            'is_featured'       => $isFeatured,
                            'short_description' => $shortDesc,
                            'short_description_ar' => $shortDesc ? $trAr->translate($shortDesc) : null,
                            'short_description_ne' => $shortDesc ? $trNe->translate($shortDesc) : null,
                            'description'       => $description,
                            'description_ar'    => $description ? $trAr->translate($description) : null,
                            'description_ne'    => $description ? $trNe->translate($description) : null,
                            'vendor_id'         => $vendorId,
                            'status'            => 0,
                            'is_upload'            => 1,
                        ];

                        $product = Product::create($productData);
                        $createdProducts++;
                    }

                    /* ===== CREATE VARIANT ===== */
                    ProductVariant::create([
                        'product_id'     => $product->id,
                        'sku'            => $sku,
                        'color'          => $color,
                        'color_ar'       => $color ? $trAr->translate($color) : null,
                        'color_ne'       => $color ? $trNe->translate($color) : null,
                        'material'       => $material,
                        'material_ar'    => $material ? $trAr->translate($material) : null,
                        'material_ne'    => $material ? $trNe->translate($material) : null,
                        'stock'          => $stock,
                        'price'          => $price,
                        'discount_type'  => $discountType,
                        'discount_value' => $discountValue,
                        'final_price'    => $finalPrice,
                        'vendor_id'      => $vendorId,
                        'image'          => json_encode([]),
                    ]);

                    $createdVariants++;
                });
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return back()->with([
            'success' => "Imported successfully",
            'bulk_upload_errors' => $errors,
        ]);
    }

    public function create_product_variant_ajax(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        
        $variant = ProductVariantLabel::create([
            'name' => $request->name,
            'status' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product variant created successfully',
            'variant' => $variant
        ]);
    }

    public function create_size_category_ajax(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        
        $category = \App\Models\ProductSizeCategory::create([
            'name' => $request->name,
            'status' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Size category created successfully',
            'category' => $category
        ]);
    }

    public function create_size_ajax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:product_size_category,id'
        ]);
        
        $size = \App\Models\ProductSize::create([
            'name' => $request->name,
            'product_size_category_id' => $request->category_id,
            'status' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Size created successfully',
            'size' => $size
        ]);
    }
}
