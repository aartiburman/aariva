@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Kyc Varification Report</h4>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
            <div class="card-body p-3">
                <form action="{{ route('kyc.report') }}" method="POST" id="kyc-report-filter-form">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1 text-uppercase">Search Name/Email</label>
                            <input type="text" name="search" id="kyc-report-search" class="form-control shadow-sm py-2" placeholder="Search name/email" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small mb-1 text-uppercase">Business Name</label>
                            <input type="text" name="business_name" id="kyc-report-business-search" class="form-control shadow-sm py-2" placeholder="Business / Store Name" value="{{ request('business_name') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small mb-1 text-uppercase">Date Range</label>
                            <div class="position-relative">
                                <input type="text" name="date_range" class="form-control range-datepicker shadow-sm py-2 pe-5" autocomplete="off" placeholder="Select Date Range" value="{{ request('date_range') }}">
                                <iconify-icon icon="solar:calendar-linear" class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></iconify-icon>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-muted small mb-1 text-uppercase">Status</label>
                            <select name="status" class="form-select shadow-sm py-2">
                                <option value="">All Status</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Pending</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Verified</option>
                                <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary shadow-sm py-2 w-100 fw-medium">Search</button>
                                <a href="{{ route('kyc.report') }}" class="btn btn-secondary shadow-sm py-2 w-100 fw-medium">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center g-2">
                            <div class="col-lg-3">
                                <h4 class="card-title">Vendor KYC List</h4>
                            </div>

                        </div>
                    </div>

                    <div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th class="ps-4">VENDOR NAME</th>
                                        <th>DOCUMENTS</th>
                                        <th>STATUS</th>
                                        <th class="text-nowrap">LAST UPLOAD</th>
                                        <th>TAX ID / BUSINESS NAME</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @include('backend.admin.report.partials.kyc-table')
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($vendors instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="card-footer border-top d-flex justify-content-between align-items-center">
                        <p class="text-muted mb-0 fs-13" id="pagination-info">Showing {{ $vendors->firstItem() ?? 0 }} to {{ $vendors->lastItem() ?? 0 }} of {{ $vendors->total() }} entries</p>
                        <div class="pagination-container" id="pagination-links">
                            {{ $vendors->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


    @endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const filterForm = $('#kyc-report-filter-form');
        const searchInputs = $('#kyc-report-search, #kyc-report-business-search');
        const tableBody = $('tbody');
        let debounceTimer;

        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), delay);
            };
        }

        function fetchKycReport(url = null) {
            $.ajax({
                url: url || filterForm.attr('action'),
                type: 'POST',
                data: filterForm.serialize(),
                beforeSend: function() {
                    tableBody.html('<tr><td colspan="6" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
                },
                success: function(response) {
                    if (response.table !== undefined) {
                        tableBody.html(response.table);
                        $('#pagination-links').html(response.pagination);
                        $('#pagination-info').text(response.info);
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
            fetchKycReport();
        });

        searchInputs.on('keyup', debounce(function() {
            fetchKycReport();
        }, 3000));

        filterForm.find('select[name="status"]').on('change', function() {
            fetchKycReport();
        });

        $('.range-datepicker').on('change', function() {
            fetchKycReport();
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            fetchKycReport($(this).attr('href'));
        });
    });
</script>
@endpush

    @push('scripts')
    <script>
        function approveVendor(id, name) {
            if (confirm('Are you sure you want to approve ' + name + '?')) {
                updateVendorStatus(id, 1, null);
            }
        }

        function rejectVendor(id, name) {
            const reason = prompt('Please enter rejection reason for ' + name + ':');
            if (reason !== null && reason.trim() !== "") {
                updateVendorStatus(id, 2, reason);
            } else if (reason !== null) {
                alert('Rejection reason is required.');
            }
        }

      
    </script>
    @endpush