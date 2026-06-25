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
    <td class="fs-13 fw-bold text-primary">{{ number_format($payout->vendor->wallet_balance ?? 0, 2) }}</td>
    <td class="fs-13">{{ (int) ($payout->items_qty ?? 0) }}</td>
    <td class="fs-13">{{ optional(optional($payout->vendor)->country)->currency_code ?? 'NPR' }} {{ number_format($payout->order_amount, 2) }}</td>
    <td class="fs-13">{{ optional(optional($payout->vendor)->country)->currency_code ?? 'NPR' }} {{ number_format($payout->commission_amount, 2) }}</td>
    <td class="fs-13">{{ optional(optional($payout->vendor)->country)->currency_code ?? 'NPR' }} {{ number_format($payout->pg_fee_amount, 2) }}</td>
    <td class="fs-13 fw-bold text-dark">{{ optional(optional($payout->vendor)->country)->currency_code ?? 'NPR' }} {{ number_format($payout->payout_amount, 2) }}</td>
    <td>
        <span class="badge bg-primary-subtle text-primary px-2 py-1 payment-mode-label">
            {{ ucfirst($payout->payment_method ?? 'Wallet') }}
        </span>
    </td>
    <td class="text-center">
        @php
            $status = trim((string)($payout->status ?? 'unpaid'));
            if ($status === '') $status = 'unpaid';
            $label = ucfirst($status);
            $cls = 'bg-danger-subtle text-danger';
            if ($status === 'paid') $cls = 'bg-success-subtle text-success';
        @endphp
        @if($status === 'unpaid')
            <a href="javascript:void(0);" class="status-badge badge px-2 py-1 {{ $cls }} mark-as-paid-btn" data-id="{{ $payout->id }}" title="Click to mark as paid">
                {{ $label }}
            </a>
        @else
            <span class="status-badge badge px-2 py-1 bg-success-subtle text-success" style="cursor: not-allowed; opacity: 0.75; pointer-events: none; user-select: none;" title="This payout has been paid and cannot be modified">
                {{ $label }}
            </span>
        @endif
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
    <td colspan="14" class="text-center py-5 text-muted">
        No payout records found.
    </td>
</tr>
@endforelse