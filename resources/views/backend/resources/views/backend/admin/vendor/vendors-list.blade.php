@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Vendor Management</h4>
                </div>
            </div>
        </div>

        <div class="border-primary border-2 mb-4" style="margin-left: -24px; margin-right: -24px;"></div>

        <!-- Stats Cards Row -->
        <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 g-3 mb-4">
            <div class="col">
                <div class="card h-100 mb-0">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div>
                                <p class="text-muted mb-1 text-uppercase fs-11 fw-bold">Total Vendors</p>
                                <h2 class="text-dark mb-0 fw-bold">{{ $total_vendors }}</h2>
                            </div>
                            <div class="avatar-lg bg-soft-purple d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:shop-linear" class="fs-32 text-purple"></iconify-icon>
                            </div>
                        </div>
                        <div class="mt-auto d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-11">
                                    <iconify-icon icon="solar:arrow-right-up-linear" class="align-middle fs-10"></iconify-icon> {{ abs($vendor_growth) }}%
                                </span>
                                <span class="text-muted fs-11">last month</span>
                            </div>
                            <!-- <a href="#" class="text-muted fs-11 fw-medium hover-text-purple">View More</a> -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 mb-0">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div>
                                <p class="text-muted mb-1 text-uppercase fs-11 fw-bold">Active Vendors</p>
                                <h2 class="text-dark mb-0 fw-bold">{{ $active_vendors }}</h2>
                            </div>
                            <div class="avatar-lg bg-soft-success d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:check-circle-linear" class="fs-32 text-success"></iconify-icon>
                            </div>
                        </div>
                        <div class="mt-auto d-flex align-items-center justify-content-between">
                            <span class="text-muted fs-11">Currently selling</span>
                            <!-- <a href="#" class="text-muted fs-11 fw-medium hover-text-purple">View More</a> -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 mb-0">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div>
                                <p class="text-muted mb-1 text-uppercase fs-11 fw-bold">Pending Approval</p>
                                <h2 class="text-dark mb-0 fw-bold">{{ $pending_vendors }}</h2>
                            </div>
                            <div class="avatar-lg bg-soft-warning d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:hourglass-linear" class="fs-32 text-warning"></iconify-icon>
                            </div>
                        </div>
                        <div class="mt-auto d-flex align-items-center justify-content-between">
                            <span class="text-danger fs-11 fw-medium">Requires Action</span>
                            <!-- <a href="#" class="text-muted fs-11 fw-medium hover-text-purple">View More</a> -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 mb-0">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div>
                                <p class="text-muted mb-1 text-uppercase fs-11 fw-bold">Blocked / Rejected</p>
                                <h2 class="text-dark mb-0 fw-bold">{{ $rejected_vendors + $blocked_vendors }}</h2>
                            </div>
                            <div class="avatar-lg bg-soft-danger d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:forbidden-circle-linear" class="fs-32 text-danger"></iconify-icon>
                            </div>
                        </div>
                        <div class="mt-auto d-flex align-items-center justify-content-between">
                            <span class="text-danger fs-11">Policy Violations</span>
                            <!-- <a href="#" class="text-muted fs-11 fw-medium hover-text-purple">View More</a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <!-- Section 1: Navigation & Add Button -->
                <div class="d-flex align-items-center justify-content-between">
                    <!-- Section 2: Filters -->
                    <div class="row col-md-12">
                        <form action="{{ route('vendors.list') }}" method="POST" id="vendor-filter-form" class="no-loader">
                            @csrf
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold fs-13 mb-1">Search Vendor</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="search" id="vendor-search" class="form-control" placeholder="Search by name, email, phone..." value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold fs-13 mb-1">Date Range</label>
                                    <div class="position-relative">
                                        <input type="text" name="date_range" class="form-control form-control-sm ps-3 pe-5 range-datepicker" autocomplete="off" placeholder="Filter by date range" value="{{ request('date_range') }}">
                                        <iconify-icon icon="solar:calendar-linear" class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></iconify-icon>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold fs-13 mb-1">Country</label>
                                    <select name="country_id" class="form-select form-select-sm" onchange="$(this.form).submit()">
                                        <option value="">All Countries</option>
                                        @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ request('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('vendors.list') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            </div>
            <!-- Filter and Table Card -->
            <div class="card">
                <div class="card-body p-4 pb-0">
                    <!-- Section 1: Navigation & Add Button -->
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        @php
                            $status = request('status');
                        @endphp
                        <ul class="nav nav-pills nav-soft-primary gap-2" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ $status === null ? 'active' : '' }} px-4 py-2" data-bs-toggle="tab" href="#all-vendors" role="tab">All Vendors</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == '0' ? 'active' : '' }} px-4 py-2" data-bs-toggle="tab" href="#pending-approval" role="tab">Pending Approval</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == '1' ? 'active' : '' }} px-4 py-2" data-bs-toggle="tab" href="#active" role="tab">Active</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == '2' ? 'active' : '' }} px-4 py-2" data-bs-toggle="tab" href="#rejected" role="tab">Rejected</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == '3' ? 'active' : '' }} px-4 py-2" data-bs-toggle="tab" href="#blocked" role="tab">Blocked</a>
                            </li>
                        </ul>
                         <div class="d-flex gap-2">
                          <a href="{{ route('add.vendor') }}" class="btn btn-sm btn-primary px-4 py-2 d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:plus-circle-linear" class="fs-18"></iconify-icon>
                                Add Vendor
                            </a>
                       </div>
                    </div>
                   
                         <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-danger px-4 py-2 d-none" id="deleteSelectedVendors">
                                <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-18"></iconify-icon>
                                Delete Selected
                            </button>
                            <form id="exportVendorsForm" action="{{ route('vendor.export.multiple') }}" method="POST" class="d-none">
                                @csrf
                                <div id="exportVendorInputs"></div>
                                <button type="submit" class="btn btn-sm btn-success px-4 py-2">
                                    <iconify-icon icon="solar:file-download-linear" class="fs-18"></iconify-icon>
                                    Export Selected
                                </button>
                            </form>
                          
                        </div>
                 


                </div>
                <div class="card-body p-0 mt-4" id="vendor-tabs-container">
                    @include('backend.admin.vendor.partials.vendor-tabs')
                </div>
                <div class="card-footer p-4">
                    <div class="row align-items-center">
                        <div class="col">
                            @php
                                $totalVendorsShown = ($all_vendors_data instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $all_vendors_data->total() : $all_vendors_data->count();
                            @endphp
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
            initVendorList();
            $('#vendor-search').on('blur', function() {
                $('#vendor-filter-form').trigger('submit');
            });
            // Optional: submit on Enter as well, but prevent immediate form submit on keypress to avoid double-submit
            $('#vendor-search').on('keydown', function(e){
                if(e.key === 'Enter'){
                    e.preventDefault();
                    $(this).blur();
                }
            });

            // Handle Checkbox Selection
            $(document).on('change', '.select-all-vendors', function() {
                $('.vendor-checkbox').prop('checked', $(this).prop('checked'));
                toggleVendorActions();
            });

            $(document).on('change', '.vendor-checkbox', function() {
                if ($('.vendor-checkbox:checked').length === $('.vendor-checkbox').length) {
                    $('.select-all-vendors').prop('checked', true);
                } else {
                    $('.select-all-vendors').prop('checked', false);
                }
                toggleVendorActions();
            });

            function toggleVendorActions() {
                if ($('.vendor-checkbox:checked').length > 0) {
                    $('#deleteSelectedVendors').removeClass('d-none');
                    $('#exportVendorsForm').removeClass('d-none');
                } else {
                    $('#deleteSelectedVendors').addClass('d-none');
                    $('#exportVendorsForm').addClass('d-none');
                }
            }

            // Multiple Delete
            $('#deleteSelectedVendors').on('click', function() {
                let ids = [];
                $('.vendor-checkbox:checked').each(function() {
                    ids.push($(this).val());
                });

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete selected vendors!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('vendor.delete.multiple') }}",
                            type: "POST",
                            data: {
                                ids: ids,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.status) {
                                    toastr.success(response.message);
                                    location.reload();
                                } else {
                                    toastr.error(response.message);
                                }
                            }
                        });
                    }
                });
            });

            // Multiple Export
            $('#exportVendorsForm').on('submit', function() {
                let inputs = '';
                $('.vendor-checkbox:checked').each(function() {
                    inputs += `<input type="hidden" name="ids[]" value="${$(this).val()}">`;
                });
                $('#exportVendorInputs').html(inputs);
            });
        });
    </script>
    @endpush
