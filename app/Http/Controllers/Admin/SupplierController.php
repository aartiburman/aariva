<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Country;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', "%{$s}%")
                  ->orWhere('company_name', 'LIKE', "%{$s}%")
                  ->orWhere('email', 'LIKE', "%{$s}%")
                  ->orWhere('phone', 'LIKE', "%{$s}%");
            });
        }

        $suppliers = $query->orderBy('name')->paginate(20);

        if ($request->ajax()) {
            return view('backend.admin.supplier.partials.suppliers-table', compact('suppliers'))->render();
        }

        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        return view('backend.admin.supplier.index', compact('suppliers', 'countries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'gst_number' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        $data = $request->all();
        $data = $this->fillLocationNames($data);

        Supplier::create($data);
        return redirect()->back()->with('success', 'Supplier added successfully');
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $data = $request->all();
        $data = $this->fillLocationNames($data);
        $supplier->update($data);
        return redirect()->back()->with('success', 'Supplier updated successfully');
    }

    private function fillLocationNames(array $data): array
    {
        if (!empty($data['country_id'])) {
            $country = Country::find($data['country_id']);
            $data['country'] = $country ? $country->name : null;
        }
        if (!empty($data['state_id'])) {
            $state = State::find($data['state_id']);
            $data['state'] = $state ? $state->name : null;
        }
        if (!empty($data['city_id'])) {
            $city = City::find($data['city_id']);
            $data['city'] = $city ? $city->name : null;
        }
        return $data;
    }

    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Supplier deleted');
    }

    public function detail($id)
    {
        $supplier = Supplier::with(['products', 'purchaseOrders.items'])->findOrFail($id);
        $allProducts = Product::with('variants')->where('status', 1)->orderBy('name')->get();
        return view('backend.admin.supplier.detail', compact('supplier', 'allProducts'));
    }

    public function linkProduct(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id' => 'required|exists:products,id',
            'supplier_sku' => 'nullable|string|max:255',
            'supply_price' => 'nullable|numeric|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'is_preferred' => 'nullable|boolean',
        ]);

        $supplier = Supplier::findOrFail($request->supplier_id);
        $supplier->products()->syncWithoutDetaching([
            $request->product_id => [
                'supplier_sku' => $request->supplier_sku,
                'supply_price' => $request->supply_price ?? 0,
                'lead_time_days' => $request->lead_time_days ?? 0,
                'is_preferred' => $request->is_preferred ?? false,
            ]
        ]);

        return redirect()->back()->with('success', 'Product linked to supplier');
    }

    public function unlinkProduct($supplierId, $productId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        $supplier->products()->detach($productId);
        return redirect()->back()->with('success', 'Product unlinked from supplier');
    }

    public function purchaseOrders(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'user', 'warehouse']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            }
        }

        $orders = $query->latest()->paginate(20);
        $suppliers = Supplier::where('status', true)->orderBy('name')->get();

        if ($request->ajax()) {
            return view('backend.admin.supplier.partials.purchase-orders-table', compact('orders'))->render();
        }

        return view('backend.admin.supplier.purchase-orders', compact('orders', 'suppliers'));
    }

    public function storePurchaseOrder(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'expected_at' => 'nullable|date',
        ]);

        $orderNumber = 'PO-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        $subTotal = 0;

        $items = [];
        foreach ($request->items as $item) {
            $totalPrice = $item['quantity'] * $item['unit_price'];
            $subTotal += $totalPrice;
            $items[] = new PurchaseOrderItem([
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $totalPrice,
            ]);
        }

        $purchaseOrder = PurchaseOrder::create([
            'order_number' => $orderNumber,
            'supplier_id' => $request->supplier_id,
            'user_id' => Auth::id(),
            'warehouse_id' => $request->warehouse_id,
            'sub_total' => $subTotal,
            'total' => $subTotal,
            'status' => 'pending',
            'notes' => $request->notes,
            'expected_at' => $request->expected_at,
        ]);

        $purchaseOrder->items()->saveMany($items);

        return redirect()->route('supplier.purchase.order.detail', $purchaseOrder->id)
            ->with('success', 'Purchase order created: ' . $orderNumber);
    }

    public function purchaseOrderDetail($id)
    {
        $order = PurchaseOrder::with(['supplier', 'user', 'warehouse', 'items.product', 'items.variant'])->findOrFail($id);
        $warehouses = Warehouse::where('status', true)->get();
        return view('backend.admin.supplier.purchase-order-detail', compact('order', 'warehouses'));
    }

    public function updatePurchaseOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,pending,approved,cancelled',
        ]);

        $order = PurchaseOrder::findOrFail($id);
        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        return redirect()->back()->with('success', "Purchase order status changed from {$oldStatus} to {$request->status}");
    }

    public function receivePurchaseOrder(Request $request, $id)
    {
        $order = PurchaseOrder::with('items')->findOrFail($id);

        if ($order->status !== 'approved') {
            return redirect()->back()->with('error', 'Only approved orders can be received');
        }

        $request->validate([
            'received_items' => 'required|array',
            'received_items.*.id' => 'required|exists:purchase_order_items,id',
            'received_items.*.received_quantity' => 'required|integer|min:0',
        ]);

        foreach ($request->received_items as $ri) {
            $item = $order->items()->find($ri['id']);
            if (!$item) continue;

            $receivedQty = min($ri['received_quantity'], $item->quantity);
            $item->update(['received_quantity' => $receivedQty]);

            // Update variant stock
            if ($item->variant_id) {
                $variant = ProductVariant::find($item->variant_id);
                if ($variant) {
                    $stockBefore = $variant->stock;
                    $variant->increment('stock', $receivedQty);

                    StockMovement::create([
                        'product_variant_id' => $variant->id,
                        'warehouse_id' => $order->warehouse_id,
                        'user_id' => Auth::id(),
                        'type' => 'in',
                        'quantity' => $receivedQty,
                        'stock_before' => $stockBefore,
                        'stock_after' => $variant->stock,
                        'reference_type' => 'purchase_order',
                        'reference_id' => $order->id,
                        'reason' => 'Purchase order received: ' . $order->order_number,
                    ]);
                }
            }
        }

        $order->update([
            'status' => 'received',
            'received_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Purchase order received successfully. Stock updated.');
    }
}
