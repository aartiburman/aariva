<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $vendorIds = $user->role == '1'
            ? \App\Models\User::whereIn('role', ['1', '2'])->pluck('id')->toArray()
            : [$user->id];

        $productsQuery = Product::whereIn('vendor_id', $vendorIds)->where('status', 1);

        $totalProducts = (clone $productsQuery)->count();
        $totalVariants = ProductVariant::whereHas('product', function ($q) use ($vendorIds) {
            $q->whereIn('vendor_id', $vendorIds);
        })->count();

        $lowStockVariants = ProductVariant::whereHas('product', function ($q) use ($vendorIds) {
            $q->whereIn('vendor_id', $vendorIds);
        })->where('stock', '>', 0)
          ->whereColumn('stock', '<=', 'low_stock_threshold')
          ->count();

        $outOfStockVariants = ProductVariant::whereHas('product', function ($q) use ($vendorIds) {
            $q->whereIn('vendor_id', $vendorIds);
        })->where('stock', '<=', 0)->count();

        $recentMovements = StockMovement::with(['variant.product', 'user'])
            ->whereHas('variant.product', function ($q) use ($vendorIds) {
                $q->whereIn('vendor_id', $vendorIds);
            })
            ->latest()
            ->take(20)
            ->get();

        $lowStockList = ProductVariant::with(['product', 'warehouse'])
            ->whereHas('product', function ($q) use ($vendorIds) {
                $q->whereIn('vendor_id', $vendorIds);
            })
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->orderBy('stock')
            ->paginate(20);

        $warehouses = Warehouse::where('status', true)->get();

        $stockValue = ProductVariant::whereHas('product', function ($q) use ($vendorIds) {
            $q->whereIn('vendor_id', $vendorIds);
        })->selectRaw('SUM(stock * final_price) as total_value')->value('total_value');

        return view('backend.admin.inventory.dashboard', compact(
            'totalProducts', 'totalVariants', 'lowStockVariants', 'outOfStockVariants',
            'recentMovements', 'lowStockList', 'warehouses', 'stockValue'
        ));
    }

    public function stockAdjustment(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $variant = ProductVariant::findOrFail($request->variant_id);
        $quantity = $request->type === 'out' ? -$request->quantity : $request->quantity;
        $stockBefore = $variant->stock;

        if ($request->type === 'adjustment') {
            $quantity = $request->quantity - $stockBefore;
        }

        $newStock = $stockBefore + $quantity;
        if ($newStock < 0) {
            return response()->json(['status' => false, 'message' => 'Insufficient stock for this operation'], 422);
        }

        $variant->update(['stock' => $newStock]);

        StockMovement::create([
            'product_variant_id' => $variant->id,
            'warehouse_id' => $variant->warehouse_id,
            'user_id' => Auth::id(),
            'type' => $request->type,
            'quantity' => abs($quantity),
            'stock_before' => $stockBefore,
            'stock_after' => $newStock,
            'reference_type' => 'adjustment',
            'reason' => $request->reason,
            'notes' => $request->notes,
        ]);

        return response()->json(['status' => true, 'message' => 'Stock adjusted successfully', 'new_stock' => $newStock]);
    }

    public function movements(Request $request)
    {
        $user = Auth::user();
        $vendorIds = $user->role == '1'
            ? \App\Models\User::whereIn('role', ['1', '2'])->pluck('id')->toArray()
            : [$user->id];

        $query = StockMovement::with(['variant.product', 'user', 'warehouse'])
            ->whereHas('variant.product', function ($q) use ($vendorIds) {
                $q->whereIn('vendor_id', $vendorIds);
            });

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('variant.product', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        $movements = $query->latest()->paginate(30);

        if ($request->ajax()) {
            return view('backend.admin.inventory.partials.movements-table', compact('movements'))->render();
        }

        return view('backend.admin.inventory.movements', compact('movements'));
    }

    public function warehouses()
    {
        $warehouses = Warehouse::orderBy('name')->paginate(20);
        return view('backend.admin.inventory.warehouses', compact('warehouses'));
    }

    public function storeWarehouse(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
        ]);

        Warehouse::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . uniqid(),
            'location' => $request->location,
            'phone' => $request->phone,
            'email' => $request->email,
            'manager_name' => $request->manager_name,
        ]);

        return redirect()->back()->with('success', 'Warehouse added successfully');
    }

    public function updateWarehouse(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->update($request->only(['name', 'location', 'phone', 'email', 'manager_name', 'status']));
        return redirect()->back()->with('success', 'Warehouse updated successfully');
    }

    public function deleteWarehouse($id)
    {
        Warehouse::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Warehouse deleted successfully');
    }

    public function updateThreshold(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'threshold' => 'required|integer|min:0',
        ]);

        ProductVariant::where('id', $request->variant_id)->update(['low_stock_threshold' => $request->threshold]);
        return response()->json(['status' => true, 'message' => 'Threshold updated']);
    }
}
