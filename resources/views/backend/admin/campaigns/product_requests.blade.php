@extends('backend.layouts.app')

@section('content')
<div class="page-content">
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Product Requests — {{ $campaign->name }}</h4>
            <a href="{{ route('campaign.list') }}" class="btn btn-secondary btn-sm">Back to Campaigns</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form id="bulk-action-form" action="{{ route('campaign.product.bulk-action', $campaign->id) }}" method="POST">
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
                                <th style="width: 40px;">
                                    <input type="checkbox" id="select-all" class="form-check-input">
                                </th>
                                <th>No.</th>
                                <th>Product</th>
                                <th>Vendor</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $key => $p)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="product_ids[]" value="{{ $p->id }}" class="form-check-input product-checkbox">
                                        </td>
                                        <td>{{ $key + 1 }}</td>
                                    <td>{{ $p->name }}</td>
                                    <td>{{ $p->vendor_name }}</td>
                                    <td>
                                        <span class="badge {{ $p->status == 1 ? 'bg-success' : ($p->status == 2 ? 'bg-danger' : 'bg-warning') }}">
                                            {{ $p->status == 1 ? 'Approved' : ($p->status == 2 ? 'Rejected' : 'Pending') }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($p->status == 0)
                                            <button type="button" class="btn btn-success btn-sm single-approve" data-id="{{ $p->id }}">Approve</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm single-reject" data-id="{{ $p->id }}">Reject</button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm single-delete" data-id="{{ $p->id }}">Delete</button>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No product requests.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
            <div class="mt-3 d-flex justify-content-end">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
</div>

{{-- Hidden forms for single actions --}}
<form id="single-approve-form" method="POST" style="display:none;">
    @csrf
</form>
<form id="single-reject-form" method="POST" style="display:none;">
    @csrf
</form>
<form id="single-delete-form" method="POST" style="display:none;">
    @csrf
</form>

@endsection

@push('chart-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.product-checkbox');
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

    // Bulk action buttons
    bulkBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            const selected = document.querySelectorAll('.product-checkbox:checked');
            
            if (selected.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select at least one product.',
                });
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: `You want to ${action} all selected products?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: action === 'approve' ? '#28a745' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, ${action} them!`
            }).then((result) => {
                if (result.isConfirmed) {
                    bulkActionInput.value = action;
                    bulkActionForm.submit();
                }
            });
        });
    });

    // Single action handlers
    document.querySelectorAll('.single-approve').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const form = document.getElementById('single-approve-form');
            
            Swal.fire({
                title: 'Approve Product?',
                text: "This product will be added to the campaign.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, approve!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.action = `{{ url('campaign/'.$campaign->id.'/product') }}/${id}/approve`;
                    form.submit();
                }
            });
        });
    });

    document.querySelectorAll('.single-reject').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const form = document.getElementById('single-reject-form');

            Swal.fire({
                title: 'Reject Product?',
                text: "This product will not be part of the campaign.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, reject!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.action = `{{ url('campaign/'.$campaign->id.'/product') }}/${id}/reject`;
                    form.submit();
                }
            });
        });
    });

    document.querySelectorAll('.single-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const form = document.getElementById('single-delete-form');

            Swal.fire({
                title: 'Delete Product Request?',
                text: "This will remove the product from this campaign request list.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Yes, delete!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.action = `{{ url('campaign/'.$campaign->id.'/product') }}/${id}/delete`;
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush
