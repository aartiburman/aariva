@extends('backend.layouts.app')

@section('content')

<div class="page-content">
    <div class="container-fluid">
        <!-- Stats Cards -->
        <div class="row mb-4 mt-4">
            <div class="col-md-4 ">
                <div class="card bg-primary-subtle border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:bag-check-bold" class="fs-24"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">{{ $currency }} {{ $reportStats->formatted_total_sales }}</h4>
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
                                <h4 class="mb-0 fw-bold text-dark">{{ $currency }} {{ $reportStats->formatted_total_revenue }}</h4>
                                <p class="mb-0 text-muted fs-13">Total Revenue</p>
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
                                <h4 class="mb-0 fw-bold text-dark">{{ $currency }} {{ $reportStats->formatted_total_refund }}</h4>
                                <p class="mb-0 text-muted fs-13">Total Refund</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Vendors Report</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <form action="{{ route('vendor.report') }}" method="POST" id="vendor-report-filter-form">
                            @csrf
                            <button type="submit" style="display: none;"></button>
                            <div class="row align-items-end g-2">
                                <div class="col-xl col-md-4 col-sm-6">
                                    <label class="form-label fw-semibold fs-13 mb-1">Search</label>
                                    <div class="position-relative">
                                        <input type="text" name="search" id="vendor-report-search" class="form-control form-control-sm ps-3 pe-5 py-2" placeholder="Search..." value="{{ request('search') }}">
                                        <iconify-icon icon="solar:magnifer-linear" class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></iconify-icon>
                                    </div>
                                </div>
                                <div class="col-xl col-md-4 col-sm-6">
                                    <label class="form-label fw-semibold fs-13 mb-1">Country</label>
                                    <select name="country_id" class="form-select form-select-sm py-2 select2">
                                        <option value="">Country</option>
                                        @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ request('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
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
                                    <select name="status" class="form-select form-select-sm py-2">
                                        <option value="">Status</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-xl-auto col-md-12 d-flex gap-2">
                                    <button type="submit" name="export" value="1" class="btn btn-sm btn-primary py-2 px-3 no-loader">
                                        Export
                                    </button>
                                    <a href="{{ route('vendor.report') }}" class="btn btn-sm btn-outline-secondary py-2 px-3">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th class="ps-4 py-3 text-muted fw-semibold fs-13">Vendor</th>
                                        <th class="py-3 text-muted fw-semibold fs-13">Contact</th>
                                        <th class="py-3 text-muted fw-semibold fs-13">Location</th>
                                        <th class="py-3 text-muted fw-semibold fs-13 text-center">Orders</th>
                                        <th class="py-3 text-muted fw-semibold fs-13 text-end">Total Revenue</th>
                                        <th class="py-3 text-muted fw-semibold fs-13 text-center">Status</th>
                                        <th class="pe-4 py-3 text-muted fw-semibold fs-13 text-end">Join Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vendors as $vendor)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-sm">
                                                    <img src="{{ $vendor->image }}" alt="" class="rounded-circle" width="32" height="32">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark fs-14">{{ $vendor->store_name ?? $vendor->name }}</h6>
                                                    <small class="text-muted fs-12">ID: #{{ $vendor->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fs-13 text-dark">{{ $vendor->email }}</span>
                                                <span class="fs-12 text-muted">{{ $vendor->phone }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1">
                                                <iconify-icon icon="solar:map-point-linear" class="text-muted"></iconify-icon>
                                                <span class="fs-13">{{ $vendor->country?->name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ $vendor->orders_count }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-dark">{{ $currency }} {{ number_format($vendor->orders_sum_total_amount ?? 0, 2) }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($vendor->status == 1)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                            @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-end text-muted fs-13">
                                            {{ $vendor->created_at->format('d M, Y') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <iconify-icon icon="solar:users-group-two-rounded-linear" class="fs-48 mb-3 opacity-25"></iconify-icon>
                                                <p class="fs-14">No vendor records found</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer p-4 bg-transparent border-top">
                        <div class="row align-items-center">
                            <div class="col-sm-6 text-center text-sm-start mb-3 mb-sm-0">
                                <p class="text-muted mb-0 fs-13">Showing {{ $vendors->firstItem() ?? 0 }} to {{ $vendors->lastItem() ?? 0 }} of {{ $vendors->total() }} entries</p>
                            </div>
                            <div class="col-sm-6">
                                <div class="pagination-container d-flex justify-content-center justify-content-sm-end">
                                    {{ $vendors->links('pagination::bootstrap-5') }}
                                </div>
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
        if (typeof initVendorReport === 'function') {
            initVendorReport();
        }
    });
</script>
@endpush
