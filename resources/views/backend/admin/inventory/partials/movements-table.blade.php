@forelse($movements as $m)
<tr>
    <td>{{ $loop->iteration + ($movements->currentPage() - 1) * $movements->perPage() }}</td>
    <td>
        <span class="badge @if($m->type == 'in') bg-success @elseif($m->type == 'out') bg-danger @else bg-info @endif">
            {{ ucfirst($m->type) }}
        </span>
    </td>
    <td>
        <a href="{{ route('product.detail', $m->variant->product_id ?? 0) }}" class="text-decoration-none fw-medium">
            {{ $m->variant->product->name ?? 'Deleted' }}
        </a>
        @if($m->variant)
        <br><small class="text-muted">SKU: {{ $m->variant->sku }}</small>
        @endif
    </td>
    <td>{{ $m->warehouse->name ?? 'N/A' }}</td>
    <td>{{ $m->quantity }}</td>
    <td>{{ $m->stock_before }}</td>
    <td>{{ $m->stock_after }}</td>
    <td><small>{{ $m->reason }}</small></td>
    <td><small>{{ $m->user->name ?? 'System' }}</small></td>
    <td><small class="text-muted">{{ $m->created_at->format('d M Y H:i') }}</small></td>
</tr>
@empty
<tr><td colspan="10" class="text-center py-4 text-muted">No stock movements found</td></tr>
@endforelse
