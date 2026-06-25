<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Country;
use App\Models\VendorsDocument;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Log;
use App\Helpers\ImageHelper;
use App\Helpers\PriceHelper;


class ReportController extends Controller
{
    public function sales_report(Request $request)
    {
        $report_type = $request->input('report_type', 'order_wise');
        
        if ($report_type == 'date_wise') {
            $query = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->selectRaw('
                    DATE(order_items.created_at) as order_date,
                    COUNT(DISTINCT order_items.order_id) as total_orders,
                    SUM(order_items.quantity) as total_qty,
                    SUM(order_items.total_actual_price) as sub_total,
                    SUM(orders.taxes) as tax,
                    SUM(orders.delivery_charges) as delivery_charge,
                    MAX(orders.currency_code) as currency_code
                ')
                ->groupBy('order_date');
        } else {
            // Common query parts for order_wise
            $query = OrderItem::leftJoin('vendor_payouts', 'order_items.id', '=', 'vendor_payouts.order_item_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->with(['order.user', 'vendor.country', 'order.shippingAddress.country'])
                ->selectRaw('
                    order_items.order_id,
                    order_items.vendor_id,
                    SUM(order_items.quantity) as total_qty,
                    SUM(order_items.total_actual_price) as sub_total,
                    SUM(order_items.discount) as total_discount,
                    SUM(order_items.campaign_discount * order_items.quantity) as campaign_discount,
                    MIN(order_items.created_at) as order_date,
                    MIN(order_items.status) as item_status,
                    MAX(vendor_payouts.id) as payout_id,
                    SUM(vendor_payouts.payout_amount) as total_payout,
                    MAX(orders.payment_status) as payment_status,
                    MAX(orders.status) as order_status,
                    MAX(orders.order_reference_id) as order_reference_id,
                    MAX(orders.taxes) as tax,
                    MAX(orders.delivery_charges) as delivery_charge,
                    MAX(orders.currency_code) as currency_code
                ')
                ->groupBy('order_items.order_id', 'order_items.vendor_id');
        }

        // Apply Filters
        $query = $this->applySalesReportFilters($request, $query);

        // Calculate Stats
        $stats = $this->calculateSalesStats($request);

        // Export logic
        if ($request->has('export')) {
            return $this->exportSalesReport($query->get(), $report_type);
        }

        // Pagination
        $sales = $query->orderBy('order_date', 'desc')->paginate(15)->withQueryString();

        // Transform Collection
        $this->transformSalesCollection($sales, $report_type);

        // Determine currency for stats display
        $currency = GeneralSetting::where('key', 'currency_code')->value('value');

        // If vendor is selected, use their country's currency code
        if ($request->filled('vendor_id')) {
            $vendor = User::with('country')->find($request->vendor_id);
            if ($vendor && $vendor->country) {
                $currency = $vendor->country->currency_code;
            }
        } elseif ($request->filled('country_id')) {
            // If country is selected, use that country's currency code
            $country = Country::find($request->country_id);
            if ($country) {
                $currency = $country->currency_code;
            }
        } else {
            // Get currency code from vendors country
            if ($sales->count() > 0 && $report_type == 'order_wise' && $sales->first()->vendor && $sales->first()->vendor->country) {
                $currency = $sales->first()->vendor->country->currency_code;
            }
        }

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'table' => view('backend.admin.report.partials.sales-table', compact('sales', 'report_type', 'currency'))->render(),
                'stats' => $stats,
                'currency' => $currency,
                'pagination' => $sales->links()->render(),
                'info' => 'Showing ' . ($sales->firstItem() ?? 0) . ' to ' . ($sales->lastItem() ?? 0) . ' of ' . $sales->total() . ' entries'
            ]);
        }

        $vendors = User::whereIn('role', ['1', '2'])->get();
        $countries = Country::where('is_active', 1)->get();

        return view('backend.admin.report.sales-report', compact(
            'sales',
            'stats',
            'vendors',
            'countries',
            'currency',
            'report_type'
        ));
    }

