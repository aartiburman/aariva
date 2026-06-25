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
                
                <div class="mb-3 d-flex gap-2">
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
                                        @if($p->status == 'pending')
                                            <input type="checkbox" name="product_ids[]" value="{{ $p->id }}" class="form-check-input product-checkbox">
                                        @endif
                                    </td>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $p->name }}</td>
                                    <td>{{ $p->vendor_name }}</td>
                                    <td>
                                        <span class="badge {{ $p->status == 'approved' ? 'bg-success' : ($p->status == 'rejected' ? 'bg-danger' : 'bg-warning') }}">
                                            {{ ucfirst($p->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($p->status == 'pending')
                                            <button type="button" class="btn btn-success btn-sm single-approve" data-id="{{ $p->id }}">Approve</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm single-reject" data-id="{{ $p->id }}">Reject</button>
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

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const bulkActionForm = document.getElementById('bulk-action-form');
    const bulkActionInput = document.getElementById('bulk-action-input');
    const bulkBtns = document.querySelectorAll('.bulk-btn');

    // Select All logic
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    // Bulk action buttons
    bulkBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            const selected = document.querySelectorAll('.product-checkbox:checked');
            
            if (selected.length === 0) {
                alert('Please select at least one product.');
                return;
            }

            if (confirm(`Are you sure you want to ${action} selected products?`)) {
                bulkActionInput.value = action;
                bulkActionForm.submit();
            }
        });
    });

    // Single action handlers
    document.querySelectorAll('.single-approve').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const form = document.getElementById('single-approve-form');
            form.action = `{{ url('campaign/'.$campaign->id.'/product') }}/${id}/approve`;
            form.submit();
        });
    });

    document.querySelectorAll('.single-reject').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Reject this product?')) return;
            const id = this.dataset.id;
            const form = document.getElementById('single-reject-form');
            form.action = `{{ url('campaign/'.$campaign->id.'/product') }}/${id}/reject`;
            form.submit();
        });
    });
});
</script>
@endpush
