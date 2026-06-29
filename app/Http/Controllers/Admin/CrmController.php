<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CustomerGroup;
use App\Models\CustomerNote;
use App\Models\AbandonedCart;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CrmController extends Controller
{
    public function dashboard(Request $request)
    {
        $totalCustomers = User::where('role', '3')->count();
        $activeCustomers = User::where('role', '3')->where('status', 1)->count();
        $totalGroups = CustomerGroup::where('status', true)->count();
        $abandonedCarts = AbandonedCart::where('status', 'active')->count();

        $recentCustomers = User::where('role', '3')->latest()->take(10)->get();

        $customerGroups = CustomerGroup::withCount('customers')->where('status', true)->get();

        // Customers with most orders
        $topCustomers = User::where('role', '3')
            ->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->take(10)
            ->get();

        if ($request->ajax()) {
            $query = User::where('role', '3');

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('name', 'LIKE', "%{$s}%")
                      ->orWhere('email', 'LIKE', "%{$s}%")
                      ->orWhere('phone', 'LIKE', "%{$s}%");
                });
            }

            if ($request->filled('group_id')) {
                $query->whereHas('customerGroups', fn($q) => $q->where('customer_group_id', $request->group_id));
            }

            $customers = $query->orderBy('id', 'desc')->paginate(20);
            return view('backend.admin.crm.partials.customers-table', compact('customers'))->render();
        }

        return view('backend.admin.crm.dashboard', compact(
            'totalCustomers', 'activeCustomers', 'totalGroups', 'abandonedCarts',
            'recentCustomers', 'customerGroups', 'topCustomers'
        ));
    }

    public function customers()
    {
        $customers = User::where('role', '3')->orderBy('id', 'desc')->paginate(20);
        $groups = CustomerGroup::where('status', true)->get();
        return view('backend.admin.crm.customers', compact('customers', 'groups'));
    }

    public function customerDetail($id)
    {
        $customer = User::with(['customerGroups', 'customerNotes.user', 'orders' => function ($q) {
            $q->latest()->limit(15);
        }, 'orders.items'])->findOrFail($id);

        $totalOrders = Order::where('user_id', $id)->count();
        $totalSpent = Order::where('user_id', $id)->where('payment_status', 'paid')->sum('total_cost');
        $tickets = Ticket::where('user_id', $id)->latest()->take(10)->get();

        return view('backend.admin.crm.customer-detail', compact('customer', 'totalOrders', 'totalSpent', 'tickets'));
    }

    public function storeNote(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'note' => 'required|string|max:2000',
        ]);

        CustomerNote::create([
            'customer_id' => $request->customer_id,
            'user_id' => Auth::id(),
            'note' => $request->note,
        ]);

        return redirect()->back()->with('success', 'Note added successfully');
    }

    public function deleteNote($id)
    {
        CustomerNote::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Note deleted');
    }

    public function groups()
    {
        $groups = CustomerGroup::withCount('customers')->orderBy('name')->paginate(20);
        return view('backend.admin.crm.groups', compact('groups'));
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        CustomerGroup::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . uniqid(),
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Group created successfully');
    }

    public function updateGroup(Request $request, $id)
    {
        $group = CustomerGroup::findOrFail($id);
        $group->update($request->only(['name', 'description', 'status']));
        return redirect()->back()->with('success', 'Group updated successfully');
    }

    public function deleteGroup($id)
    {
        CustomerGroup::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Group deleted');
    }

    public function assignGroup(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:customer_groups,id',
        ]);

        $customer = User::findOrFail($request->customer_id);
        $customer->customerGroups()->sync($request->group_ids ?? []);

        return redirect()->back()->with('success', 'Groups updated');
    }

    public function abandonedCarts()
    {
        $carts = AbandonedCart::with('user')->latest()->paginate(20);
        return view('backend.admin.crm.abandoned-carts', compact('carts'));
    }
}
