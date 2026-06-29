@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Purchase Orders</h4>
                    <div class="page-title-right">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createPOModal">
                            <iconify-icon icon="solar:add-circle-linear"></iconify-icon> New Purchase Order
                        </button>
                        <a href="{{ route('supplier.index') }}" class="btn btn-sm btn-outline-secondary">Suppliers</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" id="filter-form" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Range</label>
                        <input type="text" name="date_range" class="form-control" id="date_range" value="{{ request('date_range') }}" placeholder="Select date range">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="bg-light-subtle">
                            <tr><th>PO #</th><th>Supplier</th><th>Items</th><th>Total</th><th>Status</th><th>Created By</th><th>Date</th><th>Action</th></tr>
                        </thead>
                        <tbody id="table-body">
                            @include('backend.admin.supplier.partials.purchase-orders-table')
                        </tbody>
                    </table>
                </div>
            </div>
            @if($orders->hasPages())<div class="card-footer">{{ $orders->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>

<!-- Create PO Modal -->
<div class="modal fade" id="createPOModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('supplier.purchase.order.store') }}" method="POST" id="poForm">
                @csrf
                <div class="modal-header"><h5 class="modal-title">New Purchase Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Supplier</label>
                            <select name="supplier_id" class="form-select" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expected Delivery</label>
                            <input type="date" name="expected_at" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" maxlength="2000"></textarea>
                    </div>
                    <hr>
                    <h6>Order Items</h6>
                    <div id="poItems">
                        <div class="po-item row g-2 mb-2">
                            <div class="col-md-5">
                                <select name="items[0][product_id]" class="form-select" required>
                                    <option value="">Select Product</option>
                                    @foreach(\App\Models\Product::where('status', 1)->orderBy('name')->get() as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="items[0][quantity]" class="form-control" placeholder="Qty" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="items[0][unit_price]" class="form-control" placeholder="Unit Price" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100 remove-po-item" style="display:none;">✕</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addPoItem">+ Add Item</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Purchase Order</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let itemIndex = 1;
    $('#addPoItem').on('click', function() {
        const html = `<div class="po-item row g-2 mb-2">
            <div class="col-md-5">
                <select name="items[${itemIndex}][product_id]" class="form-select" required>
                    <option value="">Select Product</option>
                    @foreach(\App\Models\Product::where('status', 1)->orderBy('name')->get() as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="Qty" min="1" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="items[${itemIndex}][unit_price]" class="form-control" placeholder="Unit Price" step="0.01" min="0" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger w-100 remove-po-item">✕</button>
            </div>
        </div>`;
        $('#poItems').append(html);
        itemIndex++;
        toggleRemoveButtons();
    });

    $(document).on('click', '.remove-po-item', function() {
        $(this).closest('.po-item').remove();
        toggleRemoveButtons();
    });

    function toggleRemoveButtons() {
        $('.remove-po-item').each(function() {
            $(this).toggle($(this).closest('.po-item').index() > 0);
        });
    }

    // Auto-fetch product variants? For now just product selection
    // Disable filter-form default submit for AJAX if needed
});
</script>
@endpush
