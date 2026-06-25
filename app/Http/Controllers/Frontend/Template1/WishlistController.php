<?php

namespace App\Http\Controllers\Frontend\Template1;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;
use App\Helpers\GeneralHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::check() ? Auth::id() : null;
        $ipAddress = $request->ip();

        $wishlistItems = Wishlist::with('product')
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($ipAddress) {
                $q->where('ip_address', $ipAddress);
            })
            ->latest()
            ->get();

        return view('frontend.wishlist.index', compact('wishlistItems'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $userId = Auth::check() ? Auth::id() : null;
        $ipAddress = $request->ip();

        $product = Product::with('variants')->find($request->product_id);

        if (!$product || $product->variants->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'Product not found',
            ], 404);
        }

        $existing = Wishlist::where('product_id', $product->id)
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }, function ($q) use ($ipAddress) {
                $q->where('ip_address', $ipAddress);
            })
            ->first();

        if ($existing) {
            $existing->delete();
            $message = 'Removed from wishlist';
            $is_in_wishlist = false;
        } else {
            $variant = $product->variants->first();

            $img = null;
            if (!empty($variant->image)) {
                $images = is_array(json_decode($variant->image, true))
                    ? json_decode($variant->image, true)
                    : explode(',', $variant->image);
                $img = trim($images[0] ?? null);
            }

            Wishlist::create([
                'user_id'    => $userId,
                'ip_address' => $ipAddress ?? '',
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'qty'        => 1,
                'price'      => $variant->price,
                'image'      => $img,
            ]);

            $message = 'Added to wishlist';
            $is_in_wishlist = true;
        }

        return response()->json([
            'status'         => true,
            'message'        => $message,
            'is_in_wishlist' => $is_in_wishlist,
        ]);
    }

    public function count()
    {
        $userId = Auth::check() ? Auth::id() : null;
        $ipAddress = request()->ip();

        $count = Wishlist::when($userId, function ($q) use ($userId) {
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
