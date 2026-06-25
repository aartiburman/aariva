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
                        <form action="{{ route('vendor.report') }}" method="GET">
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
                                    <select name="country_id" class="form-select form-select-sm py-2 select2" onchange="this.form.submit()">
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
                                    <a href="{{ route('vendor.report') }}" class="btn btn-sm btn-outline-secondary py-2 px-3">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header border-bottom-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Vendors Report List</h4>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th style="width: 20px;">
                                            <div class="form-check ms-1">
                                                <input type="checkbox" class="form-check-input" id="customCheck1">
                                                <label class="form-check-label" for="customCheck1"></label>
                                            </div>
                                        </th>
                                        <th>Vendor Name</th>
                                        <th>Email</th>
                                        <th>Total Products</th>
                                        <th>Total Orders</th>
                                        <th>Total Sales</th>
                                        <th>Total Discount</th>
                                        <th>Commission</th>
                                        <!-- <th>Status</th> -->
                                        <th>Joined Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vendors as $vendor)
                                    <tr>
                                        <td>
                                            <div class="form-check ms-1">
                                                <input type="checkbox" class="form-check-input">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-xs">
                                                    <img src="{{ $vendor->image ? $vendor->image : asset('backend/assets/images/users/avatar-1.jpg') }}" alt="" class="img-fluid rounded-circle">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold fs-14">{{ $vendor->store_name ?? $vendor->name }}</h6>
                                                    <small class="text-muted fs-12">{{ $vendor->name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $vendor->email }}</td>
                                        <td class="text-center">{{ $vendor->total_products }}</td>
                                        <td class="text-center">{{ $vendor->total_orders }}</td>
                                        <td class="text-end">
                                            <span class="fw-semibold fs-13">
                                                {{ $currency }} {{ $vendor->formatted_total_sales }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-semibold fs-13">
                                                {{ $vendor->formatted_total_discount }}
                                                <small class="text-muted">({{ $vendor->discount_percentage }}% Off)</small>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-semibold fs-13">
                                                {{ $currency }} {{ $vendor->formatted_total_commission }}
                                            </span>
                                        </td>
                                        <!-- <td>
                                            @if($vendor->status == 1)
                                                <span class="badge bg-success-subtle text-success py-1 px-2 fs-11 text-uppercase">Verified</span>
                                            @elseif($vendor->status == 0)
                                                <span class="badge bg-warning-subtle text-warning py-1 px-2 fs-11 text-uppercase">Pending</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger py-1 px-2 fs-11 text-uppercase">Inactive</span>
                                            @endif
                                        </td> -->
                                        <td>{{ optional($vendor->created_at)->format('M d, Y') ?? 'N/A' }}</td>
                                        
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4">No vendor records found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($vendors instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="card-footer border-top">
                        {{ $vendors->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#vendor-report-search').on('blur', function() {
            if ($(this).val().trim() !== '') {
                $(this).closest('form').submit();
            }
        });
    });
</script>
@endpush




