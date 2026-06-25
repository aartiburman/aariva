@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12 d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Vendor Requests – {{ $campaign->name }}</h4>
                <a href="{{ route('campaign.list') }}" class="btn btn-secondary btn-sm">Back to Campaigns</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form id="bulk-action-form" action="{{ route('campaign.vendor.bulk-action', $campaign->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" id="bulk-action-input" value="">
                    
                    <div class="mb-3 d-flex gap-2 d-none" id="bulk-action-buttons">
                        <button type="button" class="btn btn-success btn-sm bulk-btn" data-action="approve">Bulk Approve</button>
                        <button type="button" class="btn btn-danger btn-sm bulk-btn" data-action="reject">Bulk Reject</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all" class="form-check-input">
                                    </th>
                                    <th>No.</th>
                                    <th>Vendor</th>
                                    <th>Budget</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vendors as $key => $v)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="vendor_ids[]" value="{{ $v->vendor_id }}" class="form-check-input vendor-checkbox">
                                        </td>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $v->store_name ?? $v->name }}</td>
                                        <td>{{ number_format($v->budget_total, 2) }}</td>
                                        <td>
                                            @if(($v->status ?? '') === 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif(($v->status ?? '') === 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @elseif(($v->status ?? '') === 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $v->status ?? 'N/A' }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if(($v->status ?? '') === 'pending')
                                                <button type="button" class="btn btn-success btn-sm single-approve" data-id="{{ $v->vendor_id }}">Approve</button>
                                                <button type="button" class="btn btn-outline-danger btn-sm single-reject" data-id="{{ $v->vendor_id }}">Reject</button>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No vendor requests.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
                <div class="mt-3 d-flex justify-content-end">
                    {{ $vendors->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<form id="single-action-form" action="" method="POST" style="display:none;">
    @csrf
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.vendor-checkbox');
    const bulkActionForm = document.getElementById('bulk-action-form');
    const bulkActionInput = document.getElementById('bulk-action-input');
    const bulkBtns = document.querySelectorAll('.bulk-btn');
    const bulkBtnWrapper = document.getElementById('bulk-action-buttons');

    function updateBulkButtonVisibility() {
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        if (anyChecked) {
            bulkBtnWrapper.classList.remove('d-none');
        } else {
            bulkBtnWrapper.classList.add('d-none');
        }
    }

    // Select All logic
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkButtonVisibility();
        });
    }

    // Individual checkbox logic
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkButtonVisibility);
    });

    // Bulk Action Button Clicks
    bulkBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            const actionText = action === 'approve' ? 'Approve' : 'Reject';
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You want to ${actionText.toLowerCase()} all selected vendors?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: action === 'approve' ? '#28a745' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, ${actionText} them!`
            }).then((result) => {
                if (result.isConfirmed) {
                    bulkActionInput.value = action;
                    bulkActionForm.submit();
                }
            });
        });
    });

    // Single Action Button Clicks
    const singleActionForm = document.getElementById('single-action-form');
    
    document.querySelectorAll('.single-approve').forEach(btn => {
        btn.addEventListener('click', function() {
            const vendorId = this.getAttribute('data-id');
            const url = `{{ url('campaign') }}/{{ $campaign->id }}/vendor/${vendorId}/approve`;
            
            Swal.fire({
                title: 'Approve Vendor?',
                text: "This vendor will be joined to the campaign.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, approve!'
            }).then((result) => {
                if (result.isConfirmed) {
                    singleActionForm.action = url;
                    singleActionForm.submit();
                }
            });
        });
    });

    document.querySelectorAll('.single-reject').forEach(btn => {
        btn.addEventListener('click', function() {
            const vendorId = this.getAttribute('data-id');
            const url = `{{ url('campaign') }}/{{ $campaign->id }}/vendor/${vendorId}/reject`;
            
            Swal.fire({
                title: 'Reject Request?',
                text: "The vendor request will be rejected.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, reject!'
            }).then((result) => {
                if (result.isConfirmed) {
                    singleActionForm.action = url;
                    singleActionForm.submit();
                }
            });
        });
    });
});
</script>
@endpush
