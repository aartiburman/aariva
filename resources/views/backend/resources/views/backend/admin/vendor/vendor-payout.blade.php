@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Page Title & Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Vendor Payout List — {{ ucfirst($frequency ?? 'monthly') }}</h4>
                </div>
            </div>
        </div>

        <!-- Stats Cards (Optional but adds value like other pages) -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-primary-subtle">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md  d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:wallet-money-linear" class="fs-24 text-primary"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-muted mb-1 fs-13">Total Payout</p>
                                <h4 class="mb-0 fw-bold givepayouts" id="totalPayoutAmount">{{ number_format($total_payout_amount, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-warning-subtle">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md  d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:clock-circle-linear" class="fs-24 text-warning"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-muted mb-1 fs-13">Pending Payouts</p>
                                <h4 class="mb-0 fw-bold" id="pendingPayoutsCount">{{ $pending_payouts }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-success-subtle">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md  d-flex align-items-center justify-content-center">
                                <iconify-icon icon="solar:hand-money-linear" class="fs-24 text-success"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-muted mb-1 fs-13">Total Commission</p>
                                <h4 class="mb-0 fw-bold" id="totalCommissionAmount">{{ number_format($total_commission, 2) }}</h4>
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
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                                <option value="3" {{ request('status') === '3' ? 'selected' : '' }}>Approved</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Paid</option>
                                <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Failed</option>
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
                                <th class="fs-13 fw-semibold text-muted">Items Qty</th>
                                <th class="fs-13 fw-semibold text-muted">Total Amount</th>
                                <th class="fs-13 fw-semibold text-muted">Commission</th>
                                <th class="fs-13 fw-semibold text-muted">Payment Amount</th>
                                <th class="fs-13 fw-semibold text-muted">Payment mode</th>
                                <th class="fs-13 fw-semibold text-muted text-center">Status</th>
                                <th class="fs-13 fw-semibold text-muted">Payment Date</th>
                                <th class="pe-4 text-end fs-13 fw-semibold text-muted">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payouts as $payout)
                            <tr>
                                <td class="ps-4">
                                    <div class="form-check">
                                        <input class="form-check-input payout-checkbox" type="checkbox" value="{{ $payout->id }}">
                                    </div>
                                </td>
                                <td class="fs-13">#VP-{{ str_pad($payout->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td class="fs-13 fw-medium text-dark">{{ $payout->vendor->name ?? 'N/A' }}</td>
                                <td class="fs-13 text-muted">{{ $payout->vendor->store_name ?? 'N/A' }}</td>
                                <td class="fs-13">{{ (int) ($payout->items_qty ?? 0) }}</td>
                                <td class="fs-13">{{ optional(optional($payout->vendor)->country)->currency_code ?? 'AED' }} {{ number_format($payout->order_amount, 2) }}</td>
                                <td class="fs-13">{{ optional(optional($payout->vendor)->country)->currency_code ?? 'AED' }} {{ number_format($payout->commission_amount, 2) }}</td>
                                <td class="fs-13 fw-bold text-dark">{{ optional(optional($payout->vendor)->country)->currency_code ?? 'AED' }} {{ number_format($payout->payout_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary px-2 py-1 payment-mode-label">
                                        <!-- {{ ucfirst($payout->payment_method ?? 'Wallet') }} -->
                                         'Wallet
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="payout-status" data-id="{{ $payout->id }}" data-status="{{ $payout->status }}">
                                        @php
                                            $status = $payout->status;
                                            $label = ucfirst($status);
                                            $cls = 'bg-danger-subtle text-danger';
                                            if ($status === 'paid') $cls = 'bg-success-subtle text-success';
                                            elseif ($status === 'pending') $cls = 'bg-warning-subtle text-warning';
                                            elseif ($status === 'approved') $cls = 'bg-info-subtle text-info';
                                        @endphp
                                        <span class="status-badge badge px-2 py-1 {{ $cls }} {{ $status !== 'paid' ? 'cursor-pointer' : '' }}">{{ $label }}</span>
                                        <select class="form-select form-select-sm d-none w-auto payout-select" data-current="{{ $payout->status }}">
                                            <option value="pending" {{ $payout->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ $payout->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="paid" {{ $payout->status === 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="failed" {{ $payout->status === 'failed' ? 'selected' : '' }}>Failed</option>
                                        </select>
                                    </div>
                                </td>
                                <td class="fs-13 text-muted">
                                    <span class="payment-date-label">
                                        @if($payout->paid_at)
                                            {{ \Carbon\Carbon::parse($payout->paid_at)->format('Y-m-d') }}
                                        @elseif($payout->created_at)
                                            {{ \Carbon\Carbon::parse($payout->created_at)->format('Y-m-d') }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('vendor.payout.show', ['id' => $payout->id, 'frequency' => $frequency ?? 'monthly']) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="View Detail">
                                        <iconify-icon icon="solar:eye-linear" class="fs-20"></iconify-icon>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-5 text-muted">
                                    No payout records found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($payouts instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="card-footer bg-transparent border-0 py-4 px-4">
                <div class="d-flex justify-content-end">
                    {{ $payouts->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .avatar-md { width: 48px; height: 48px; }
    .avatar-sm { width: 32px; height: 32px; }
    .fs-13 { font-size: 0.8125rem; }
    .fs-24 { font-size: 1.5rem; }
    .card { transition: all 0.3s ease; }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initDateRangePicker === 'function') {
            initDateRangePicker('.range-datepicker');
        }
        const form = document.getElementById('payout-filter-form');
        const statusSelect = form.querySelector('select[name="status"]');
        const dateInput = form.querySelector('input[name="date_range"]');
        const clearBtn = document.getElementById('btnClearPayoutFilters');
        statusSelect.addEventListener('change', () => form.submit());
        if (typeof $ !== 'undefined' && $(dateInput).data('daterangepicker')) {
            $(dateInput).on('apply.daterangepicker', function() { form.submit(); });
        } else {
            dateInput.addEventListener('change', () => form.submit());
        }
        clearBtn.addEventListener('click', function() {
            form.querySelector('[name="search"]').value = '';
            form.querySelector('[name="date_range"]').value = '';
            form.querySelector('[name="status"]').value = '';
            form.submit();
        });

        const checkAll = document.getElementById('checkAllPayouts');
        checkAll && checkAll.addEventListener('change', function() {
            document.querySelectorAll('.payout-checkbox').forEach(cb => cb.checked = checkAll.checked);
        });
        const btnExport = document.getElementById('btnExportSelectedPayouts');
        const idsInput = document.getElementById('exportPayoutIds');
        btnExport && btnExport.addEventListener('click', function() {
            const ids = Array.from(document.querySelectorAll('.payout-checkbox:checked')).map(el => el.value);
            if (!ids.length) {
                alert('Select at least one payout to export');
                return;
            }
            idsInput.value = ids.join(',');
            document.getElementById('exportSelectedPayoutsForm').submit();
        });
    });
    function statusBadgeClass(s) {
        if (s === 'paid') return 'bg-success-subtle text-success';
        if (s === 'pending') return 'bg-warning-subtle text-warning';
        if (s === 'approved') return 'bg-info-subtle text-info';
        return 'bg-danger-subtle text-danger';
    }
    function showAlert(opts) {
        if (window.Swal && Swal.fire) {
            return Swal.fire(opts);
        }
        if (opts.icon === 'question' || opts.showCancelButton) {
            const ok = confirm(opts.text || opts.title || 'Are you sure?');
            return Promise.resolve({ isConfirmed: ok, value: null });
        }
        alert(opts.text || opts.title || '');
        return Promise.resolve({ isConfirmed: true, value: null });
    }
    document.addEventListener('click', function(e) {
        const badge = e.target.closest('.status-badge');
        if (!badge) return;
        const wrap = badge.closest('.payout-status');
        if (!wrap) return;
        const cur = (wrap.getAttribute('data-status') || 'pending');
        if (cur === 'paid') return;
        const select = wrap.querySelector('.payout-select');
        if (!select) return;
        select.classList.remove('d-none');
        badge.classList.add('d-none');
        select.focus();
    });
    document.addEventListener('change', async function(e) {
        const select = e.target.closest('.payout-select');
        if (!select) return;
        const wrap = select.closest('.payout-status');
        const id = wrap.getAttribute('data-id');
        const previous = select.getAttribute('data-current') || 'pending';
        const next = select.value;
        let payload = { status: next };
        if (next === 'paid') {
            const ask = await showAlert({
                title: 'Mark as Paid?',
                text: 'Amount will be transferred to vendor wallet.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, confirm',
                cancelButtonText: 'Cancel'
            });
            if (!ask.isConfirmed) {
                select.value = previous;
                select.classList.add('d-none');
                wrap.querySelector('.status-badge').classList.remove('d-none');
                return;
            }
            if (window.Swal && Swal.fire) {
                const input = await Swal.fire({
                    title: 'Transaction ID',
                    input: 'text',
                    inputPlaceholder: 'Optional',
                    showCancelButton: true,
                    confirmButtonText: 'Save'
                });
                if (input.isDismissed) {
                    select.value = previous;
                    select.classList.add('d-none');
                    wrap.querySelector('.status-badge').classList.remove('d-none');
                    return;
                }
                payload.transaction_id = input.value || '';
            }
            payload.payment_method = 'Wallet';
        } else {
            const ask = await showAlert({
                title: 'Change Status?',
                text: 'Update payout status to ' + next.toUpperCase() + '?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            });
            if (!ask.isConfirmed) {
                select.value = previous;
                select.classList.add('d-none');
                wrap.querySelector('.status-badge').classList.remove('d-none');
                return;
            }
        }
        try {
            const resp = await fetch(`{{ url('vendor-payout') }}/${id}/status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const ok = resp.ok ? await resp.json() : null;
            if (!resp.ok || !ok || !ok.status) {
                throw new Error((ok && ok.message) ? ok.message : 'reject');
            }
            select.setAttribute('data-current', next);
            wrap.setAttribute('data-status', next);
            const badge = wrap.querySelector('.status-badge');
            badge.textContent = next.charAt(0).toUpperCase() + next.slice(1);
            badge.className = 'status-badge badge px-2 py-1 ' + statusBadgeClass(next) + (next !== 'paid' ? ' cursor-pointer' : '');
            if (ok && ok.data && ok.data.summary) {
                const s = ok.data.summary;
                const fmt = (v) => {
                    try { return Number(v).toFixed(2); } catch(e) { return v; }
                };
                const elTotal = document.getElementById('totalPayoutAmount');
                const elPending = document.getElementById('pendingPayoutsCount');
                const elCommission = document.getElementById('totalCommissionAmount');
                if (elTotal) elTotal.textContent = fmt(s.total_payout_amount ?? s.total_incl_vat ?? s.total ?? 0);
                if (elPending) elPending.textContent = s.pending_payouts ?? 0;
                if (elCommission) elCommission.textContent = fmt(s.total_commission ?? 0);
            }
            if (ok && ok.data) {
                const row = wrap.closest('tr');
                const pm = row ? row.querySelector('.payment-mode-label') : null;
                if (pm && ok.data.payment_method) pm.textContent = ok.data.payment_method;
                const pd = row ? row.querySelector('.payment-date-label') : null;
                if (pd && ok.data.paid_at) {
                    let d = ok.data.paid_at;
                    if (typeof d === 'string') { d = d.substring(0, 10); }
                    pd.textContent = d;
                }
            }
            if (window.Swal && Swal.fire) Swal.fire({ icon: 'success', title: 'Updated', timer: 1200, showConfirmButton: false });
            if (next === 'paid') {
                select.classList.add('d-none');
                badge.classList.remove('d-none');
                return;
            }
            select.classList.add('d-none');
            badge.classList.remove('d-none');
        } catch (err) {
            if (window.Swal && Swal.fire) Swal.fire({ icon: 'error', title: 'reject', text: err.message || 'Update reject' });
            select.value = previous;
            select.classList.add('d-none');
            wrap.querySelector('.status-badge').classList.remove('d-none');
        }
    });
</script>
@endsection
