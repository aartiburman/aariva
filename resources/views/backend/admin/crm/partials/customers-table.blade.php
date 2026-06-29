@forelse($customers as $c)
<tr>
    <td>{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
    <td>
        <a href="{{ route('crm.customer.detail', $c->id) }}" class="text-decoration-none fw-medium">
            {{ $c->name }}
        </a>
    </td>
    <td><small>{{ $c->email }}</small></td>
    <td>{{ $c->phone ?? '—' }}</td>
    <td><span class="badge bg-info">{{ $c->orders_count ?? $c->orders()->count() }}</span></td>
    <td>
        <span class="badge {{ $c->status == 1 ? 'bg-success' : 'bg-danger' }}">
            {{ $c->status == 1 ? 'Active' : 'Inactive' }}
        </span>
    </td>
    <td><small class="text-muted">{{ $c->created_at->format('d M Y') }}</small></td>
    <td>
        <a href="{{ route('crm.customer.detail', $c->id) }}" class="btn btn-sm btn-soft-primary">
            <iconify-icon icon="solar:eye-linear"></iconify-icon>
        </a>
    </td>
</tr>
@empty
<tr><td colspan="8" class="text-center py-4 text-muted">No customers found</td></tr>
@endforelse
