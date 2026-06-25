@extends('backend.layouts.app')

@section('content')

<div class="page-content">
    <!-- Start Container Fluid -->
    <div class="container-fluid">
        <!-- Featured Categories Stats Cards -->
        <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 mb-4">
            <div class="col">
                <a href="{{ route('product.list') }}" class="text-decoration-none h-100">
                    <div class="card border-0 shadow-sm h-100 mb-0">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 text-uppercase fs-11 fw-bold">Total Products</p>
                                    <h2 class="text-dark mb-2 fw-bold" id="total-products-count">{{ $statusCounts->total ?? 0 }}</h2>
                                    <div class="mt-4">
                                        <span class="text-muted fs-11">All Products</span>
                                    </div>
                                </div>
                                <div class="avatar-lg bg-soft-primary d-flex align-items-center justify-content-center rounded-circle">
                                    <iconify-icon icon="solar:box-minimalistic-linear" class="fs-32 text-primary"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('product.list', ['status' => 0]) }}" class="text-decoration-none h-100">
                    <div class="card border-0 shadow-sm h-100 mb-0">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 text-uppercase fs-11 fw-bold">Pending Products</p>
                                    <h2 class="text-dark mb-2 fw-bold" id="pending-products-count">{{ $statusCounts->pending ?? 0 }}</h2>
                                    <div class="mt-4">
                                        <span class="text-warning fs-11 fw-medium">Requires Action</span>
                                    </div>
                                </div>
                                <div class="avatar-lg bg-soft-warning d-flex align-items-center justify-content-center rounded-circle">
                                    <iconify-icon icon="solar:hourglass-linear" class="fs-32 text-warning"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('product.list', ['status' => 1]) }}" class="text-decoration-none h-100">
                    <div class="card border-0 shadow-sm h-100 mb-0">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 text-uppercase fs-11 fw-bold">Approved Products</p>
                                    <h2 class="text-dark mb-2 fw-bold" id="approved-products-count">{{ $statusCounts->approved ?? 0 }}</h2>
                                    <div class="mt-4">
                                        <span class="text-success fs-11">Currently Active</span>
                                    </div>
                                </div>
                                <div class="avatar-lg bg-soft-success d-flex align-items-center justify-content-center rounded-circle">
                                    <iconify-icon icon="solar:verified-check-linear" class="fs-32 text-success"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col">
                <a href="{{ route('product.list', ['status' => 2]) }}" class="text-decoration-none h-100">
                    <div class="card border-0 shadow-sm h-100 mb-0">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 text-uppercase fs-11 fw-bold">Rejected Products</p>
                                    <h2 class="text-dark mb-2 fw-bold" id="rejected-products-count">{{ $statusCounts->rejected ?? 0 }}</h2>
                                    <div class="mt-4">
                                        <span class="text-danger fs-11">Policy Violations</span>
                                    </div>
                                </div>
                                <div class="avatar-lg bg-soft-danger d-flex align-items-center justify-content-center rounded-circle">
                                    <iconify-icon icon="solar:forbidden-circle-linear" class="fs-32 text-danger"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Filter and Search Row -->
        <div class=" {{ (request('search') || request('date_range') || request('status') !== null) ? 'show' : '' }}">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <form action="{{ route('product.list') }}" method="POST" id="filter-form" class="no-loader">
                        @csrf
                        <div class="row align-items-end g-2">
                            @if(Auth::user()->role == '1')
                            <div class="col-md-3">
                                @else
                                <div class="col-md-4">
                                    @endif
                                    <label class="form-label fw-semibold fs-13 mb-1">Product Name</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="search" id="product-search" class="form-control" placeholder="Search..." value="{{ request('search') }}" data-trigger="blur">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                                        </button>
                                    </div>
                                </div>
                                @if(Auth::user()->role == '1')
                            <div class="col-md-2">
                                @else
                                <div class="col-md-3">
                                    @endif
                                    <label class="form-label fw-semibold fs-13 mb-1">Date Range</label>
                                    <div class="position-relative">
                                        <input type="text" name="date_range" class="form-control form-control-sm ps-3 pe-5 range-datepicker" autocomplete="off" placeholder="Filter by date range" value="{{ request('date_range') }}">
                                        <iconify-icon icon="solar:calendar-linear" class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></iconify-icon>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold fs-13 mb-1">Brand</label>
                                    <select name="brand_id" class="form-select form-select-sm select2">
                                        <option value="">All Brands</option>
                                        @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if(Auth::user()->role == '1')
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold fs-13 mb-1">Store Name</label>
                                    <select name="vendor_id" class="form-select form-select-sm py-2 select2">
                                        <option value="">All Stores</option>
                                        @foreach($all_vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->store_name ?? $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold fs-13 mb-1">Status</label>
                                    <select name="status" class="form-select form-select-sm py-2">
                                        <option value="">All Status</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Approved</option>
                                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <a href="{{ route('product.list') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                                </div>
                            </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-1">
                        <h4 class="card-title flex-grow-1">All Product List</h4>

                        <div class="d-flex align-items-center gap-2">
                            @if(Auth::user()->role == '1')
                            <button type="button" id="product_bulk_active_btn" class="btn btn-sm btn-outline-success d-none">
                                <iconify-icon icon="solar:check-circle-linear" class="align-middle me-1"></iconify-icon> Bulk Active
                            </button>
                            <button type="button" id="product_bulk_deactive_btn" class="btn btn-sm btn-outline-warning d-none">
                                <iconify-icon icon="solar:close-circle-linear" class="align-middle me-1"></iconify-icon> Bulk Deactive
                            </button>
                            @endif
                            <button type="button" id="product_export_btn" class="btn btn-sm btn-outline-secondary  d-none">
                                <iconify-icon icon="solar:download-linear" class="align-middle me-1"></iconify-icon> Export
                            </button>
                            <button class="btn btn-sm btn-outline-danger d-none" id="bulk-delete-btn">
                                <iconify-icon icon="solar:trash-bin-trash-linear" class="align-middle me-1"></iconify-icon> Bulk Delete
                            </button>
                            <a href="{{ route('add.product') }}" class="btn btn-sm btn-primary">
                                <iconify-icon icon="solar:add-circle-linear" class="align-middle me-1"></iconify-icon> Add Product
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th style="width: 20px;">
                                            <div class="form-check ms-1">
                                                <input type="checkbox" class="form-check-input" id="productCheckAll">
                                                <label class="form-check-label" for="productCheckAll"></label>
                                            </div>
                                        </th>
                                        <th>Product Name & Size</th>
                                        <th>Category</th>
                                        <th>Sub Category</th>
                                        <th>Child Category</th>
                                        <th>Brand</th>
                                        <th>Vendor</th>
                                        <th>Variant Count</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @include('backend.admin.product.product-table')
                                </tbody>
                            </table>
                        </div>
                        <!-- end table-responsive -->
                    </div>
                    <div class="card-footer p-4">
                        <div class="row align-items-center">
                            @php
                                $isFiltered = request()->filled('search') || 
                                              request()->filled('date_range') || 
                                              request()->filled('brand_id') || 
                                              request()->filled('vendor_id') || 
                                              request()->filled('status');
                            @endphp

                          

                            <div class="col">
                                @if($isFiltered == 1)
                                    <p class="text-muted mb-0 fs-13">Showing all {{ $products->total() }} products</p>
                                @else
                                    <p class="text-muted mb-0 fs-13">Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products</p>
                                @endif
                            </div>
                            
                            @if(!$isFiltered && $products->hasPages())
                            <div class="col-auto">
                                {{ $products->appends(request()->query())->links() }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
    <!-- End Container Fluid -->

    @endsection

    @include('backend.admin.product.partials.discount-validation')



    @push('scripts')
    <script>
        $(document).ready(function() {
            if (typeof initProductListPage === 'function') {
                initProductListPage({
                    filterFormSelector: '#filter-form',
                    tableBodySelector: 'tbody',
                    countSelector: '.card-footer p',
                    bulkDeleteUrl: "{{ route('bulk.delete.product') }}",
                    bulkStatusUrl: "{{ route('bulk.product.status') }}",
                    exportUrl: "{{ route('export.products') }}"
                });
            }
        });
    </script>
@endpush
