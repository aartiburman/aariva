@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">My Payouts</h4>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2">Preferred Payout Frequency</label>
                        <div class="d-flex flex-wrap gap-2">
                            @php
                                $current = Auth::user()->payout_frequency ?? 'monthly';
                                $frequencies = [
                                    'weekly' => 'Weekly',
                                    'bi-weekly' => 'Bi-weekly',
                                    'monthly' => 'Monthly',
                                    'daily' => 'Daily'
                                ];
                            @endphp
                            
                            @foreach($frequencies as $key => $label)
                                @if(in_array($key, $allowedFrequencies))
                                    <div class="form-check form-check-inline border rounded p-2 px-3 @if($current == $key) border-primary bg-primary-subtle @endif">
                                        <input class="form-check-input payout-frequency-radio" type="radio" name="payout_frequency" id="freq_{{ $key }}" value="{{ $key }}" @if($current == $key) checked @endif>
                                        <label class="form-check-label cursor-pointer" for="freq_{{ $key }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                @endif
                            @endforeach

                            @if(empty($allowedFrequencies))
                                <div class="alert alert-info py-2 px-3 mb-0 w-100">
                                    <iconify-icon icon="solar:info-circle-linear" class="align-middle me-1"></iconify-icon>
                                    No payout frequency options are currently enabled by the admin.
                                </div>
                            @endif
                        </div>
                        <small class="text-muted mt-1 d-block">Choose your preferred schedule for receiving payments.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header  border-0">
                <form method="POST" id="vendorPayoutFilterForm" action="{{ route('vendor.payouts') }}">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold fs-13 mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                               
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold fs-13 mb-1">Date Range</label>
                            <input type="text" name="date_range" class="form-control form-control-sm range-datepicker" value="{{ request('date_range') }}" placeholder="YYYY-MM-DD to YYYY-MM-DD" autocomplete="off">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="button" id="btnVendorPayoutClear" class="btn btn-sm btn-outline-secondary">Clear</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">Payout List</h5>
                <form action="{{ route('vendor.payouts.export.selected') }}" method="POST" id="vendorExportSelectedForm" class="d-inline">
                    @csrf
                    <input type="hidden" name="ids" id="vendorExportIds">
                    <button type="button" id="btnVendorExportSelected" class="btn btn-sm btn-outline-secondary">
                        <iconify-icon icon="solar:download-linear" class="align-middle me-1"></iconify-icon> Export Selected
                    </button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-4" style="width:40px">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkAllVendorPayouts">
                                    </div>
                                </th>
                                <th>Payout ID</th>
                                <th>Items Qty</th>
                                <th>Order Amount</th>
                                <th>PG Fee</th>
                                <th>Commission</th>
                                <th>Payout Amount</th>
                                <th>Status</th>
                                <th>Payment Date</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payouts as $payout)
                            <tr>
                                <td class="ps-4">
                                    <div class="form-check">
                                        <input class="form-check-input vendor-payout-checkbox" type="checkbox" value="{{ $payout->id }}">
                                    </div>
                                </td>
                                <td>#VP-{{ str_pad($payout->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ (int) ($payout->items_qty ?? 0) }}</td>
                                <td>{{ optional(optional($payout->vendor)->country)->currency_code ?? 'INR' }} {{ number_format($payout->order_amount, 2) }}</td>
                                <td>{{ optional(optional($payout->vendor)->country)->currency_code ?? 'INR' }} {{ number_format($payout->pg_fee_amount, 2) }}</td>
                                <td>{{ optional(optional($payout->vendor)->country)->currency_code ?? 'INR' }} {{ number_format($payout->commission_amount, 2) }}</td>
                                <td class="fw-bold">{{ number_format($payout->payout_amount, 2) }}</td>
                                <td>
                            @php
                                $cls = 'bg-danger-subtle text-danger';
                                if ($payout->status === 'paid') $cls = 'bg-success-subtle text-success';
                                elseif ($payout->status === 'pending') $cls = 'bg-warning-subtle text-warning';
                                
                            @endphp
                                    <span class="badge px-2 py-1 {{ $cls }}">{{ ucfirst($payout->status) }}</span>
                                </td>
                                <td>
                                    @if($payout->paid_at)
                                        {{ \Carbon\Carbon::parse($payout->paid_at)->format('Y-m-d') }}
                                    @elseif($payout->created_at)
                                        {{ \Carbon\Carbon::parse($payout->created_at)->format('Y-m-d') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <a class="text-purple" href="{{ route('vendor.payouts.show', $payout->id) }}" title="View Detail">
                                        <iconify-icon icon="solar:eye-linear" class="fs-20"></iconify-icon>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No payout records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($payouts instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="card-footer bg-transparent border-0">
                <div class="d-flex justify-content-end">
                    {{ $payouts->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('vendorPayoutFilterForm');
        const statusSelect = form.querySelector('select[name="status"]');
        const dateInput = form.querySelector('input[name="date_range"]');
        statusSelect.addEventListener('change', () => form.submit());
        if (typeof initDateRangePicker === 'function') {
            initDateRangePicker('.range-datepicker');
        }
        if (typeof $ !== 'undefined' && $(dateInput).data('daterangepicker')) {
            $(dateInput).on('apply.daterangepicker', function() { form.submit(); });
        } else {
            dateInput.addEventListener('change', () => form.submit());
        }
        document.getElementById('btnVendorPayoutClear').addEventListener('click', function() {
            form.querySelector('[name="status"]').value = '';
            form.querySelector('[name="date_range"]').value = '';
            form.submit();
        });

        const checkAll = document.getElementById('checkAllVendorPayouts');
        checkAll && checkAll.addEventListener('change', function() {
            document.querySelectorAll('.vendor-payout-checkbox').forEach(cb => cb.checked = checkAll.checked);
        });
        const btnExport = document.getElementById('btnVendorExportSelected');
        const idsInput = document.getElementById('vendorExportIds');
        btnExport && btnExport.addEventListener('click', function() {
            const ids = Array.from(document.querySelectorAll('.vendor-payout-checkbox:checked')).map(el => el.value);
            if (!ids.length) {
                alert('Select at least one payout to export');
                return;
            }
            idsInput.value = ids.join(',');
            document.getElementById('vendorExportSelectedForm').submit();
        });

        // Payout Frequency Change
        $('.payout-frequency-radio').on('change', function() {
            const freq = $(this).val();
            const $container = $(this).closest('.form-check');
            
            $.ajax({
                url: "{{ route('vendor.update.payout.frequency') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    frequency: freq
                },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        // Refresh UI
                        $('.form-check-inline').removeClass('border-primary bg-primary-subtle');
                        $container.addClass('border-primary bg-primary-subtle');
                    } else {
                        toastr.error(response.message);
                        location.reload(); // Reset radio state
                    }
                },
                error: function() {
                    toastr.error('Failed to update payout frequency.');
                    location.reload();
                }
            });
        });
    });
</script>
@endsection
