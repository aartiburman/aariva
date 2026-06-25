@extends('backend.layouts.app')

@section('content')

@php
$formatPrice = function($amount) {
$amount = (float) $amount;
if ($amount >= 10000000) {
return number_format($amount / 10000000, 2) . ' Cr';
} elseif ($amount >= 100000) {
return number_format($amount / 100000, 2) . ' Lakh';
} elseif ($amount >= 1000) {
return number_format($amount / 1000, 2) . ' K';
}
return number_format($amount, 2);
};
@endphp

<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-12">
                <h4 class="fw-bold mb-0">Product Report</h4>
            </div>

        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('product.report') }}" method="GET" class="row g-2 align-items-end">
                            <button type="submit" style="display: none;"></button>
                            <div class="col-xl col-md-4 col-sm-6">
                                <label class="form-label fw-semibold fs-13 mb-1">Search</label>
                                <div class="position-relative">
                                    <input type="text" name="search" class="form-control form-control-sm ps-3 pe-5 py-2" placeholder="Search..." value="{{ request('search') }}">
                                    <iconify-icon icon="solar:magnifer-linear" class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></iconify-icon>
                                </div>
                            </div>
                            <div class="col-xl col-md-4 col-sm-6">
                                <label class="form-label fw-semibold fs-13 mb-1">Vendor</label>
                                <select name="vendor_id" class="form-select form-select-sm py-2 select2" onchange="this.form.submit()">
                                    <option value="">Vendor</option>
                                    @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl col-md-4 col-sm-6">
                                <label class="form-label fw-semibold fs-13 mb-1">Date Range</label>
                                <div class="position-relative">
                                    <input type="text" name="date_range" class="form-control form-control-sm ps-3 pe-5 py-2 range-datepicker" autocomplete="off" placeholder="Date Range" value="{{ request('date_range') }}">
                                    <iconify-icon icon="solar:calendar-linear" class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></iconify-icon>
                                </div>
                            </div>
                            <div class="col-xl col-md-4 col-sm-6">
                                <label class="form-label fw-semibold fs-13 mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm py-2" onchange="this.form.submit()">
                                    <option value="">Status</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-xl-auto col-md-12 d-flex gap-2">
                                <button type="submit" name="export" value="1" class="btn btn-sm btn-primary py-2 px-3">
                                    Export
                                </button>
                                <a href="{{ route('product.report') }}" class="btn btn-sm btn-outline-secondary py-2 px-3">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center g-2">
                            <div class="col-lg-2">
                                <h4 class="card-title">Product Report</h4>
                            </div>

                        </div>
                    </div>

                    <div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th class="ps-4">Product</th>
                                        <th>SKU</th>
                                        <th>Vendor</th>
                                        <th>Category</th>
                                        <th>Brand</th>
                                        <th>Price</th>
                                        <th>Sold Qty</th>
                                        <th>Discount</th>
                                        <th class="text-nowrap">Total Sales</th>
                                        <th class="text-center">Status</th>
                                        <th class="pe-4 text-end">Last Sold</th>
                                        <!-- <th class="pe-4 text-end">Action</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($products as $product)
                                    @php
                                    $price = $product->firstVariant->price ?? 0;
                                    $currency = optional(optional($product->vendor)->country)->currency_code ?? 'AED';
                                    @endphp
                                    <tr>
                                        <td class="ps-4 fs-13 fw-medium text-dark text-nowrap">
                                            <a href="{{ url('product-detail/' . $product->id) }}">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <img src="{{ $product->image }}" alt="" class="img-fluid rounded">
                                                </div>
                                                <div>{{ \Illuminate\Support\Str::limit($product->name, 10, '..') }}</div>
                                            </div>
                                            </a>
                                        </td>
                                        <td class="fs-13 text-muted text-nowrap">{{ $product->firstVariant->sku ?? 'N/A' }}</td>
                                        <td class="fs-13 text-muted text-nowrap">{{ $product->vendor->store_name ?? $product->vendor->name ?? 'N/A' }}</td>
                                        <td class="fs-13 text-muted text-nowrap">{{ $product->category->name ?? 'N/A' }}</td>
                                        <td class="fs-13 text-muted text-nowrap">{{ $product->brand->name ?? 'N/A' }}</td>
                                        <td class="fs-13 text-nowrap">{{ $currency }} {{ $formatPrice($price) }}</td>
                                        <td class="fs-13 text-center">{{ $product->sold_qty }}</td>
                                        <td class="fs-13 text-nowrap">
                                            {{ $formatPrice($product->total_discount) }}
                                            @if($product->discount_percentage > 0)
                                                <small class="text-danger d-block">({{ $product->discount_percentage }}% Off)</small>
                                            @endif
                                        </td>
                                        <td class="fs-13 fw-bold text-dark text-nowrap">{{ $currency }} {{ $formatPrice($product->total_sales) }}</td>
                                        <td class="text-center">
                                            @if($product->status == 1)
                                            <span class="badge bg-success-subtle text-success px-2 py-1 text-uppercase">Active</span>
                                            @else
                                            <span class="badge bg-danger-subtle text-danger px-2 py-1 text-uppercase">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="fs-13 text-muted text-nowrap">{{ $product->last_sold ? \Carbon\Carbon::parse($product->last_sold)->format('Y-m-d') : 'N/A' }}</td>
                                        <!-- <td class="pe-4 text-end">
                                            <a href="#" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="View Detail">
                                                <iconify-icon icon="solar:eye-linear" class="fs-20"></iconify-icon>
                                            </a>
                                        </td> -->
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="12" class="text-center py-5 text-muted">
                                            <iconify-icon icon="solar:box-linear" class="fs-48 mb-2 d-block mx-auto opacity-25"></iconify-icon>
                                            No product records found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="card-footer border-top">
                        {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>



    @endsection