    private function calculateSalesStats(Request $request)
    {
        $query = OrderItem::query();
        
        // Re-apply common filters manually to avoid grouping/select issues
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($oq) use ($search) {
                    $oq->where('order_reference_id', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'LIKE', "%{$search}%");
                        });
                })->orWhereHas('vendor', function ($vq) use ($search) {
                    $vq->where('name', 'LIKE', "%{$search}%");
                })->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        if ($request->filled('vendor_id')) {
            $query->where('order_items.vendor_id', $request->vendor_id);
        }

        if ($request->filled('store_name')) {
            $query->whereHas('vendor', function($q) use ($request) {
                $q->where('store_name', 'like', '%' . $request->store_name . '%');
            });
        }

        if ($request->filled('country_id')) {
            $query->whereHas('order.shippingAddress', function($q) use ($request) {
                $q->where('country_id', $request->country_id);
            });
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

        $stats = $query->selectRaw('
            SUM(order_items.total_actual_price) as total_sales,
            SUM(order_items.total_actual_price - order_items.discount - order_items.campaign_discount) as total_revenue,
            SUM(CASE WHEN order_items.status IN (5, 6) THEN order_items.total_actual_price ELSE 0 END) as total_refund
        ')->first();

        return (object) [
            'formatted_total_sales' => PriceHelper::formatLargeNumber($stats->total_sales ?? 0),
            'formatted_total_revenue' => PriceHelper::formatLargeNumber($stats->total_revenue ?? 0),
            'formatted_total_refund' => PriceHelper::formatLargeNumber($stats->total_refund ?? 0),
        ];
    }

    private function transformSalesCollection($sales, $report_type = 'order_wise')
    {
        $sales->getCollection()->transform(function ($sale) use ($report_type) {
            // Priority: Vendor Country Currency -> Order currency code -> Default System Currency
            $vendorCurrency = null;
            if ($report_type == 'order_wise' && $sale->vendor && $sale->vendor->country) {
                $vendorCurrency = $sale->vendor->country->currency_code;
            }

            $sale->currency = $vendorCurrency 
                            ?? $sale->currency_code 
                            ?? GeneralSetting::where('key', 'currency_code')->value('value') 
                            ?? 'USD';
            
            // Format amounts
            $sale->formatted_amount = PriceHelper::formatLargeNumber($sale->sub_total + ($sale->tax ?? 0) + ($sale->delivery_charge ?? 0));
            $sale->formatted_sub_total = PriceHelper::formatLargeNumber($sale->sub_total);
            $sale->formatted_tax = PriceHelper::formatLargeNumber($sale->tax ?? 0);
            $sale->formatted_delivery = PriceHelper::formatLargeNumber($sale->delivery_charge ?? 0);
            $sale->formatted_discount = PriceHelper::formatLargeNumber($sale->total_discount ?? 0);
            $sale->formatted_campaign_discount = PriceHelper::formatLargeNumber($sale->campaign_discount ?? 0);
            $sale->formatted_payout = PriceHelper::formatLargeNumber($sale->total_payout ?? 0);

            return $sale;
        });
    }

    private function applySalesReportFilters(Request $request, $query)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($oq) use ($search) {
                    $oq->where('order_reference_id', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'LIKE', "%{$search}%");
                        });
                })->orWhereHas('vendor', function ($vq) use ($search) {
                    $vq->where('name', 'LIKE', "%{$search}%");
                })->orWhereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        if ($request->filled('vendor_id')) {
            $query->where('order_items.vendor_id', $request->vendor_id);
        }

        // Apply Status Filter
        if ($request->filled('status')) {
            $query->where('order_items.status', $request->status);
        } elseif (!$request->filled('search')) {
            // Default to Delivered (3) if no search or status is specified
            $query->where('order_items.status', 3);
        }

        if ($request->filled('country_id')) {
            $query->whereHas('order.shippingAddress', function($q) use ($request) {
                $q->where('country_id', $request->country_id);
            });
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('order_items.created_at', '>=', $dates[0])
                      ->whereDate('order_items.created_at', '<=', $dates[1]);
            } else {
                $query->whereDate('order_items.created_at', $dates[0]);
            }
        } elseif ($request->filled('year')) {
            $query->whereYear('order_items.created_at', $request->year);
        } else {
            // Default to current year if no date filter is applied
            $query->whereYear('order_items.created_at', date('Y'));
        }

        return $query;
    }

    private function exportSalesReport($data, $report_type = 'order_wise')
    {
        $filename = "sales_report_" . $report_type . "_" . date('Y-m-d_H-i-s') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        if ($report_type == 'date_wise') {
            $columns = ['Date', 'Total Orders', 'Qty', 'Sub Total', 'Tax', 'Delivery', 'Total'];
        } else {
            $columns = ['Payout ID', 'Order Ref', 'Customer', 'Vendor', 'Qty', 'Sub Total', 'Tax', 'Delivery', 'Coupon Discount', 'Campaign Discount', 'Payout Amount', 'Total Amount', 'Payment Status', 'Order Status', 'Date'];
        }

        $callback = function() use($data, $columns, $report_type) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $item) {
                if ($report_type == 'date_wise') {
                    fputcsv($file, [
                        \Carbon\Carbon::parse($item->order_date)->format('Y-m-d'),
                        $item->total_orders,
                        $item->total_qty,
                        number_format($item->sub_total, 2),
                        number_format($item->tax, 2),
                        number_format($item->delivery_charge, 2),
                        number_format($item->sub_total + $item->tax + $item->delivery_charge, 2),
                    ]);
                } else {
                    // Determine statuses
                    $paymentStatus = ($item->payment_status == 1 || strtolower($item->payment_status) == 'paid') ? 'Paid' : 'Unpaid';
                    $orderStatus = $this->getStatusText($item->order_status);
                    
                    // Calculate amounts (same logic as transform)
                    $subTotal = $item->sub_total;
                    $tax = $item->tax;
                    $delivery = $item->delivery_charge;
                    $couponDiscount = $item->total_discount ?? 0;
                    $campaignDiscount = $item->campaign_discount ?? 0;
                    $total = $subTotal + $tax + $delivery;
                    
                    fputcsv($file, [
                        $item->payout_id ?? 'N/A',
                        $item->order_reference_id ?? 'N/A',
                        $item->order->user->name ?? 'N/A',
                        $item->vendor->store_name ?? $item->vendor->name ?? 'N/A',
                        $item->total_qty,
                        number_format($subTotal, 2),
                        number_format($tax, 2),
                        number_format($delivery, 2),
                        number_format($couponDiscount, 2),
                        number_format($campaignDiscount, 2),
                        number_format($item->total_payout ?? 0, 2),
                        number_format($total, 2),
                        $paymentStatus,
                        $orderStatus,
                        \Carbon\Carbon::parse($item->order_date)->format('Y-m-d')
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportVendorReport($data)
    {
        $filename = "vendor_report_" . date('Y-m-d_H-i-s') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Vendor Name', 'Email', 'Store Name', 'Total Products', 'Total Orders', 'Total Sales', 'Total Discount', 'Joined Date'];

        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $vendor) {
                $stats = OrderItem::where('vendor_id', $vendor->id)
                    ->selectRaw('COUNT(DISTINCT order_id) as total_orders, SUM(total_actual_price) as total_sales, SUM(discount) as total_discount')
                    ->first();
                
                $totalProducts = Product::where('vendor_id', $vendor->id)->count();
                
                fputcsv($file, [
                    $vendor->name,
                    $vendor->email,
                    $vendor->store_name ?? 'N/A',
                    $totalProducts,
                    $stats->total_orders ?? 0,
                    number_format($stats->total_sales ?? 0, 2),
                    number_format($stats->total_discount ?? 0, 2),
                    optional($vendor->created_at)->format('Y-m-d') ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getStatusText($status)
    {
        $statuses = [
            0 => 'Pending',
            1 => 'Confirmed',
            2 => 'Shipped',
            3 => 'Delivered',
            4 => 'Cancelled',
            5 => 'Returned',
            6 => 'In Dispute'
        ];
        return $statuses[$status] ?? 'Unknown';
    }
    
    public function vendor_report(Request $request)
    {
        $query = User::select('id', 'name', 'store_name', 'email', 'country_id', 'status', 'created_at', 'image')->where('role', '2');

        // 1. Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('store_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // 2. Country Filter
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        // 3. Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 4. Date Range Filter
        if ($request->filled('date_filter')) {
            $now = now();
            if ($request->date_filter == 'week') {
                $query->where('created_at', '>=', $now->subWeek());
            } elseif ($request->date_filter == '3months') {
                $query->where('created_at', '>=', $now->subMonths(3));
            } elseif ($request->date_filter == '6months') {
                $query->where('created_at', '>=', $now->subMonths(6));
            }
        } elseif ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            } else {
                $query->whereDate('created_at', $dates[0]);
            }
        } elseif ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        // Calculate Aggregate Stats for the filtered vendors
        $vendorIds = $query->pluck('id');
        
        $reportStats = OrderItem::whereIn('vendor_id', $vendorIds)
            ->selectRaw('
                SUM(CASE WHEN status NOT IN (4, 5, 6) THEN total_actual_price ELSE 0 END) as total_sales,
                SUM(CASE WHEN status IN (5, 6) THEN total_actual_price ELSE 0 END) as total_refund,
                SUM(CASE WHEN status IN (0, 1, 2, 3) THEN total_actual_price ELSE 0 END) as total_revenue
            ')->first();

        $reportStats->formatted_total_sales = PriceHelper::formatLargeNumber($reportStats->total_sales ?? 0);
        $reportStats->formatted_total_revenue = PriceHelper::formatLargeNumber($reportStats->total_revenue ?? 0);
        $reportStats->formatted_total_refund = PriceHelper::formatLargeNumber($reportStats->total_refund ?? 0);

        // Export logic
        if ($request->has('export')) {
            return $this->exportVendorReport($query->get());
        }

        $vendors = $query->with('country')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Determine currency for stats
        $currency = GeneralSetting::where('key', 'currency_symbol')->value('value') ?? '$';
        
        // Try to get from filter
        if ($request->filled('country_id')) {
             $country = Country::find($request->country_id);
             if ($country) $currency = $country->currency_code;
        } else {
             // Try to get from first vendor in the list
             $firstVendor = $vendors->first();
             if ($firstVendor && $firstVendor->country) {
                 $currency = $firstVendor->country->currency_code;
             }
        }

        $commission_rate = GeneralSetting::where('key', 'vendor_commission')->value('value') ?? 0;

        $vendors->getCollection()->transform(function($vendor) use ($commission_rate) {
            $stats = OrderItem::where('vendor_id', $vendor->id)
                ->selectRaw('COUNT(DISTINCT order_id) as total_orders, SUM(quantity) as total_products_sold, SUM(total_actual_price) as total_sales, SUM(discount) as total_discount, MAX(created_at) as last_order_date')
                ->first();
            
            $vendor->total_products = Product::where('vendor_id', $vendor->id)->count();
            $vendor->total_orders = $stats->total_orders ?? 0;
            $vendor->total_products_sold = $stats->total_products_sold ?? 0;
            $vendor->total_sales = $stats->total_sales ?? 0;
            $vendor->total_discount = $stats->total_discount ?? 0;
            $vendor->last_order_date = $stats->last_order_date;
            
            // Calculate commission based on system rate
            $vendor->total_commission = ($vendor->total_sales * $commission_rate) / 100;
            $vendor->vendor_earnings = $vendor->total_sales - $vendor->total_commission;

            // Format amounts for view
            $vendor->formatted_total_sales = PriceHelper::formatLargeNumber($vendor->total_sales);
            $vendor->formatted_total_discount = PriceHelper::formatLargeNumber($vendor->total_discount);
            $vendor->formatted_total_commission = PriceHelper::formatLargeNumber($vendor->total_commission);

            // Calculate discount percentage
            $total_original_price = $vendor->total_sales + $vendor->total_discount;
            if ($total_original_price > 0) {
                $vendor->discount_percentage = round(($vendor->total_discount / $total_original_price) * 100, 2);
            } else {
                $vendor->discount_percentage = 0;
            }

            $vendor->image = ImageHelper::getVendorsImage($vendor->image);

            return $vendor;
        });

        $countries = Country::where('is_active', 1)->get();

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'table' => view('backend.admin.report.partials.vendor-table', compact('vendors', 'currency'))->render(),
                'reportStats' => $reportStats,
                'currency' => $currency,
                'pagination' => $vendors->links()->render(),
                'info' => 'Showing ' . ($vendors->firstItem() ?? 0) . ' to ' . ($vendors->lastItem() ?? 0) . ' of ' . $vendors->total() . ' entries'
            ]);
        }

        return view('backend/admin/report/vendor-report', compact('vendors', 'countries', 'reportStats', 'currency'));
    }

    public function kyc_report(Request $request)
    {
        $query = User::where('role', '2');

        // 1. Search Filter (Vendor Name / Email / Store Name / Phone)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('store_name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // 2. Business Name Filter (Business Name / Store Name / Tax ID)
        if ($request->filled('business_name')) {
            $business_name = $request->business_name;
            $query->where(function($q) use ($business_name) {
                $q->where('business_name', 'LIKE', "%{$business_name}%")
                  ->orWhere('store_name', 'LIKE', "%{$business_name}%")
                  ->orWhere('vendor_tax', 'LIKE', "%{$business_name}%");
            });
        }

        // 3. Status Filter (Vendor KYC Status)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 4. Date Range Filter
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            } else {
                $query->whereDate('created_at', $dates[0]);
            }
        }

        $vendors = $query->with(['documents' => function($query) {
                $query->latest();
            }])
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $vendors->getCollection()->transform(function($vendor) {
            $vendor->document_count = VendorsDocument::where('vendor_id', $vendor->id)->count();
            $vendor->last_upload = VendorsDocument::where('vendor_id', $vendor->id)->latest()->first();
            
            // Format document paths using ImageHelper
            if ($vendor->documents) {
                foreach ($vendor->documents as $doc) {
                    $doc->formatted_path = ImageHelper::getVendorDocImage($doc->document);
                }
            }
            if ($vendor->last_upload) {
                $vendor->last_upload->formatted_path = ImageHelper::getVendorDocImage($vendor->last_upload->document);
            }

            $vendor->image = ImageHelper::getVendorsImage($vendor->image);
            return $vendor;
        });

        if ($request->ajax()) {
            return response()->json([
                'table' => view('backend.admin.report.partials.kyc-table', compact('vendors'))->render(),
                'pagination' => $vendors->links()->render(),
                'info' => 'Showing ' . ($vendors->firstItem() ?? 0) . ' to ' . ($vendors->lastItem() ?? 0) . ' of ' . $vendors->total() . ' entries'
            ]);
        }

        return view('backend/admin/report/kyc-report', compact('vendors'));
    }

    public function product_report(Request $request)
    {
        $query = Product::with(['category', 'brand', 'vendor.country', 'firstVariant']);

        // 1. Search Filter (Product Name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // 2. Vendor/Admin Filter
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        } elseif ($request->filled('role')) {
            $query->whereHas('vendor', function($q) use ($request) {
                $q->where('role', $request->role);
            });
        }

        // 3. Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 4. Date Filter
        if ($request->filled('date_filter')) {
            $now = now();
            if ($request->date_filter == 'today') {
                $query->whereDate('created_at', $now);
            } elseif ($request->date_filter == '1month') {
                $query->where('created_at', '>=', $now->subMonth());
            } elseif ($request->date_filter == '3months') {
                $query->where('created_at', '>=', $now->subMonths(3));
            } elseif ($request->date_filter == '6months') {
                $query->where('created_at', '>=', $now->subMonths(6));
            }
        } elseif ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            } else {
                $query->whereDate('created_at', $dates[0]);
            }
        } elseif ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        } else {
            // Default to current year if no date filter is applied
            $query->whereYear('created_at', date('Y'));
        }

        // Export logic
        if ($request->has('export')) {
            return $this->exportProductReport($query->get());
        }

        $products = $query->paginate(15)->withQueryString();

        $products->getCollection()->transform(function($product) {
            $stats = OrderItem::where('product_id', $product->id)
                ->selectRaw('SUM(quantity) as sold_qty, SUM(total_actual_price) as total_sales, SUM(discount) as total_discount, MAX(created_at) as last_sold')
                ->first();
            
            $product->sold_qty = $stats->sold_qty ?? 0;
            $product->total_sales = $stats->total_sales ?? 0;
            $product->total_discount = $stats->total_discount ?? 0;

            // Calculate discount percentage
            $total_original_price = $product->total_sales + $product->total_discount;
            if ($total_original_price > 0) {
                $product->discount_percentage = round(($product->total_discount / $total_original_price) * 100, 2);
            } else {
                $product->discount_percentage = 0;
            }

            $product->last_sold = $stats->last_sold;

            $images = json_decode($product->firstVariant->image ?? '[]', true);
            $firstImage = (is_array($images) && count($images) > 0) ? $images[0] : '';
            $product->image = ImageHelper::getProductImage($firstImage);

            return $product;
        });

        $vendors = User::whereIn('role', ['1', '2'])->get();

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'table' => view('backend.admin.report.partials.product-table', compact('products'))->render(),
                'pagination' => $products->links()->render(),
                'info' => 'Showing ' . ($products->firstItem() ?? 0) . ' to ' . ($products->lastItem() ?? 0) . ' of ' . $products->total() . ' entries'
            ]);
        }

        return view('backend/admin/report/product-report', compact('products', 'vendors'));
    }

    private function exportProductReport($data)
    {
        $filename = "product_report_" . date('Y-m-d_H-i-s') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Product', 'SKU', 'Vendor', 'Currency', 'Category', 'Brand', 'Price', 'Sold Qty', 'Discount', 'Total Sales', 'Status', 'Last Sold'];

        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $product) {
                $stats = OrderItem::where('product_id', $product->id)
                    ->selectRaw('SUM(quantity) as sold_qty, SUM(total_actual_price) as total_sales, SUM(discount) as total_discount, MAX(created_at) as last_sold')
                    ->first();

                fputcsv($file, [
                    $product->name,
                    $product->firstVariant->sku ?? 'N/A',
                    $product->vendor->store_name ?? $product->vendor->name ?? 'N/A',
                    optional($product->vendor->country)->currency ?? 'AED',
                    $product->category->name ?? 'N/A',
                    $product->brand->name ?? 'N/A',
                    number_format($product->firstVariant->price ?? 0, 2),
                    $stats->sold_qty ?? 0,
                    number_format($stats->total_discount ?? 0, 2),
                    number_format($stats->total_sales ?? 0, 2),
                    $product->status == 1 ? 'Active' : 'Inactive',
                    $stats->last_sold ? \Carbon\Carbon::parse($stats->last_sold)->format('Y-m-d') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}
