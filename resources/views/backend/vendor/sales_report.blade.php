@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Sales Report</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Vendor</a></li>
                            <li class="breadcrumb-item active">Sales Report</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:wallet-linear" class="fs-24"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">{{ $currency }}{{ \App\Helpers\PriceHelper::formatLargeNumber($totalSales) }}</h4>
                                <p class="mb-0 text-muted fs-13">Total Sales</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md bg-success text-white rounded-circle d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:cart-check-linear" class="fs-24"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">{{ \App\Helpers\PriceHelper::formatLargeNumber($ordersCount) }}</h4>
                                <p class="mb-0 text-muted fs-13">Total Orders</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md bg-info text-white rounded-circle d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:tag-linear" class="fs-24"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">{{ $currency }}{{ \App\Helpers\PriceHelper::formatLargeNumber($avgOrderValue) }}</h4>
                                <p class="mb-0 text-muted fs-13">Avg Order Value</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-purple-subtle border-0 h-100" style="background-color: rgba(93, 26, 143, 0.1);">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md bg-purple text-white rounded-circle d-flex align-items-center justify-content-center" style="background-color: #5d1a8f !important;">
                                <iconify-icon icon="solar:bank-note-linear" class="fs-24"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">{{ $currency }}{{ \App\Helpers\PriceHelper::formatLargeNumber($netEarnings) }}</h4>
                                <p class="mb-0 text-muted fs-13">Net Earnings</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
            <div class="card-body p-3">
                <form action="{{ route('vendor.sales.report') }}" method="POST" id="vendor-sales-report-filter-form">
                    @csrf
                    <div class="row g-3 align-items-end">
                   
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small mb-1 text-uppercase">Status</label>
                            <select name="status" class="form-select shadow-sm py-2">
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
                             <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small mb-1 text-uppercase">Report Type</label>
                            <select name="report_type" class="form-select shadow-sm py-2">
                                <option value="order_wise" {{ request('report_type') == 'order_wise' ? 'selected' : '' }}>Order-wise</option>
                                <option value="date_wise" {{ request('report_type') == 'date_wise' ? 'selected' : '' }}>Date-wise</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small mb-1 text-uppercase">Date Range</label>
                            <div class="position-relative">
                                <input type="text" name="date_range" class="form-control range-datepicker shadow-sm py-2" autocomplete="off" placeholder="Select Date Range" value="{{ request('date_range') }}">
                                <iconify-icon icon="solar:calendar-linear" class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></iconify-icon>
                            </div>
                        </div>
                        <div class="col-md-auto ms-auto">
                            <div class="d-flex gap-4">
                                <a href="{{ route('vendor.sales.report') }}" class="btn btn-white border shadow-sm px-3 py-2 text-muted" title="Reset Filters">
                                    <iconify-icon icon="solar:refresh-linear" class="align-middle me-1"></iconify-icon> Reset
                                </a>
                                <button type="submit" name="export" value="1" class="btn btn-primary shadow-sm px-4 py-2 no-loader">
                                    <iconify-icon icon="solar:download-linear" class="align-middle me-1"></iconify-icon> Export Data
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header border-0 py-3">
                <h5 class="mb-0 fw-bold" id="table-title">{{ $report_type == 'date_wise' ? 'Daily Sales Summary' : 'Recent Transactions' }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase" id="table-head">
                            @if($report_type == 'date_wise')
                            <tr>
                                <th class="ps-4 border-0">Date</th>
                                <th class="border-0 text-center">Total Orders</th>
                                <th class="border-0 text-center">Qty Sold</th>
                                <th class="border-0 text-end">Sub Total</th>
                                <th class="border-0 text-end">Tax</th>
                                <th class="border-0 text-end">Delivery</th>
                                <th class="border-0 text-end pe-4">Total Amount</th>
                            </tr>
                            @else
                            <tr>
                                <th class="ps-4 border-0">Order ID</th>
                                <th class="border-0">Product</th>
                                <th class="border-0">Customer</th>
                                <th class="border-0">Amount</th>
                                <th class="border-0">Status</th>
                                <th class="border-0 pe-4">Date</th>
                            </tr>
                            @endif
                        </thead>
                        <tbody>
                            @include('backend.vendor.partials.sales-report-table')
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-0 py-4">
                <div class="row align-items-center">
                    <div class="col-sm-6 text-center text-sm-start mb-3 mb-sm-0">
                        <p class="text-muted mb-0 fs-13" id="pagination-info">Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries</p>
                    </div>
                    <div class="col-sm-6">
                        <div class="pagination-container d-flex justify-content-center justify-content-sm-end" id="pagination-links">
                            {{ $transactions->links('pagination::bootstrap-5') }}
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
        const filterForm = $('#vendor-sales-report-filter-form');
        const tableBody = $('tbody');
        const statsCards = $('.card.bg-primary-subtle, .card.bg-success-subtle, .card.bg-info-subtle, .card.bg-purple-subtle');
        const currency = "{{ $currency }}";

        function fetchVendorSalesReport(url = null) {
            const formData = filterForm.serialize();
            $.ajax({
                url: url || filterForm.attr('action'),
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    tableBody.html('<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
                },
                success: function(response) {
                    if (response.table !== undefined) {
                        tableBody.html(response.table);
                        $('#pagination-links').html(response.pagination);
                        $('#pagination-info').text(response.info);

                        // Update table head and title based on report type
                        const reportType = filterForm.find('select[name="report_type"]').val();
                        if (reportType === 'date_wise') {
                            $('#table-title').text('Daily Sales Summary');
                            $('#table-head').html(`
                                <tr>
                                    <th class="ps-4 border-0">Date</th>
                                    <th class="border-0 text-center">Total Orders</th>
                                    <th class="border-0 text-center">Qty Sold</th>
                                    <th class="border-0 text-end">Sub Total</th>
                                    <th class="border-0 text-end">Tax</th>
                                    <th class="border-0 text-end">Delivery</th>
                                    <th class="border-0 text-end pe-4">Total Amount</th>
                                </tr>
                            `);
                        } else {
                            $('#table-title').text('Recent Transactions');
                            $('#table-head').html(`
                                <tr>
                                    <th class="ps-4 border-0">Order ID</th>
                                    <th class="border-0">Product</th>
                                    <th class="border-0">Customer</th>
                                    <th class="border-0">Amount</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0 pe-4">Date</th>
                                </tr>
                            `);
                        }

                        // Update stats cards
                        if (response.stats) {
                            $(statsCards[0]).find('h4').text(currency + response.stats.totalSales);
                            $(statsCards[1]).find('h4').text(response.stats.ordersCount);
                            $(statsCards[2]).find('h4').text(currency + response.stats.avgOrderValue);
                            $(statsCards[3]).find('h4').text(currency + response.stats.netEarnings);
                        }
                    } else {
                        location.reload();
                    }
                },
                error: function() {
                    location.reload();
                }
            });
        }

        filterForm.on('submit', function(e) {
            e.preventDefault();
            fetchVendorSalesReport();
        });

        filterForm.find('select').on('change', function() {
            fetchVendorSalesReport();
        });

        $('.range-datepicker').on('change', function() {
            fetchVendorSalesReport();
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            fetchVendorSalesReport($(this).attr('href'));
        });

        filterForm.find('button[name="export"]').on('click', function(e) {
            e.preventDefault();
            const action = filterForm.attr('action');
            const exportForm = $('<form action="' + action + '" method="POST" style="display:none;"></form>');
            exportForm.append('@csrf');
            filterForm.find('input, select').each(function() {
                if ($(this).attr('name')) {
                    exportForm.append($('<input>').attr({
                        type: 'hidden',
                        name: $(this).attr('name'),
                        value: $(this).val()
                    }));
                }
            });
            exportForm.append($('<input>').attr({type: 'hidden', name: 'export', value: '1'}));
            exportForm.appendTo('body');
            exportForm.submit();
            exportForm.remove();
        });
    });
</script>
@endpush