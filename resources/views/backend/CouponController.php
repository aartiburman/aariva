<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

class CouponController extends Controller
{
   public function index()
{
    $today = now();
    $coupons = Coupon::where('status', 1)
      
        ->orderBy('id', 'desc')
        ->get();

    return view('backend.admin.coupons.coupon-list', compact('coupons'));
}
    public function create()
    {
        $categories = Category::where('is_active', 1)->get();
        $products = Product::where('status', 1)->get();
        $vendors = User::where('role', '2')->where('status', 1)->get();
        return view('backend.admin.coupons.add-coupon', compact('categories', 'products', 'vendors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|in:0,1',
            'value' => 'required|numeric|min:0',
            'status' => 'required|in:1,0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'max_uses' => 'nullable|integer|min:1',

            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',

            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',

            'vendor_ids' => 'nullable|array',
            'vendor_ids.*' => 'exists:users,id',
        ]);

        $coupon = Coupon::create([
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'status' => $request->status,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'max_uses' => $request->max_uses,
            'category_ids' => $request->category_ids ? json_encode($request->category_ids) : json_encode([]),
            'product_ids' => $request->product_ids ? json_encode($request->product_ids) : json_encode([]),
            'vendor_ids' => $request->vendor_ids ? json_encode($request->vendor_ids) : json_encode([]),
        ]);

        if ($request->has('category_ids')) {
            $coupon->categories()->sync($request->category_ids);
        }
        if ($request->has('product_ids')) {
            $coupon->products()->sync($request->product_ids);
        }
        if ($request->has('vendor_ids')) {
            $coupon->vendors()->sync($request->vendor_ids);
        }

        return redirect()->route('coupons.list')->with('success', 'Coupon created successfully');
    }

    public function edit($id)
    {
        $coupon = Coupon::with(['categories', 'products', 'vendors'])->findOrFail($id);
        $categories = Category::where('is_active', 1)->get();
        $products = Product::where('status', 1)->get();
        $vendors = User::where('role', 2)->where('status', 1)->get();
        return view('backend.admin.coupons.edit-coupon', compact('coupon', 'categories', 'products', 'vendors'));
    }

    public function update(Request $request)
    {
        $coupon = Coupon::findOrFail($request->id);

        $request->validate([
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:0,1',
            'value' => 'required|numeric|min:0',
            'status' => 'required|in:1,0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'max_uses' => 'nullable|integer|min:1',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'vendor_ids' => 'nullable|array',
            'vendor_ids.*' => 'exists:users,id',
        ]);

        $coupon->update([
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'status' => $request->status,
            'valid_from' => $request->valid_from,
            'valid_until' => $request->valid_until,
            'max_uses' => $request->max_uses,
            'category_ids' => $request->category_ids ? json_encode($request->category_ids) : json_encode([]),
            'product_ids' => $request->product_ids ? json_encode($request->product_ids) : json_encode([]),
            'vendor_ids' => $request->vendor_ids ? json_encode($request->vendor_ids) : json_encode([]),
        ]);

        // Sync Categories
        if ($request->filled('category_ids')) {
            $coupon->categories()->sync($request->category_ids);
        } else {
            $coupon->categories()->detach();
        }

        // Sync Products
        if ($request->filled('product_ids')) {
            $coupon->products()->sync($request->product_ids);
        } else {
            $coupon->products()->detach();
        }

        // Sync Vendors
        if ($request->filled('vendor_ids')) {
            $coupon->vendors()->sync($request->vendor_ids);
        } else {
            $coupon->vendors()->detach();
        }

        return redirect()->route('coupons.list')->with('success', 'Coupon updated successfully');
    }

    public function delete(Request $request)
    {
        $coupon = Coupon::findOrFail($request->id);
        $coupon->delete();
        return response()->json(['status' => true, 'message' => 'Coupon deleted successfully']);
    }

    public function status(Request $request)
    {
        $coupon = Coupon::findOrFail($request->id);
        $coupon->status = !$coupon->status;
        $coupon->save();
        return response()->json(['status' => true, 'message' => 'Status updated successfully']);
    }
}
