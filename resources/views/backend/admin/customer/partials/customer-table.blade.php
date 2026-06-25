@foreach ($customers as $value)
<tr id="row_{{ $value->id }}">
    <td>
        <div class="form-check ms-1">
            <input type="checkbox" class="form-check-input" id="customCheck{{ $value->id }}">
            <label class="form-check-label" for="customCheck{{ $value->id }}"></label>
        </div>
    </td>
    <td>{{ $value->name }}</td>
    <td>{{ $value->email }}</td>
    <td>{{ $value->phone }}</td>
    <td>{{ $value->gender ?? 'N/A' }}</td>
    <td>
        <div class="customer-status-container" data-id="{{ $value->id }}" style="cursor: pointer;">
            @if($value->status == 1)
            <span class="badge bg-success-subtle text-success py-1 px-2 fs-11 text-uppercase customer-status-badge">Active</span>
            @elseif($value->status == 0)
            <span class="badge bg-warning-subtle text-warning py-1 px-2 fs-11 text-uppercase customer-status-badge">Pending</span>
            @else
            <span class="badge bg-danger-subtle text-danger py-1 px-2 fs-11 text-uppercase customer-status-badge">Rejected</span>
            @endif

            <select class="form-select form-select-sm customer-status-select d-none">
                <option value="1" @selected($value->status == 1)>Active</option>
                <option value="0" @selected($value->status == 0)>Pending</option>
                <option value="2" @selected($value->status == 2)>Reject</option>
            </select>
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('customer.detail', $value->id) }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="View Detail">
                <iconify-icon icon="solar:eye-linear" class="fs-20"></iconify-icon>
            </a>
            <a href="javascript:void(0);" class="text-purple hover-opacity-100 delete-customer" data-id="{{ $value->id }}" data-bs-toggle="tooltip" title="Delete">
                <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-20"></iconify-icon>
            </a>
        </div>
    </td>
</tr>
@endforeach
@if($customers->isEmpty())
<tr>
    <td colspan="7" class="text-center py-5 text-muted">No customers found.</td>
</tr>
@endif