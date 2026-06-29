@forelse($orders as $po)
<tr>
    <td><a href="{{ route('supplier.purchase.order.detail', $po->id) }}" class="text-decoration-none fw-medium">{{ $po->order_number }}</a></td>
    <td>{{ $po->supplier->name ?? 'Deleted' }}</td>
    <td>{{ $po->items->count() }}</td>
    <td>{{ number_format($po->total, 2) }}</td>
    <td>
        @php
        $cls = match($po->status) {
            'draft' => 'secondary', 'pending' => 'warning',
            'approved' => 'info', 'received' => 'success',
            'cancelled' => 'danger', default => 'secondary'
        };
        @endphp
        <span class="badge bg-{{ $cls }}">{{ ucfirst($po->status) }}</span>
    </td>
    <td><small>{{ $po->user->name ?? 'System' }}</small></td>
    <td><small class="text-muted">{{ $po->created_at->format('d M Y') }}</small></td>
    <td><a href="{{ route('supplier.purchase.order.detail', $po->id) }}" class="btn btn-sm btn-soft-primary"><iconify-icon icon="solar:eye-linear"></iconify-icon></a></td>
</tr>
@empty
<tr><td colspan="8" class="text-center py-4 text-muted">No purchase orders</td></tr>
@endforelse
