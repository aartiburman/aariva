@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Vendor Payout List — {{ ucfirst($frequency ?? 'daily') }}</h4>
                </div>
            </div>
        </div>

        <!-- Payout Frequency Tabs -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex gap-2">
                    <a href="{{ route('vendor.payout', ['frequency' => 'daily']) }}" class="btn btn-sm {{ ($frequency ?? 'monthly') == 'daily' ? 'btn-primary' : 'btn-outline-primary' }} px-3">Daily</a>
                    <a href="{{ route('vendor.payout', ['frequency' => 'weekly']) }}" class="btn btn-sm {{ ($frequency ?? 'monthly') == 'weekly' ? 'btn-primary' : 'btn-outline-primary' }} px-3">Weekly</a>
                    <a href="{{ route('vendor.payout', ['frequency' => 'bi-weekly']) }}" class="btn btn-sm {{ ($frequency ?? 'monthly') == 'bi-weekly' ? 'btn-primary' : 'btn-outline-primary' }} px-3">Bi-weekly</a>
                    <a href="{{ route('vendor.payout', ['frequency' => 'monthly']) }}" class="btn btn-sm {{ ($frequency ?? 'monthly') == 'monthly' ? 'btn-primary' : 'btn-outline-primary' }} px-3">Monthly</a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-primary-subtle h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:wallet-money-linear" class="fs-24 text-primary"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-muted mb-1 fs-13">Total Paid</p>
                                <h4 class="mb-0 fw-bold" id="totalPayoutAmount">{{ number_format($total_payout_amount, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-warning-subtle h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:clock-circle-linear" class="fs-24 text-warning"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-muted mb-1 fs-13">Unpaid Payouts ({{ $pending_payouts }})</p>
                                <h4 class="mb-0 fw-bold text-warning">{{ number_format($total_unpaid_amount, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-success-subtle h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:card-transfer-linear" class="fs-24 text-success"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-muted mb-1 fs-13">Total Wallet Balance</p>
                                <h4 class="mb-0 fw-bold text-success">{{ number_format($total_wallet_balance, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-info-subtle h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:hand-money-linear" class="fs-24 text-info"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-muted mb-1 fs-13">Total Commission</p>
                                <h4 class="mb-0 fw-bold text-info">{{ number_format($total_commission, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Search Row -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header  border-0 p-4">
                <form action="{{ route('vendor.payout', ['frequency' => $frequency ?? 'monthly']) }}" method="POST" id="payout-filter-form">
                    @csrf
                    <input type="hidden" name="frequency" value="{{ $frequency ?? 'monthly' }}">
                    <div class="row align-items-end g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold fs-13 mb-1">Search Payout</label>
                            <input type="text" name="search" id="payout-search" class="form-control form-control-sm" placeholder="Search by vendor, request ID..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold fs-13 mb-1">Date Range</label>
                            <input type="text" name="date_range" class="form-control form-control-sm range-datepicker" autocomplete="off" placeholder="Select Date Range" value="{{ request('date_range') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold fs-13 mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="button" id="btnClearPayoutFilters" class="btn btn-sm btn-outline-secondary w-100">Clear</button>
                            <button type="submit" class="btn btn-sm btn-primary d-none">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">Vendor Payout List</h5>
                <div class="d-flex gap-2 align-items-center">
                    <!-- <a href="{{ route('vendor.payout.create') }}" class="btn btn-sm btn-primary px-3">
                        <iconify-icon icon="solar:add-circle-linear" class="align-middle me-1"></iconify-icon> Add Payout
                    </a> -->
                    <form action="{{ route('vendor.payout.export.selected') }}" method="POST" id="exportSelectedPayoutsForm" class="d-inline">
                        @csrf
                        <input type="hidden" name="ids" id="exportPayoutIds">
                        <button type="button" id="btnExportSelectedPayouts" class="btn btn-sm btn-outline-secondary px-3">
                            <iconify-icon icon="solar:download-linear" class="align-middle me-1"></iconify-icon> Export Selected
                        </button>
                    </form>
                    <a href="{{ route('vendor.payout', array_merge(request()->except('page'), ['export' => 1])) }}" class="btn btn-sm btn-outline-secondary px-3">Export All</a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-4" style="width: 40px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkAllPayouts">
                                    </div>
                                </th>
                                <th class="fs-13 fw-semibold text-muted">Payout ID</th>
                                <th class="fs-13 fw-semibold text-muted">Vendor Name</th>
                                <th class="fs-13 fw-semibold text-muted">Store Name</th>
                                <th class="fs-13 fw-semibold text-muted">Wallet Bal</th>
                                <th class="fs-13 fw-semibold text-muted">Items Qty</th>
                                <th class="fs-13 fw-semibold text-muted">Total Amount</th>
                                <th class="fs-13 fw-semibold text-muted">Commission</th>
                                <th class="fs-13 fw-semibold text-muted">PG Fee</th>
                                <th class="fs-13 fw-semibold text-muted">Payment Amount</th>
                                <th class="fs-13 fw-semibold text-muted">Payment mode</th>
                                <th class="fs-13 fw-semibold text-muted text-center">Status</th>
                                <th class="fs-13 fw-semibold text-muted">Payment Date</th>
                                <th class="pe-4 text-end fs-13 fw-semibold text-muted">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('backend.admin.vendor.partials.vendor-payout-table')
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($payouts instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="card-footer bg-transparent border-0 py-4 px-4">
                <div class="row align-items-center">
                    <div class="col-sm-6 text-center text-sm-start mb-3 mb-sm-0">
                        <p class="text-muted mb-0 fs-13" id="pagination-info">Showing {{ $payouts->firstItem() ?? 0 }} to {{ $payouts->lastItem() ?? 0 }} of {{ $payouts->total() }} entries</p>
                    </div>
                    <div class="col-sm-6">
                        <div class="pagination-container d-flex justify-content-center justify-content-sm-end" id="pagination-links">
                            {{ $payouts->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        const filterForm = $('#payout-filter-form');
        const searchInput = $('#payout-search');
        const tableBody = $('tbody');
        const statsContainers = {
            totalPayoutAmount: $('#totalPayoutAmount'),
            pendingPayoutsCount: $('.bg-warning-subtle p'),
            totalUnpaidAmount: $('.bg-warning-subtle h4'),
            totalWalletBalance: $('.bg-success-subtle h4'),
            totalCommission: $('.bg-info-subtle h4')
        };
        let debounceTimer;

        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), delay);
            };
        }

        function fetchPayouts(url = null) {
            const method = url ? 'GET' : 'POST';
            const data = url ? null : filterForm.serialize();
            
            $.ajax({
                url: url || filterForm.attr('action'),
                type: method,
                data: data,
                beforeSend: function() {
                    tableBody.html('<tr><td colspan="14" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
                },
                success: function(response) {
                    if (response.status && response.table !== undefined) {
                        tableBody.html(response.table);
                        $('#pagination-links').html(response.pagination);
                        $('#pagination-info').text(response.info);

                        if (response.stats) {
                            updateStats(response.stats);
                        }
                    } else {
                        // If response is not JSON or status is false, but we got HTML back (e.g. from a non-AJAX POST)
                        if (typeof response === 'string') {
                            const newDoc = new DOMParser().parseFromString(response, 'text/html');
                            const newTable = newDoc.querySelector('tbody').innerHTML;
                            tableBody.html(newTable);
                        } else {
                            location.reload();
                        }
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr);
                    // Only reload if it's not a user-initiated cancellation
                    if (xhr.status !== 0) {
                        location.reload();
                    }
                }
            });
        }

        function updateStats(stats) {
            statsContainers.totalPayoutAmount.text(stats.total_payout_amount);
            statsContainers.pendingPayoutsCount.text(`Unpaid Payouts (${stats.pending_payouts})`);
            statsContainers.totalUnpaidAmount.text(stats.total_unpaid_amount);
            statsContainers.totalWalletBalance.text(stats.total_wallet_balance);
            statsContainers.totalCommission.text(stats.total_commission);
        }

        filterForm.on('submit', function(e) {
            e.preventDefault();
            fetchPayouts();
        });

        filterForm.find('select').on('change', function() {
            fetchPayouts();
        });

        searchInput.on('keyup', debounce(function() {
            fetchPayouts();
        }, 300)); // Reduced debounce time for better responsiveness

        $('.range-datepicker').on('change', function() {
            fetchPayouts();
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            fetchPayouts($(this).attr('href'));
        });

        $('#btnClearPayoutFilters').on('click', function() {
            filterForm[0].reset();
            fetchPayouts();
        });

        // Handle "Mark as Paid" action
        $(document).on('click', '.mark-as-paid-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const payoutId = $btn.data('id');
            const url = `{{ url('vendor-payout') }}/${payoutId}/mark-as-paid`;
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            Swal.fire({
                title: 'Mark as Paid?',
                text: "Are you sure you want to mark this payout as paid? This will also update the vendor's wallet balance.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Pay Now!',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: csrfToken,
                            frequency: filterForm.find('input[name="frequency"]').val()
                        }
                    }).catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error.responseJSON?.message || 'Something went wrong'}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value.status) {
                    // Success - Update UI immediately without reloading the table
                    const response = result.value;

                    // Change badge from a link to a span and update styles
                    $btn.replaceWith(
                        `<span class="status-badge badge px-2 py-1 bg-success-subtle text-success"
                            style="cursor: not-allowed; opacity: 0.75;"
                            title="This payout has been paid and cannot be modified">
                            Paid
                        </span>`
                    );

                    // Update stats without refreshing the whole table
                    if (response.stats) {
                        updateStats(response.stats);
                    }

                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Final safety sync to ensure UI matches server state exactly
                    setTimeout(() => {
                        fetchPayouts();
                    }, 1000);

                } else if (result.isConfirmed && !result.value.status) {
                    Swal.fire('Error!', result.value.message, 'error');
                }
            });
        });

        // Bulk Actions
        $(document).on('change', '#checkAllPayouts', function() {
            $('.payout-checkbox').prop('checked', $(this).prop('checked'));
        });

        $('#btnExportSelectedPayouts').on('click', function() {
            const ids = $('.payout-checkbox:checked').map(function() { return $(this).val(); }).get();
            if (!ids.length) {
                toastr.warning('Select at least one payout to export');
                return;
            }
            $('#exportPayoutIds').val(ids.join(','));
            $('#exportSelectedPayoutsForm').submit();
        });
    });
</script>
@endpush
@endsection
