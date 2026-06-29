@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Stock Movements</h4>
                    <div class="page-title-right">
                        <a href="{{ route('inventory.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                            <iconify-icon icon="solar:alt-arrow-left-linear" class="fs-18"></iconify-icon> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" id="filter-form" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Stock In</option>
                            <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Stock Out</option>
                            <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Range</label>
                        <input type="text" name="date_range" class="form-control" id="date_range" value="{{ request('date_range') }}" placeholder="Select date range">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search product..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Product / Variant</th>
                                <th>Warehouse</th>
                                <th>Qty</th>
                                <th>Before</th>
                                <th>After</th>
                                <th>Reason</th>
                                <th>By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            @include('backend.admin.inventory.partials.movements-table')
                        </tbody>
                    </table>
                </div>
            </div>
            @if($movements->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $movements->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    if (typeof initAjaxFilter === 'function') {
        initAjaxFilter('#filter-form', '#table-body', null, 'movements');
    }

    if ($('#date_range').length && typeof moment !== 'undefined') {
        $('#date_range').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
        });
        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
        });
        $('#date_range').on('cancel.daterangepicker', function() { $(this).val(''); });
    }
});
</script>
@endpush
