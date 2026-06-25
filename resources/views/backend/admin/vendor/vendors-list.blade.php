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
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold fs-13 mb-1">Search Vendor</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="search" id="vendor-search" class="form-control" placeholder="Search name, email, phone..." value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold fs-13 mb-1">Store Name</label>
                                    <select name="vendor_id" class="form-select form-select-sm select2" onchange="$(this.form).submit()">
                                        <option value="">All Stores</option>
                                        @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->store_name ?? $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
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
                <!-- Section 1: Navigation & Action Buttons -->
                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                    @php
                    $status = request('status');
                    @endphp
                    <ul class="nav nav-pills nav-soft-primary gap-2" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $status === null ? 'active' : '' }} px-3 py-2" data-bs-toggle="tab" href="#all-vendors" role="tab">All Vendors</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status == '0' ? 'active' : '' }} px-3 py-2" data-bs-toggle="tab" href="#pending-approval" role="tab">Pending Approval</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status == '1' ? 'active' : '' }} px-3 py-2" data-bs-toggle="tab" href="#active" role="tab">Active</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status == '2' ? 'active' : '' }} px-3 py-2" data-bs-toggle="tab" href="#rejected" role="tab">Rejected</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $status == '3' ? 'active' : '' }} px-3 py-2" data-bs-toggle="tab" href="#blocked" role="tab">Blocked</a>
                        </li>
                    </ul>

                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('add.vendor') }}" class="btn btn-sm btn-primary px-3 py-2 d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:plus-circle-linear" class="fs-18"></iconify-icon>
                            Add Vendor
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger px-3 py-2 d-none" id="deleteSelectedVendors">
                            <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-18"></iconify-icon>
                            Delete 
                        </button>
                        <form id="exportVendorsForm" action="{{ route('vendor.export.multiple') }}" method="POST" class="d-inline">
                            @csrf
                            <div id="exportVendorInputs"></div>
                            <button type="submit" class="btn btn-sm btn-outline-info px-3 py-2 d-none" id="exportVendorBtn">
                                <iconify-icon icon="solar:file-download-linear" class="fs-18"></iconify-icon>
                                Export
                            </button>
                        </form>
                    </div>
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
        $('#vendor-search').on('keydown', function(e) {
            if (e.key === 'Enter') {
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

        // Toggle Verified Badge
        $(document).on('change', '.verify-vendor-toggle', function() {
            const vendorId = $(this).data('id');
            const isChecked = $(this).is(':checked');
            const $row = $(this).closest('tr');
            const $badgeContainer = $row.find('.verified-badge-container');

            $.ajax({
                url: "{{ route('vendor.toggle.verified') }}",
                type: "POST",
                data: {
                    id: vendorId,
                    is_verified: isChecked ? 1 : 0,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.status) {
                        if (isChecked) {
                            $badgeContainer.html('<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fs-11">Verified</span>');
                        } else {
                            $badgeContainer.empty();
                        }
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                        $(this).prop('checked', !isChecked);
                    }
                }.bind(this),
                error: function() {
                    toastr.error('An error occurred. Please try again.');
                    $(this).prop('checked', !isChecked);
                }.bind(this)
            });
        });

        // Change Vendor Status Action
        $(document).on('click', '.action-change-status', function() {
            const id = $(this).data('id');
            const status = $(this).data('status');
            let confirmText = "You want to change this vendor status!";

            if (status == 1) confirmText = "You want to approve/unblock this vendor!";
            if (status == 2) confirmText = "You want to reject this vendor!";
            if (status == 3) confirmText = "You want to block this vendor!";

            Swal.fire({
                title: 'Are you sure?',
                text: confirmText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('vendor.change.status') }}",
                        type: "POST",
                        data: {
                            id: id,
                            status: status,
                            force: true, // Bypass document check if any
                            rejection_reason: status == 2 || status == 3 ? 'Action by Admin' : '',
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.status === true || response.status === 'success') {
                                toastr.success(response.message || 'Status updated successfully');
                                location.reload();
                            } else {
                                toastr.error(response.message || 'Failed to update status');
                            }
                        },
                        error: function() {
                            toastr.error('An error occurred');
                        }
                    });
                }
            });
        });

        // Click on status badge to show select
        $(document).on('click', '.status-badge', function() {
            let container = $(this).closest('.status-container');
            $(this).addClass('d-none');
            container.find('.status-select').removeClass('d-none').focus();
        });

        // Handle status select change
        $(document).on('change', '.status-select', function() {
            let select = $(this);
            let container = select.closest('.status-container');
            let badge = container.find('.status-badge');
            let vendorId = container.data('id');
            let newStatus = select.val();
            
            // Get old status from data-status or badge text
            let oldStatusText = badge.text().trim();
            let oldStatus = '0';
            if (oldStatusText === 'Approved') oldStatus = '1';
            else if (oldStatusText === 'Rejected') oldStatus = '2';
            else if (oldStatusText === 'Blocked') oldStatus = '3';

            if (newStatus == 2 || newStatus == 3) { // Rejected or Blocked
                 Swal.fire({
                    title: newStatus == 2 ? 'Reject Vendor?' : 'Block Vendor?',
                    text: "Please provide a reason:",
                    input: 'text',
                    inputPlaceholder: 'Reason...',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Confirm',
                    preConfirm: (reason) => {
                        if (!reason) {
                            Swal.showValidationMessage('Reason is required');
                        }
                        return reason;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        updateVendorStatus(vendorId, newStatus, result.value, select, badge);
                    } else {
                        select.val(oldStatus).addClass('d-none');
                        badge.removeClass('d-none');
                    }
                });
            } else {
                 Swal.fire({
                    title: 'Are you sure?',
                    text: "Change vendor status?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, update it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        updateVendorStatus(vendorId, newStatus, null, select, badge);
                    } else {
                        select.val(oldStatus).addClass('d-none');
                        badge.removeClass('d-none');
                    }
                });
            }
        });

        $(document).on('blur', '.status-select', function() {
             setTimeout(() => {
                 if (!$(this).is(':focus') && !Swal.isVisible()) {
                     $(this).addClass('d-none');
                     $(this).siblings('.status-badge').removeClass('d-none');
                 }
             }, 200);
        });

        function updateVendorStatus(id, status, reason, select, badge, force = false) {
            let data = {
                _token: "{{ csrf_token() }}",
                id: id,
                status: status,
                rejection_reason: reason || 'Action by Admin'
            };
            if (force) data.force = 1;

            $.ajax({
                url: "{{ route('vendor.change.status') }}",
                type: "POST",
                data: data,
                success: function(response) {
                    if (response.status === 'confirm') {
                        Swal.fire({
                            title: 'Warning',
                            text: response.message,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, approve anyway!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                updateVendorStatus(id, status, reason, select, badge, true);
                            } else {
                                select.val(badge.text().trim() === 'Approved' ? '1' : '0').addClass('d-none');
                                badge.removeClass('d-none');
                            }
                        });
                        return;
                    }

                    if (response.status === true || response.status === 'success') {
                        toastr.success(response.message || 'Status updated successfully');
                        location.reload(); // Reload to update all UI elements and counts
                    } else {
                        toastr.error(response.message || 'Failed to update status');
                        select.addClass('d-none');
                        badge.removeClass('d-none');
                    }
                },
                error: function() {
                    toastr.error('An error occurred');
                    select.addClass('d-none');
                    badge.removeClass('d-none');
                }
            });
        }

        function toggleVendorActions() {
            if ($('.vendor-checkbox:checked').length > 0) {
                $('#deleteSelectedVendors').removeClass('d-none');
                $('#exportVendorBtn').removeClass('d-none');
            } else {
                $('#deleteSelectedVendors').addClass('d-none');
                $('#exportVendorBtn').addClass('d-none');
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