@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:bag-check-bold" class="fs-24"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">{{ $currency }} {{ $stats->formatted_total_sales }}</h4>
                                <p class="mb-0 text-muted fs-13">Total Sales</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md bg-success text-white rounded-circle d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:wallet-money-bold" class="fs-24"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">{{ $currency }} {{ $stats->formatted_total_revenue }}</h4>
                                <p class="mb-0 text-muted fs-13">Revenue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md bg-danger text-white rounded-circle d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:bill-cross-bold" class="fs-24"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">{{ $currency }} {{ $stats->formatted_total_refund }}</h4>
                                <p class="mb-0 text-muted fs-13">Refund</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card shadow-sm">
                    <div class="card-body p-4 mb-3">
                        <form action="{{ url()->current() }}" method="POST" id="sales-report-filter-form">
                            @csrf
                            <button type="submit" style="display: none;"></button>
                            <div class="row g-3 align-items-end">
                                <div class="col-xl-3 col-md-4 col-sm-6">
                                    <label class="form-label fw-bold text-uppercase fs-12 mb-2">Status</label>
                                    <select name="status" class="form-select form-select-sm py-2">
                                        <option value="">All Status</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Shipped</option>
                                        <option value="3" {{ request('status') === '3' || (!request()->has('status') && !request()->has('search')) ? 'selected' : '' }}>Delivered</option>
                                        <option value="4" {{ request('status') === '4' ? 'selected' : '' }}>Cancelled</option>
                                        <option value="5" {{ request('status') === '5' ? 'selected' : '' }}>Returned</option>
                                        <option value="6" {{ request('status') === '6' ? 'selected' : '' }}>In Dispute</option>
                                    </select>
                                </div>

                                <div class="col-xl-3 col-md-4 col-sm-6">
                                    <label class="form-label fw-bold text-uppercase fs-12 mb-2">Report Type</label>
                                    <select name="report_type" class="form-select form-select-sm py-2">
                                        <option value="order_wise" {{ request('report_type') == 'order_wise' ? 'selected' : '' }}>Order-wise</option>
                                        <option value="date_wise" {{ request('report_type') == 'date_wise' ? 'selected' : '' }}>Date-wise</option>
                                    </select>
                                </div>
                               
                                <div class="col-xl-4 col-md-4 col-sm-6">
                                    <label class="form-label fw-bold text-uppercase fs-12 mb-2">Date Range</label>
                                    <div class="position-relative">
                                        <input type="text" name="date_range" class="form-control form-control-sm ps-3 pe-5 py-2 range-datepicker" autocomplete="off" placeholder="Date Range" value="{{ request('date_range') }}">
                                        <iconify-icon icon="solar:calendar-linear" class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></iconify-icon>
                                    </div>
                                </div>

                                <div class="col-xl-2 col-md-12 d-flex gap-2 justify-content-end">
                                    <a href="{{ route('sales.report') }}" class="btn btn-sm btn-outline-secondary py-2 px-3 d-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:restart-linear"></iconify-icon> Reset
                                    </a>
                                    <button type="button" id="export-sales-report" class="btn btn-sm btn-primary py-2 px-3 no-loader d-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:download-linear"></iconify-icon> Export Data
                                    </button>
                                </div>

                                <div class="col-xl-4 col-md-4 col-sm-6">
                                    <label class="form-label fw-semibold fs-13 mb-1">Search By Order Reference Id</label>
                                    <div class="position-relative">
                                        <input type="text" name="search" id="sales-report-search" class="form-control form-control-sm ps-3 pe-5 py-2" placeholder="Enter Order Reference Id....." value="{{ request('search') }}">
                                        <iconify-icon icon="solar:magnifer-linear" class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted" id="search-icon"></iconify-icon>
                                        <iconify-icon icon="solar:close-circle-linear" class="position-absolute top-50 end-0 translate-middle-y me-3 text-danger cursor-pointer" id="clear-search" style="display: {{ request('search') ? 'block' : 'none' }};"></iconify-icon>
                                    </div>
                                </div>

                                <div class="col-xl-4 col-md-4 col-sm-6">
                                    <label class="form-label fw-semibold fs-13 mb-1">Store Name</label>
                                    <select name="vendor_id" class="form-select form-select-sm py-2 select2">
                                        <option value="">All Store</option>
                                        @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->store_name ?? $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-xl-4 col-md-4 col-sm-6">
                                    <label class="form-label fw-semibold fs-13 mb-1">Country</label>
                                    <select name="country_id" class="form-select form-select-sm py-2 select2">
                                        <option value="">All Country</option>
                                        @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ request('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    @if($report_type == 'date_wise')
                                    <tr>
                                        <th class="ps-4 py-3 text-muted fw-semibold fs-13">Date</th>
                                        <th class="py-3 text-muted fw-semibold fs-13 text-center">Total Orders</th>
                                        <th class="py-3 text-muted fw-semibold fs-13 text-center">Qty</th>
                                        <th class="py-3 text-muted fw-semibold fs-13 text-end">Sub Total</th>
                                        <th class="py-3 text-muted fw-semibold fs-13 text-end">Tax</th>
                                        <th class="py-3 text-muted fw-semibold fs-13 text-end">Delivery</th>
                                        <th class="py-3 text-muted fw-semibold fs-13 text-end">Total</th>
                                    </tr>
                                    @else
                                    <tr>
                                        <th class="ps-4 py-3 text-muted fw-semibold fs-13">Payout ID</th>
                                        <th class="py-3 text-muted fw-semibold fs-13">Order Ref</th>
                                        <th class="py-3 text-muted fw-semibold fs-13">Customer</th>
                                        <th class="py-3 text-muted fw-semibold fs-13 text-center">Amount Details</th>
                                        <th class="py-3 text-muted fw-semibold fs-13 text-end">Total Amount</th>
                                        <th class="py-3 text-muted fw-semibold fs-13 text-center">Payment</th>
                                        <th class="py-3 text-muted fw-semibold fs-13 text-center">Status</th>
                                        <th class="pe-4 py-3 text-muted fw-semibold fs-13 text-end">Date</th>
                                    </tr>
                                    @endif
                                </thead>
                                <tbody>
                                    @forelse($sales as $sale)
                                        @if($report_type == 'date_wise')
                                            @include('backend.admin.report.partials.date-wise-row', ['sale' => $sale, 'currency' => $currency])
                                        @else
                                            @include('backend.admin.report.partials.sales-row', ['sale' => $sale, 'currency' => $sale->currency])
                                        @endif
                                    @empty
                                    <tr>
                                        <td colspan="{{ $report_type == 'date_wise' ? 7 : 8 }}" class="text-center py-5">
                                            <div class="text-muted">
                                                <iconify-icon icon="solar:clipboard-list-linear" class="fs-48 mb-3 opacity-25"></iconify-icon>
                                                <p class="fs-14">No sales records found</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer p-4">
                        <div class="row align-items-center">
                            <div class="col-sm-6 text-center text-sm-start mb-3 mb-sm-0">
                                <p class="text-muted mb-0 fs-13">Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} entries</p>
                            </div>
                            <div class="col-sm-6">
                                <div class="pagination-container d-flex justify-content-center justify-content-sm-end">
                                    {{ $sales->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        if (typeof initSalesReport === 'function') {
            initSalesReport();
        }
    });
</script>
@endpush

