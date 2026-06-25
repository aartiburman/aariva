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