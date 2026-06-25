@forelse($vendors as $vendor)
<tr>
    <td class="ps-4">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-sm">
                <img src="{{ $vendor->image }}" alt="" class="rounded-circle" width="32" height="32">
            </div>
            <div>
                <h6 class="mb-0 fw-bold text-dark fs-14">{{ $vendor->store_name ?? $vendor->name }}</h6>
                <small class="text-muted fs-12">{{ $vendor->email }}</small>
            </div>
        </div>
    </td>
    <td>
        @if($vendor->documents->count() > 0)
        <div class="d-flex flex-column gap-1">
            @foreach($vendor->documents as $doc)
            <a href="{{ $doc->formatted_path }}" target="_blank" class="btn btn-xs btn-soft-info d-flex align-items-center gap-1 py-1 px-2 fs-11" style="width: fit-content;">
                <iconify-icon icon="solar:document-linear" width="14"></iconify-icon>
                Doc #{{ $loop->iteration }}
            </a>
            @endforeach
        </div>
        @else
        <span class="text-muted fs-12 italic">No documents</span>
        @endif
    </td>
    <td>
        @if($vendor->status == 1)
        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fs-11 text-uppercase">Verified</span>
        @elseif($vendor->status == 0)
        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fs-11 text-uppercase">Pending</span>
        @else
        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fs-11 text-uppercase">Rejected</span>
        @endif
    </td>
    <td class="text-muted text-nowrap">
        {{ $vendor->last_upload ? $vendor->last_upload->created_at->format('M d, Y') : 'No uploads' }}
    </td>
    <td>
        <div class="text-dark fw-medium">{{ $vendor->tax_id ?? 'N/A' }}</div>
        <small class="text-muted">{{ $vendor->business_name ?? 'N/A' }}</small>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="text-center py-5">
        <div class="d-flex flex-column align-items-center">
            <iconify-icon icon="solar:folder-error-linear" width="64" class="text-muted mb-3"></iconify-icon>
            <h6 class="text-muted">No KYC records found</h6>
        </div>
    </td>
</tr>
@endforelse