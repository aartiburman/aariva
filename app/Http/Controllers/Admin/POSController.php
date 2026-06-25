<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Helpers\ImageHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\GeneralSetting;



class POSController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', 1)->get();
        $currencySymbol = GeneralSetting::where('key', 'currency_symbol')->value('value') ?? 'NPR';
        return view('backend.admin.pos.index', compact('categories', 'currencySymbol'));
    }

    public function orderHistory()
    {
        $query = Order::query()->where('order_reference_id', 'LIKE', 'POS-%');

        if (Auth::user()->role == 2) {
            $query->where('user_id', Auth::user()->id);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();
        $currencySymbol = GeneralSetting::where('key', 'currency_symbol')->value('value') ?? 'NPR';
        return view('backend.admin.pos.history', compact('orders', 'currencySymbol'));
    }

    public function searchProducts(Request $request)
    {
        $query = Product::with(['variants'])
            ->where('status', 1);

        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        if (Auth::user()->role == 2) {
            $query->where('vendor_id', Auth::user()->id);
        }

        $products = $query->latest()->paginate(12)->withQueryString();

        // Map product thumbnails using ImageHelper, prioritizing first variant image
        $products->getCollection()->transform(function ($product) {
            $variantImage = null;
            if ($product->variants->isNotEmpty()) {
                $firstVariant = $product->variants->first();
                $images = json_decode($firstVariant->image, true);
                if (!empty($images) && is_array($images)) {
                    $variantImage = $images[0];
                }
            }
            
            // Use variant image if found, else fall back to product thumbnail
            $displayImage = $variantImage ?: $product->thumbnail;
            $product->thumbnail_url = ImageHelper::getProductImage($displayImage);
            
            return $product;
        });

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    public function getProductDetails($id)
    {
        $product = Product::with(['variants', 'category', 'brand'])->find($id);
        
        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Product not found']);
        }

        // Add formatted thumbnail, prioritizing first variant image
        $variantImage = null;
        if ($product->variants->isNotEmpty()) {
            $firstVariant = $product->variants->first();
            $images = json_decode($firstVariant->image, true);
            if (!empty($images) && is_array($images)) {
                $variantImage = $images[0];
            }
        }
        
        $displayImage = $variantImage ?: $product->thumbnail;
        $product->thumbnail_url = ImageHelper::getProductImage($displayImage);

        return response()->json([
            'status' => true,
            'data' => $product
        ]);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'cart' => 'required|array',
            'payment_method' => 'required|string|in:cash,card,online',
        ]);

        $cart = $request->cart;
        $totalCost = 0;
        $totalSavings = 0;
        foreach ($cart as $item) {
            $totalCost += $item['price'] * $item['qty'];
            
            // Calculate savings if variant is found
            $variant = ProductVariant::find($item['variant_id']);
            if ($variant && $variant->price > $item['price']) {
                $totalSavings += ($variant->price - $item['price']) * $item['qty'];
            }
        }

        try {
            DB::beginTransaction();

            // Create Order
            $order = Order::create([
                'order_reference_id' => 'POS-' . strtoupper(Str::random(10)),
                'user_id' => Auth::user()->id, // Assuming admin/vendor is the "user" for POS
                'sub_total' => $totalCost + $totalSavings,
                'total_discount' => $totalSavings,
                'total_cost' => $totalCost,
                'payment_mode' => $request->payment_method,
                'payment_status' => '1', // All POS orders are marked as Paid
                'order_status' => '3', // Use numeric status 3 for Completed/Delivered
                'order_date' => now(),
            ]);

            // Create Order Items
            foreach ($cart as $item) {
                $product = Product::find($item['product_id']);
                $variant = ProductVariant::find($item['variant_id']);
                
                // Original price from variant
                $originalPrice = $variant ? $variant->price : $item['price'];
                $sellingPrice = $item['price'];
                $itemSavings = ($originalPrice > $sellingPrice) ? ($originalPrice - $sellingPrice) * $item['qty'] : 0;

                OrderItem::create([
                    'order_id' => $order->id,
                    'vendor_id' => $product ? $product->vendor_id : null,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'price' => $originalPrice,
                    'actual_price' => $sellingPrice,
                    'quantity' => $item['qty'],
                    'total_actual_price' => $sellingPrice * $item['qty'],
                    'discount' => ($originalPrice > $sellingPrice) ? ($originalPrice - $sellingPrice) : 0,
                    'payment_mode' => $request->payment_method,
                    'payment_status' => 1,
                    'status' => 3, // Delivered/Completed
                ]);

                // ✅ STOCK REDUCTION for POS
                if ($variant && $variant->stock >= $item['qty']) {
                    $variant->decrement('stock', $item['qty']);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error placing order: ' . $e->getMessage()
            ]);
        }
    }

    public function generateInvoice($id)
    {
        $order = Order::with(['user', 'items.product', 'items.variant'])->findOrFail($id);
        
        // Default values
        $websiteName = "";
        $contactEmail = null;
        $contactPhone = null;
        $defaultCurrency = null;
        $timezone = null;
        $address = null;
        $websiteLogo = null;
        $favicon = null;

        if(Auth::user()->role == 1) {
            $websiteName = GeneralSetting::where('key', 'company_name')->first()->value ?? GeneralSetting::where('key', 'website_name')->first()->value ?? "";
            $contactEmail = GeneralSetting::where('key', 'customer_support_email')->first() ?? GeneralSetting::where('key', 'contact_email')->first();
            $contactPhone = GeneralSetting::where('key', 'contact_phone')->first();
            $defaultCurrency = GeneralSetting::where('key', 'default_currency')->first();
            $timezone = GeneralSetting::where('key', 'timezone')->first();
            $address = GeneralSetting::where('key', 'registered_office')->first() ?? GeneralSetting::where('key', 'address')->first();
            $logoValue = GeneralSetting::where('key', 'website_logo_dark')->first()->value ?? null;
            $websiteLogo = (object)['value' => ImageHelper::getWebsiteLogo($logoValue)];
            $favicon = GeneralSetting::where('key', 'favicon')->first();
        } else {
            // For vendors, use their own shop info if available, else fall back to vendor settings
            $vendor = Auth::user();
            $websiteName = $vendor->store_name ?? "";
            $contactEmail = (object)['value' => $vendor->email ?? ''];
            $contactPhone = (object)['value' => $vendor->phone ?? ''];
            $defaultCurrency = GeneralSetting::where('key', 'vendor_default_currency')->first();
            $timezone = GeneralSetting::where('key', 'vendor_timezone')->first();
            $address = (object)['value' => $vendor->address ?? GeneralSetting::where('key', 'vendor_address')->first()->value ?? ''];
            
            // Handle logo path properly for vendors vs settings
            $logoValue = $vendor->image ;
            $logoValue =  ImageHelper::getVendorsImage($logoValue);
            // echo $logoValue;die;
            $websiteLogo = (object)['value' => $logoValue];
           
            
            $favicon = GeneralSetting::where('key', 'vendor_favicon')->first();
        }

        $currencySymbol = GeneralSetting::where('key', 'currency_symbol')->value('value') ?? 'NPR';

        return view('backend.admin.pos.invoice', compact('order', 'websiteName', 'contactEmail', 'contactPhone', 'defaultCurrency', 'timezone', 'address', 'websiteLogo', 'favicon', 'currencySymbol'));
    }

    public function showPaymentPage($order_reference_id)
    {
        $order = Order::where('order_reference_id', $order_reference_id)->firstOrFail();
        $currencySymbol = GeneralSetting::where('key', 'currency_symbol')->value('value') ?? 'NPR';
        return view('backend.admin.pos.payment', compact('order', 'currencySymbol'));
    }
}
