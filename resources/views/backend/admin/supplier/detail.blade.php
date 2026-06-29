@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Supplier: {{ $supplier->name }}</h4>
                    <div class="page-title-right">
                        <a href="{{ route('supplier.index') }}" class="btn btn-sm btn-outline-secondary">Back to Suppliers</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $supplier->name }}</h5>
                        <hr>
                        <div class="small">
                            @if($supplier->company_name)<p class="mb-1"><strong>Company:</strong> {{ $supplier->company_name }}</p>@endif
                            @if($supplier->contact_person)<p class="mb-1"><strong>Contact:</strong> {{ $supplier->contact_person }}</p>@endif
                            @if($supplier->email)<p class="mb-1"><strong>Email:</strong> {{ $supplier->email }}</p>@endif
                            @if($supplier->phone)<p class="mb-1"><strong>Phone:</strong> {{ $supplier->phone }}</p>@endif
                            @if($supplier->address)<p class="mb-1"><strong>Address:</strong> {{ $supplier->address }}</p>@endif
                            @if($supplier->city || $supplier->state)<p class="mb-1"><strong>City/State:</strong> {{ $supplier->city }}, {{ $supplier->state }}</p>@endif
                            @if($supplier->country)<p class="mb-1"><strong>Country:</strong> {{ $supplier->country }}</p>@endif
                            @if($supplier->gst_number)<p class="mb-1"><strong>GST:</strong> {{ $supplier->gst_number }}</p>@endif
                            <p class="mb-0"><strong>Status:</strong> <span class="badge {{ $supplier->status ? 'bg-success' : 'bg-secondary' }}">{{ $supplier->status ? 'Active' : 'Inactive' }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Linked Products</h5>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#linkProductModal">+ Link Product</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle"><tr><th>Product</th><th>Supplier SKU</th><th>Supply Price</th><th>Lead Time</th><th>Preferred</th><th>Action</th></tr></thead>
                                <tbody>
                                    @forelse($supplier->products as $p)
                                    <tr>
                                        <td>{{ $p->name }}</td>
                                        <td>{{ $p->pivot->supplier_sku ?? '—' }}</td>
                                        <td>{{ number_format($p->pivot->supply_price, 2) }}</td>
                                        <td>{{ $p->pivot->lead_time_days }} days</td>
                                        <td><span class="badge {{ $p->pivot->is_preferred ? 'bg-success' : 'bg-secondary' }}">{{ $p->pivot->is_preferred ? 'Yes' : 'No' }}</span></td>
                                        <td>
                                            <form action="{{ route('supplier.product.unlink', [$supplier->id, $p->id]) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger" onclick="return confirm('Unlink this product?')">Unlink</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center py-3 text-muted">No products linked</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Purchase Orders</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle"><tr><th>PO #</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                                <tbody>
                                    @forelse($supplier->purchaseOrders as $po)
                                    <tr>
                                        <td><a href="{{ route('supplier.purchase.order.detail', $po->id) }}" class="text-decoration-none fw-medium">{{ $po->order_number }}</a></td>
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
                                        <td><small class="text-muted">{{ $po->created_at->format('d M Y') }}</small></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center py-3 text-muted">No POs yet</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Link Product Modal -->
<div class="modal fade" id="linkProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('supplier.product.link') }}" method="POST">
                @csrf
                <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
                <div class="modal-header"><h5 class="modal-title">Link Product</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Product</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Select Product</option>
                            @foreach($allProducts as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Supplier SKU</label>
                        <input type="text" name="supplier_sku" class="form-control" maxlength="255">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Supply Price</label>
                            <input type="number" name="supply_price" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lead Time (days)</label>
                            <input type="number" name="lead_time_days" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_preferred" value="1" id="is_preferred">
                        <label class="form-check-label" for="is_preferred">Preferred Supplier</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Link Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
