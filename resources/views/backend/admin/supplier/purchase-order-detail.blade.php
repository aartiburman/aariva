@extends('backend.layouts.app')
@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Purchase Order: {{ $order->order_number }}</h4>
                    <div class="page-title-right">
                        <a href="{{ route('supplier.purchase.orders') }}" class="btn btn-sm btn-outline-secondary">Back</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Items</h5>
                        @if($order->status == 'approved')
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#receivePOModal">Receive Stock</button>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr><th>Product</th><th>Qty Ordered</th><th>Unit Price</th><th>Total</th><th>Received</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format($item->unit_price, 2) }}</td>
                                        <td>{{ number_format($item->total_price, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $item->received_quantity >= $item->quantity ? 'bg-success' : 'bg-warning' }}">
                                                {{ $item->received_quantity }} / {{ $item->quantity }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end">Sub Total:</th>
                                        <th>{{ number_format($order->sub_total, 2) }}</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th>{{ number_format($order->total, 2) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h6>Order Details</h6>
                        <hr>
                        <div class="small">
                            <p class="mb-1"><strong>PO #:</strong> {{ $order->order_number }}</p>
                            <p class="mb-1"><strong>Supplier:</strong> <a href="{{ route('supplier.detail', $order->supplier_id) }}">{{ $order->supplier->name }}</a></p>
                            <p class="mb-1"><strong>Created By:</strong> {{ $order->user->name ?? 'System' }}</p>
                            <p class="mb-1"><strong>Warehouse:</strong> {{ $order->warehouse->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Status:</strong>
                                @php $cls = match($order->status) { 'draft'=>'secondary', 'pending'=>'warning', 'approved'=>'info', 'received'=>'success', 'cancelled'=>'danger', default=>'secondary' }; @endphp
                                <span class="badge bg-{{ $cls }}">{{ ucfirst($order->status) }}</span>
                            </p>
                            @if($order->expected_at)<p class="mb-1"><strong>Expected:</strong> {{ $order->expected_at->format('d M Y') }}</p>@endif
                            @if($order->received_at)<p class="mb-1"><strong>Received:</strong> {{ $order->received_at->format('d M Y H:i') }}</p>@endif
                            @if($order->notes)<p class="mb-0"><strong>Notes:</strong><br>{{ $order->notes }}</p>@endif
                        </div>
                        <hr>
                        @if(in_array($order->status, ['draft', 'pending']))
                        <form action="{{ route('supplier.purchase.order.status', $order->id) }}" method="POST" class="d-inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="{{ $order->status == 'draft' ? 'pending' : 'approved' }}">
                            <button type="submit" class="btn btn-sm btn-{{ $order->status == 'draft' ? 'warning' : 'info' }} w-100 mb-2">
                                {{ $order->status == 'draft' ? 'Submit for Approval' : 'Approve Order' }}
                            </button>
                        </form>
                        @endif
                        @if(in_array($order->status, ['draft', 'pending']))
                        <form action="{{ route('supplier.purchase.order.status', $order->id) }}" method="POST" class="d-inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Cancel this PO?')">Cancel Order</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($order->status == 'approved')
<!-- Receive Stock Modal -->
<div class="modal fade" id="receivePOModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('supplier.purchase.order.receive', $order->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title">Receive Stock</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p>Enter the quantity received for each item:</p>
                    @foreach($order->items as $item)
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-md-6">
                            <strong>{{ $item->product->name ?? 'N/A' }}</strong>
                            <small class="text-muted">(Ordered: {{ $item->quantity }})</small>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="received_items[{{ $loop->index }}][id]" value="{{ $item->id }}" hidden>
                            <input type="number" name="received_items[{{ $loop->index }}][received_quantity]" class="form-control"
                                value="{{ $item->quantity }}" min="0" max="{{ $item->quantity }}" required>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">of {{ $item->quantity }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Receive Stock & Update Inventory</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
