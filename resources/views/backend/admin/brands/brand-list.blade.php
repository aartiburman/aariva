@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 text-dark fw-bold">{{ __('messages.brand_list') }}</h4>
                </div>
            </div>
        </div>

        <!-- Featured Brands Stats Cards -->
        <div class="row mb-4">
            @foreach($featured_brands as $f_brand)
            @php
                $bg_subtle_colors = ['bg-primary-subtle', 'bg-primary-subtle', 'bg-warning-subtle', 'bg-danger-subtle'];
                $text_colors = ['text-primary', 'text-primary', 'text-warning', 'text-danger'];
                $idx = $loop->index % 4;
            @endphp
            <div class="col-md-3">
                <div class="card border-0 shadow-sm overflow-hidden {{ $bg_subtle_colors[$idx] }}">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-lg  p-1">
                                <img src="{{ $f_brand->logo }}" alt="{{ $f_brand->name }}" class="img-fluid" style="width: 60px; height: 60px; object-fit: cover;">
                            </div>
                            <div>
                                <h5 class="fs-14 mb-1 fw-bold {{ $text_colors[$idx] }} text-truncate" style="max-width: 120px;">{{ $f_brand->name }}</h5>
                                <p class="mb-0 fs-12 text-muted">Brand</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Filter and Search Row -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <form action="{{ route('brand.list') }}" method="POST" id="brand-filter-form" class="no-loader">
                    @csrf
                    <div class="row align-items-end g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-13 mb-1">Brand Name</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" id="brand-search" class="form-control" placeholder="Search brand..." value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-13 mb-1">Date Range</label>
                            <input type="text" name="date_range" class="form-control form-control-sm range-datepicker" autocomplete="off" placeholder="Select Date Range" value="{{ request('date_range') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold fs-13 mb-1">Status</label>
                            <select name="is_active" class="form-select form-select-sm" onchange="$('#brand-filter-form').submit()">
                                <option value="">All Status</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <a href="{{ route('brand.list') }}" class="btn btn-sm btn-primary w-100">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Brands Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <h4 class="card-title mb-0">{{ __('messages.brand_list') }}</h4>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-danger d-none" id="bulk-delete-btn">
                        <iconify-icon icon="solar:trash-bin-trash-linear" class="align-middle me-1"></iconify-icon> Bulk Delete
                    </button>
                     <a href="{{ route('export.brands', request()->all()) }}" class="btn btn-sm btn-outline-info d-none no-loader" id="export-brands-btn">
                        <iconify-icon icon="solar:download-linear" class="align-middle me-1"></iconify-icon> Export
                    </a>
                    <a href="{{ route('add.brand') }}" class="btn btn-sm btn-primary">
                        <iconify-icon icon="solar:add-circle-linear" class="align-middle me-1"></iconify-icon>
                        {{ __('messages.add_brand') }}
                    </a> 
                   
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-4" style="width: 50px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="brandCheckAll">
                                    </div>
                                </th>
                                <th>{{ __('messages.brand') }}</th>
                                <th>Slug</th>
                                <th>Created By</th>
                                <th>ID</th>
                                <th>{{ __('messages.products') }}</th>
                                <th>Status</th>
                                <th class="text-end pe-4">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('backend.admin.brands.brand-table')
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer border-top-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <p class="text-muted mb-0 fs-13">Showing {{ $brands->firstItem() }} to {{ $brands->lastItem() }} of {{ $brands->total() }} brands</p>
                    <div class="pagination-container">
                        {{ $brands->appends(request()->query())->links() }}
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
        initDateRangePicker('.range-datepicker');
        initAjaxFilter('#brand-filter-form', 'tbody', '.card-footer p', 'brands');
        initBulkDelete('.row-checkbox', '#bulk-delete-btn', "{{ route('bulk.delete.brand') }}");

        // Brand CheckAll Change
        $(document).on('change', '#brandCheckAll', function() {
            $('.row-checkbox').prop('checked', $(this).prop('checked')).trigger('change');
        });

        // Row Checkbox Change
        $(document).on('change', '.row-checkbox', function() {
            var total = $('.row-checkbox').length;
            var checked = $('.row-checkbox:checked').length;
            $('#brandCheckAll').prop('checked', total === checked);

            if (checked > 0) {
                $('#bulk-delete-btn').removeClass('d-none');
                $('#export-brands-btn').removeClass('d-none');
            } else {
                $('#bulk-delete-btn').addClass('d-none');
                $('#export-brands-btn').addClass('d-none');
            }
        });
    });
</script>
@endpush
