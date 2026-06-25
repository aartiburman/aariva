<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Country;
use App\Models\ShippingAddress;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ImageHelper;



class HomeController extends Controller
{
    public function admin_dashboard(Request $request)
    {
        $year = $request->input('year');
        $dateRange = $request->input('date_range');
        
        $queryBuilder = function($query, $column = 'created_at') use ($year, $dateRange) {
            if ($dateRange) {
                $dates = explode(' to ', $dateRange);
                if (count($dates) == 2) {
                    $query->whereDate($column, '>=', $dates[0])
                          ->whereDate($column, '<=', $dates[1]);
                } else {
                    $query->whereDate($column, $dates[0]);
                }
            } elseif ($year) {
                $query->whereYear($column, $year);
            }
            return $query;
        };

        $totalOrders = $queryBuilder(OrderItem::query(), 'order_items.created_at')->distinct('order_id')->count('order_id');
        
        // Customer count should be absolute (all-time)
        $totalCustomers = User::where('role', '3')->count();
        
        // Vendor counts should be absolute (all-time) to match Vendor List
        $totalVendors = User::where('role', '2')->count();
        $vendorStats = (object)[
            'total' => $totalVendors,
            'active' => User::where('role', '2')->where('status', '1')->count(),
            'pending' => User::where('role', '2')->whereIn('status', ['0', '4'])->count(),
            'blocked' => User::where('role', '2')->whereIn('status', ['2', '3'])->count(),
        ];

        // Product count should be absolute (all-time)
        $totalProducts = Product::count();
        
        $totalRevenue = $queryBuilder(OrderItem::where('payment_status', '1'), 'order_items.created_at')->sum('total_actual_price');

        // Vendor Growth calculation
        $lastMonthVendors = User::where('role', '2')
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();
        $currentMonthVendors = User::where('role', '2')
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
        $vendorGrowth = $lastMonthVendors > 0 ? (($currentMonthVendors - $lastMonthVendors) / $lastMonthVendors) * 100 : 0;
        $vendorStats->growth = number_format($vendorGrowth, 1);
        $vendorStats->growth_class = $vendorGrowth >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
        $vendorStats->growth_prefix = $vendorGrowth >= 0 ? '+' : '';

        // Fetch country IDs dynamically
        $nepalId = Country::where('name', 'Nepal')->value('id') ;
        // $uaeId = Country::where('name', 'United Arab Emirates')->value('id');
        // $indiaId = Country::where('name', 'India')->value('id');

        // Revenue for Nepal and UAE (Based on Vendor Performance)
        $nepalRevenue = $queryBuilder(
            OrderItem::where('order_items.payment_status', '1')
                ->join('users', 'order_items.vendor_id', '=', 'users.id')
                ->where('users.country_id', $nepalId)
                ->where('users.role', '2'),
            'order_items.created_at'
        )->sum('order_items.total_actual_price');

        // $uaeRevenue = $queryBuilder(
        //     OrderItem::where('order_items.payment_status', '1')
        //         ->join('users', 'order_items.vendor_id', '=', 'users.id')
        //         ->where('users.country_id', $uaeId)
        //         ->where('users.role', '2'),
        //     'order_items.created_at'
        // )->sum('order_items.total_actual_price');

        // $indiaRevenue = $queryBuilder(
        //     OrderItem::where('order_items.payment_status', '1')
        //         ->join('users', 'order_items.vendor_id', '=', 'users.id')
        //         ->where('users.country_id', $indiaId)
        //         ->where('users.role', '2'),
        //     'order_items.created_at'
        // )->sum('order_items.total_actual_price');

        // Performance Chart Data by Country
        $countryPerformanceData = [];
        $countries = [
            'Nepal' => $nepalId,
            // 'India' => $indiaId,
            // 'UAE' => $uaeId
        ];

        foreach ($countries as $name => $id) {
            $data = $queryBuilder(
                OrderItem::select(
                    DB::raw('SUM(total_actual_price) as revenue'),
                    DB::raw("DATE_FORMAT(order_items.created_at, '%b') as month"),
                    DB::raw("MONTH(order_items.created_at) as month_num")
                )
                ->join('users', 'order_items.vendor_id', '=', 'users.id')
                ->where('users.country_id', $id)
                ->where('users.role', '2')
                ->where('order_items.payment_status', '1'),
                'order_items.created_at'
            )
            ->groupBy('month', 'month_num')
            ->orderBy('month_num')
            ->get();

            $countryPerformanceData[$name] = $data;
        }

        // Prepare chart data for all months
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $nepalChartData = array_fill(0, 12, 0);
        // $indiaChartData = array_fill(0, 12, 0);
        // $uaeChartData = array_fill(0, 12, 0);

        foreach ($countryPerformanceData['Nepal'] as $item) {
            $index = array_search($item->month, $months);
            if ($index !== false) $nepalChartData[$index] = (float)$item->revenue;
        }
        // foreach ($countryPerformanceData['India'] as $item) {
        //     $index = array_search($item->month, $months);
        //     if ($index !== false) $indiaChartData[$index] = (float)$item->revenue;
        // }
        // foreach ($countryPerformanceData['UAE'] as $item) {
        //     $index = array_search($item->month, $months);
        //     if ($index !== false) $uaeChartData[$index] = (float)$item->revenue;
        // }

        // Calculate percentage changes
        $lastMonthOrders = $queryBuilder(OrderItem::query(), 'order_items.created_at')
            ->whereYear('order_items.created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('order_items.created_at', Carbon::now()->subMonth()->month)
            ->distinct('order_id')
            ->count('order_id');
        $currentMonthOrders = $queryBuilder(OrderItem::query(), 'order_items.created_at')
            ->whereYear('order_items.created_at', Carbon::now()->year)
            ->whereMonth('order_items.created_at', Carbon::now()->month)
            ->distinct('order_id')
            ->count('order_id');
        $orderChange = $lastMonthOrders > 0 ? (($currentMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100 : ($currentMonthOrders > 0 ? 100 : 0);

        // Recent Orders - fetched from OrderItem as requested
        $recentOrders = $queryBuilder(OrderItem::with(['order.user', 'product', 'variant', 'vendor.country']), 'order_items.created_at')
        ->orderBy('order_items.id', 'desc')
        ->where('order_items.status', 0)
        ->paginate(10)->withQueryString();

        // Stats for cards
        $orderStats = (object)[
            'pending' => $queryBuilder(OrderItem::where('status', 0), 'order_items.created_at')->count(),
            'confirmed' => $queryBuilder(OrderItem::where('status', 1), 'order_items.created_at')->count(),
            'shipped' => $queryBuilder(OrderItem::where('status', 2), 'order_items.created_at')->count(),
            'delivered' => $queryBuilder(OrderItem::where('status', 3), 'order_items.created_at')->count(),
            'cancelled' => $queryBuilder(OrderItem::where('status', 4), 'order_items.created_at')->count(),
            'returned' => $queryBuilder(OrderItem::where('status', 5), 'order_items.created_at')->count(),
        ];

        // Performance Chart Data - based on OrderItem
        $performanceQuery = OrderItem::select(
            DB::raw('SUM(total_actual_price) as revenue'),
            DB::raw('COUNT(*) as items_count'),
            DB::raw("DATE_FORMAT(order_items.created_at, '%b') as month"),
            DB::raw("MONTH(order_items.created_at) as month_num")
        );
        $performanceData = $queryBuilder($performanceQuery, 'order_items.created_at')
            ->groupBy('month', 'month_num')
            ->orderBy('month_num')
            ->get();

        $chartMonths = $performanceData->pluck('month')->toArray();
        $chartRevenue = $performanceData->pluck('revenue')->map(fn($v) => (float)$v)->toArray();
        $chartOrders = $performanceData->pluck('items_count')->toArray();

        // Conversions Data - based on OrderItem
        $thisWeekRevenue = OrderItem::where('payment_status', '1')
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('total_actual_price');
        $lastWeekRevenue = OrderItem::where('payment_status', '1')
            ->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
            ->sum('total_actual_price');

        // Calculate Revenue Growth
        if ($lastWeekRevenue > 0) {
            $revenueGrowth = (($thisWeekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100;
        } else {
            $revenueGrowth = 0; // Show 0 if no historical data as requested
        }

        // Revenue by Country
        $revenueByCountryQuery = OrderItem::select(
                'countries.name as country',
                'countries.currency',
                DB::raw('SUM(order_items.total_actual_price) as revenue'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as orders_count')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('shipping_addresses', 'orders.shipping_id', '=', 'shipping_addresses.id')
            ->join('countries', 'shipping_addresses.country_id', '=', 'countries.id')
            ->where('countries.id', $nepalId)
            ->where('order_items.payment_status', '1');
             
        $revenueByCountryData = $queryBuilder($revenueByCountryQuery, 'order_items.created_at')
            ->groupBy('countries.name', 'countries.currency')
            ->orderByDesc('revenue')
            ->get();

        // Required countries to always show
        // $requiredCountries = ['Nepal', 'United Arab Emirates', 'India'];
        $requiredCountries = ['Nepal'];
        $existingCountries = $revenueByCountryData->pluck('country')->toArray();

        foreach ($requiredCountries as $country) {
            if (!in_array($country, $existingCountries)) {
                $currency = Country::where('name', $country)->value('currency') ?? '$';
                $revenueByCountryData->push((object)[
                    'country' => $country,
                    'revenue' => 0,
                    'orders_count' => 0,
                    'currency' => $currency
                ]);
            }
        }

        $totalRevenueAll = $revenueByCountryData->sum('revenue');

        // Ensure Nepal and UAE are always represented if they have data
        $sessionsByCountry = $revenueByCountryData->map(function($item) use ($totalRevenueAll) {
            $currency = $item->currency ?? '$';
            if($currency == 'रु') $currency = 'NPR';
            return [
                'country' => $item->country,
                'revenue' => number_format($item->revenue, 2),
                'currency' => $currency,
                'percentage' => $totalRevenueAll > 0 ? round(($item->revenue / $totalRevenueAll) * 100) : 0,
                'orders' => $item->orders_count
            ];
        })->toArray();

        // If Nepal or UAE are missing from the top list, we could add them, 
        // but the query already groups by country, so they will appear if they have orders.
        // To specifically highlight them as requested, we can sort them to the top or just leave as is if they are the main countries.
        
        return view('backend/admin/admin-dashboard', compact(
            'totalOrders', 
            'totalCustomers', 
            'totalVendors',
            'vendorStats',
            'totalProducts', 
            'totalRevenue',
            'nepalRevenue',
            // 'uaeRevenue',
            // 'indiaRevenue',
            'nepalChartData',
            // 'indiaChartData',
            // 'uaeChartData',
            'orderChange',
            'chartMonths',
            'chartRevenue',
            'chartOrders',
            'thisWeekRevenue',
            'lastWeekRevenue',
            'revenueGrowth',
            'recentOrders',
            'orderStats',
            'sessionsByCountry'
        ));
    }

    public function vendor_dashboard()
    {
        $vendorId = Auth::user()->id;

        // Total Orders (where the vendor has items)
        $totalOrders = Order::whereHas('items', function($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->count();

        // Total Customers (unique users who ordered from this vendor)
        $totalCustomers = User::where('role', '3')
            ->whereHas('orders.items', function($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })->count();

        // Total Products
        $totalProducts = Product::where('vendor_id', $vendorId)->count();

        // Total Revenue
        $totalRevenue = OrderItem::where('vendor_id', $vendorId)
            ->where('payment_status', '1')
            ->sum('total_actual_price');

        // Calculate percentage changes
        $lastMonthOrders = Order::whereHas('items', function($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();
            
        $currentMonthOrders = Order::whereHas('items', function($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
            
        $orderChange = $lastMonthOrders > 0 ? (($currentMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100 : ($currentMonthOrders > 0 ? 100 : 0);

        // Calculate Revenue changes
        $lastMonthRevenue = OrderItem::where('vendor_id', $vendorId)
            ->where('payment_status', '1')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('total_actual_price');

        $currentMonthRevenue = OrderItem::where('vendor_id', $vendorId)
            ->where('payment_status', '1')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_actual_price');

        $revenueChange = $lastMonthRevenue > 0 ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : ($currentMonthRevenue > 0 ? 100 : 0);

        // Recent Orders - fetched from OrderItem
        $recentOrders = OrderItem::with(['order.user', 'product'])
            ->where('vendor_id', $vendorId)
            ->orderBy('id','desc')
            ->limit(10)
            ->get();

        // Stats for cards
        $orderStats = (object)[
            'pending' => OrderItem::where('status', 0)->where('vendor_id', $vendorId)->count(),
            'confirmed' => OrderItem::where('status', 1)->where('vendor_id', $vendorId)->count(),
            'shipped' => OrderItem::where('status', 2)->where('vendor_id', $vendorId)->count(),
            'delivered' => OrderItem::where('status', 3)->where('vendor_id', $vendorId)->count(),
            'cancelled' => OrderItem::where('status', 4)->where('vendor_id', $vendorId)->count(),
            'returned' => OrderItem::where('status', 5)->where('vendor_id', $vendorId)->count(),
            'dispute' => OrderItem::where('status', 6)->where('vendor_id', $vendorId)->count(),
        ];

        // Performance Chart Data - based on OrderItem
        $performanceData = OrderItem::select(
            DB::raw('SUM(total_actual_price) as revenue'),
            DB::raw('COUNT(*) as items_count'),
            DB::raw("DATE_FORMAT(created_at, '%b') as month"),
            DB::raw("MONTH(created_at) as month_num")
        )
        ->where('vendor_id', $vendorId)
        ->where('payment_status', '1')
        ->where('created_at', '>=', Carbon::now()->subYear())
        ->groupBy('month', 'month_num')
        ->orderBy('month_num')
        ->get();

        $chartMonths = $performanceData->pluck('month')->toArray();
        $chartRevenue = $performanceData->pluck('revenue')->map(fn($v) => (float)$v)->toArray();
        $chartOrders = $performanceData->pluck('items_count')->toArray();

        // Conversions Data - based on OrderItem
        $thisWeekRevenue = OrderItem::where('vendor_id', $vendorId)
            ->where('payment_status', '1')
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('total_actual_price');
        $lastWeekRevenue = OrderItem::where('vendor_id', $vendorId)
            ->where('payment_status', '1')
            ->whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
            ->sum('total_actual_price');

        $revenueGrowth = $lastWeekRevenue > 0 ? (($thisWeekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100 : ($thisWeekRevenue > 0 ? 100 : 0);

        // Orders Location - based on OrderItem's order shipping address
        $sessionsByCountry = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('shipping_addresses', 'orders.shipping_id', '=', 'shipping_addresses.id')
            ->join('countries', 'shipping_addresses.country_id', '=', 'countries.id')
            ->where('order_items.vendor_id', $vendorId)
            ->select('countries.name as name', DB::raw('COUNT(DISTINCT orders.id) as sessions'))
            ->groupBy('countries.name')
            ->get()
            ->map(function($item) {
                // Fallback mapping for common countries
                $codes = [
                    'Nepal' => 'NP',
                    'India' => 'IN',
                    'United Arab Emirates' => 'AE',
                    'United States' => 'US',
                    'China' => 'CN',
                    'United Kingdom' => 'GB',
                    'Australia' => 'AU',
                    'Canada' => 'CA'
                ];
                return [
                    'name' => $item->name,
                    'sessions' => $item->sessions,
                    'code' => $codes[$item->name] ?? strtoupper(substr($item->name, 0, 2))
                ];
            });

        $sessionsByState = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('shipping_addresses', 'orders.shipping_id', '=', 'shipping_addresses.id')
            ->join('states', 'shipping_addresses.state_id', '=', 'states.id')
            ->where('order_items.vendor_id', $vendorId)
            ->select('states.name as state', DB::raw('COUNT(DISTINCT orders.id) as sessions'))
            ->groupBy('states.name')
            ->get()
            ->map(function($item) use ($totalOrders) {
                $sessions = (int)$item->sessions;
                $percentage = $totalOrders > 0 ? ($sessions / $totalOrders) * 100 : 0;
                return [
                    'state' => $item->state,
                    'sessions' => $sessions,
                    'percentage' => round($percentage, 1)
                ];
            });

        $totalVendorCustomersCount = $totalCustomers;
        $totalVendorProductsCount = $totalProducts;
        $vendor = optional(Auth::user())->loadMissing('country') ?? Auth::user();
        $currency = $vendor->country ? $vendor->country->currency_code : 'AED';

        return view('backend/vendor/vendor_dashboard', compact(
            'totalOrders', 'totalCustomers', 'totalProducts', 'totalRevenue',
            'orderChange', 'revenueChange', 'recentOrders', 'orderStats',
            'chartMonths', 'chartRevenue', 'chartOrders', 'revenueGrowth',
            'sessionsByCountry', 'sessionsByState', 'totalVendorCustomersCount',
            'totalVendorProductsCount', 'currency'
        ));
    }

    public function getVendorPerformanceData(Request $request)
    {
        $vendorId = Auth::user()->id;
        $filter = $request->filter ?? '1y';
        
        $query = OrderItem::select(
            DB::raw('SUM(total_actual_price) as revenue'),
            DB::raw('COUNT(*) as items_count')
        )
        ->where('vendor_id', $vendorId)
        ->where('payment_status', '1');

        if ($filter === '1m') {
            $query->addSelect(DB::raw("DATE_FORMAT(created_at, '%d %b') as label"))
                  ->where('created_at', '>=', Carbon::now()->subMonth())
                  ->groupBy(DB::raw("DATE_FORMAT(created_at, '%d %b')"))
                  ->orderBy(DB::raw('MIN(created_at)'));
        } elseif ($filter === '6m') {
            $query->addSelect(DB::raw("DATE_FORMAT(created_at, '%b %Y') as label"))
                  ->where('created_at', '>=', Carbon::now()->subMonths(6))
                  ->groupBy(DB::raw("DATE_FORMAT(created_at, '%b %Y')"))
                  ->orderBy(DB::raw('MIN(created_at)'));
        } elseif ($filter === '1y') {
            $query->addSelect(DB::raw("DATE_FORMAT(created_at, '%b %Y') as label"))
                  ->where('created_at', '>=', Carbon::now()->subYear())
                  ->groupBy(DB::raw("DATE_FORMAT(created_at, '%b %Y')"))
                  ->orderBy(DB::raw('MIN(created_at)'));
        } else {
            // ALL
            $query->addSelect(DB::raw("DATE_FORMAT(created_at, '%Y') as label"))
                  ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y')"))
                  ->orderBy(DB::raw('MIN(created_at)'));
        }

        $performanceData = $query->get();

        return response()->json([
            'status' => true,
            'labels' => $performanceData->pluck('label')->toArray(),
            'revenue' => $performanceData->pluck('revenue')->map(fn($v) => (float)$v)->toArray(),
            'orders' => $performanceData->pluck('items_count')->toArray()
        ]);
    }

    public function vendor_sales_report(Request $request)
    {
        try {
            $vendorId = Auth::user()->id;
            $vendor = optional(Auth::user())->loadMissing('country') ?? Auth::user();
            $currency = $vendor->country ? $vendor->country->currency_code : 'AED';

            $report_type = $request->get('report_type', 'order_wise');

            if ($report_type == 'date_wise') {
                $query = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('order_items.vendor_id', $vendorId)
                    ->selectRaw('
                    DATE(order_items.created_at) as order_date,
                    COUNT(DISTINCT order_items.order_id) as total_orders,
                    SUM(order_items.quantity) as total_qty,
                    SUM(order_items.total_actual_price) as total_sales,
                    SUM(orders.taxes) as tax,
                    SUM(orders.delivery_charges) as delivery_charge
                ')
                    ->groupBy('order_date');
            } else {
                $query = OrderItem::with(['order.user', 'product'])
                    ->where('vendor_id', $vendorId);
            }

            // Apply Filters
            if ($request->filled('status')) {
                $query->where('order_items.status', $request->status);
            } else {
                // Default to Delivered (3) if no status is specified
                $query->where('order_items.status', 3);
            }

            if ($request->filled('date_range')) {
                $dates = explode(' to ', $request->date_range);
                if (count($dates) == 2) {
                    $query->whereDate('order_items.created_at', '>=', $dates[0])
                          ->whereDate('order_items.created_at', '<=', $dates[1]);
                } else {
                    $query->whereDate('order_items.created_at', $dates[0]);
                }
            }

            // Export Data
            if ($request->has('export')) {
                $data = $query->get();
                $filename = "vendor_sales_report_" . $report_type . "_" . date('Y-m-d') . ".csv";
                $headers = [
                    "Content-type"        => "text/csv",
                    "Content-Disposition" => "attachment; filename=$filename",
                    "Pragma"              => "no-cache",
                    "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                    "Expires"             => "0"
                ];

                if ($report_type == 'date_wise') {
                    $columns = ['Date', 'Total Orders', 'Qty Sold', 'Sub Total', 'Tax', 'Delivery', 'Total Amount'];
                } else {
                    $columns = ['Order ID', 'Product', 'Customer', 'Amount', 'Status', 'Date'];
                }

                $callback = function() use($data, $columns, $report_type, $currency) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);

                    foreach ($data as $item) {
                        if ($report_type == 'date_wise') {
                            fputcsv($file, [
                                \Carbon\Carbon::parse($item->order_date)->format('Y-m-d'),
                                $item->total_orders,
                                $item->total_qty,
                                number_format($item->total_sales, 2),
                                number_format($item->tax, 2),
                                number_format($item->delivery_charge, 2),
                                number_format($item->total_sales + $item->tax + $item->delivery_charge, 2),
                            ]);
                        } else {
                            $status = 'Pending';
                            switch($item->status) {
                                case 1: $status = 'Confirmed'; break;
                                case 2: $status = 'Shipped'; break;
                                case 3: $status = 'Delivered'; break;
                                case 4: $status = 'Cancelled'; break;
                                case 5: $status = 'Returned'; break;
                                case 6: $status = 'In Dispute'; break;
                            }
                            fputcsv($file, [
                                $item->order->order_reference_id ?? 'N/A',
                                $item->product->name ?? 'Product Deleted',
                                $item->order->user->name ?? 'Guest',
                                number_format($item->total_actual_price, 2),
                                $status,
                                \Carbon\Carbon::parse($item->created_at)->format('Y-m-d'),
                            ]);
                        }
                    }
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);
            }

            // Summary Stats (Always calculated based on all vendor's paid items, respecting date filter)
            $summaryQuery = OrderItem::where('vendor_id', $vendorId)->where('payment_status', '1');
            
            if ($request->filled('date_range')) {
                $dates = explode(' to ', $request->date_range);
                if (count($dates) == 2) {
                    $summaryQuery->whereDate('created_at', '>=', $dates[0])
                                 ->whereDate('created_at', '<=', $dates[1]);
                } else {
                    $summaryQuery->whereDate('created_at', $dates[0]);
                }
            }

            $totalSales = $summaryQuery->sum('total_actual_price');
            $ordersCount = $summaryQuery->distinct('order_id')->count('order_id');
            $avgOrderValue = $ordersCount > 0 ? $totalSales / $ordersCount : 0;
            $netEarnings = $totalSales; // Simplified

            // Paginated results
            $orderBy = $report_type == 'date_wise' ? 'order_date' : 'order_items.created_at';
            $transactions = $query->orderBy($orderBy, 'desc')->paginate(10)->withQueryString();

            if ($report_type == 'order_wise') {
                $transactions->getCollection()->transform(function ($item) {
                    $item->product_image_url = ImageHelper::getProductImage($item->product->thumbnail ?? '');
                    $item->order_date = $item->created_at;
                    return $item;
                });
            }

            if ($request->ajax()) {
                return response()->json([
                    'table' => view('backend.vendor.partials.sales-report-table', compact('transactions', 'report_type', 'currency'))->render(),
                    'pagination' => $transactions->links()->render(),
                    'info' => 'Showing ' . ($transactions->firstItem() ?? 0) . ' to ' . ($transactions->lastItem() ?? 0) . ' of ' . $transactions->total() . ' entries',
                    'stats' => [
                        'totalSales' => \App\Helpers\PriceHelper::formatLargeNumber($totalSales),
                        'ordersCount' => \App\Helpers\PriceHelper::formatLargeNumber($ordersCount),
                        'avgOrderValue' => \App\Helpers\PriceHelper::formatLargeNumber($avgOrderValue),
                        'netEarnings' => \App\Helpers\PriceHelper::formatLargeNumber($netEarnings),
                    ]
                ]);
            }

            return view('backend/vendor/sales_report', compact(
                'transactions',
                'totalSales',
                'ordersCount',
                'avgOrderValue',
                'netEarnings',
                'currency',
                'report_type'
            ));
        } catch (\Exception $e) {
            return "Vendor Sales Report Error: " . $e->getMessage() . " at line " . $e->getLine() . " in " . $e->getFile();
        }
    }

    public function vendor_support_center()
    {
        return view('backend/vendor/support_center');
    }


    public function shipping_zone()
    {
        return view('backend/admin/shipping_zone/shipping-zone');
    }

    public function add_shipping_zone()
    {
        return view('backend/admin/shipping_zone/add-shipping-zone');
    }

    public function error_403()
    {
        return view('backend/403');
    }

    public function change_password()
    {
        return view('backend/admin/change_password');
    }

  

    public function my_customer_list(Request $request)
    {
        $user = Auth::user();
        $query = User::where('role', '3');

        // If vendor (role 2), only show customers who have ordered from them
        if ((string)$user->role === '2') {
            $vendorId = $user->id;
            $query->whereHas('orders', function ($q) use ($vendorId) {
                $q->whereHas('items', function ($iq) use ($vendorId) {
                    $iq->where('vendor_id', $vendorId);
                });
            });
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

    

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        $customers = $query->latest()->paginate(15)->withQueryString();
        $countries = Country::where('is_active', 1)->get();
        return view('backend/admin/customer/my-customer-list', compact('customers', 'countries'));
    }

    public function export_my_customers(Request $request)
    {
        $user = Auth::user();
        $query = User::where('role', '3');

        // If vendor (role 2), only show customers who have ordered from them
        if ((string)$user->role === '2') {
            $vendorId = $user->id;
            $query->whereHas('orders', function ($q) use ($vendorId) {
                $q->whereHas('items', function ($iq) use ($vendorId) {
                    $iq->where('vendor_id', $vendorId);
                });
            });
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        $customers = $query->latest()->get();

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=customers_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $columns = array('SNO', 'Name', 'Email', 'Phone', 'Gender', 'Status', 'Registered At');

        $callback = function() use ($customers, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($customers as $key => $customer) {
                $status = $customer->status == 1 ? 'Active' : ($customer->status == 0 ? 'Pending' : 'Rejected');
                fputcsv($file, array(
                    $key + 1,
                    $customer->name,
                    $customer->email,
                    $customer->phone,
                    $customer->gender ?? 'N/A',
                    $status,
                    $customer->created_at->format('Y-m-d H:i:s')
                ));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

     
    public function all_customers(Request $request)
    {
        $query = User::where('role', '3');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        $customers = $query->latest()->paginate(15)->withQueryString();
        $countries = Country::where('is_active', 1)->get();

        if ($request->ajax()) {
            return response()->json([
                'table' => view('backend.admin.customer.partials.customer-table', compact('customers'))->render(),
                'pagination' => $customers->links()->render(),
                'info' => 'Showing ' . ($customers->firstItem() ?? 0) . ' to ' . ($customers->lastItem() ?? 0) . ' of ' . $customers->total() . ' entries'
            ]);
        }

        return view('backend/admin/customer/all-customers', compact('customers', 'countries'));
    }

    public function export_customers(Request $request)
    {
        $query = User::where('role', '3');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        $customers = $query->latest()->get();

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=all_customers_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $columns = array('SNO', 'Name', 'Email', 'Phone', 'Gender', 'Status', 'Registered At');

        $callback = function() use ($customers, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($customers as $key => $customer) {
                $status = $customer->status == 1 ? 'Active' : ($customer->status == 0 ? 'Pending' : 'Rejected');
                fputcsv($file, array(
                    $key + 1,
                    $customer->name,
                    $customer->email,
                    $customer->phone,
                    $customer->gender ?? 'N/A',
                    $status,
                    $customer->created_at->format('Y-m-d H:i:s')
                ));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function customer_detail($id)
    {
        $customer = User::with(['country', 'state', 'city'])->findOrFail($id);
        
        $orders = Order::where('user_id', $id)
            ->with(['items.product', 'items.vendor', 'shippingAddress.country', 'shippingAddress.state', 'shippingAddress.city'])
            ->latest()
            ->get();
            
        $totalOrders = $orders->count();
        
        // Sum total_cost from paid orders
        $totalSpent = Order::where('user_id', $id)->where('payment_status', '1')->sum('total_cost');

        // All shipping addresses for this customer
        $shippingAddresses = ShippingAddress::where('user_id', $id)
            ->with(['country', 'state', 'city'])
            ->latest()
            ->get();
    $profile_image = ImageHelper::getUserImage($customer->image);

        
        return view('backend/admin/customer/customer-detail', compact('customer', 'orders', 'totalOrders', 'totalSpent', 'shippingAddresses', 'profile_image'));
    }

    public function tax_rates()
    {
        return view('backend/admin/tax/tax-rates');
    }

    public function change_customer_status(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'status' => 'required|in:0,1,2',
            'rejection_reason' => 'required_if:status,2'
        ]);

        $user = User::find($request->id);
        $user->status = $request->status;
        if ($request->status == 2) {
            $user->rejection_reason = $request->rejection_reason;
        } else {
            $user->rejection_reason = null;
        }
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Customer status updated successfully'
        ]);
    }
}
