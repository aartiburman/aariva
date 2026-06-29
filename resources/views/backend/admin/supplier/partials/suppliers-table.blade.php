@forelse($suppliers as $s)
<tr>
    <td><a href="{{ route('supplier.detail', $s->id) }}" class="text-decoration-none fw-medium">{{ $s->name }}</a></td>
    <td>{{ $s->company_name ?? '—' }}</td>
    <td>{{ $s->contact_person ?? '—' }}</td>
    <td><small>{{ $s->email ?? '—' }}</small></td>
    <td>{{ $s->phone ?? '—' }}</td>
    <td><span class="badge bg-info">{{ $s->products_count ?? $s->products()->count() }}</span></td>
    <td><span class="badge {{ $s->status ? 'bg-success' : 'bg-secondary' }}">{{ $s->status ? 'Active' : 'Inactive' }}</span></td>
    <td>
        <button class="btn btn-sm btn-soft-primary edit-supplier" data-supplier='@json($s)'>
            <iconify-icon icon="solar:pen-linear"></iconify-icon>
        </button>
        <form action="{{ route('supplier.destroy', $s->id) }}" method="POST" class="d-inline delete-form">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-soft-danger">
                <iconify-icon icon="solar:trash-bin-trash-linear"></iconify-icon>
            </button>
        </form>
    </td>
</tr>
@empty
<tr><td colspan="8" class="text-center py-4 text-muted">No suppliers found</td></tr>
@endforelse
