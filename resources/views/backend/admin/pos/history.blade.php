@extends('backend.layouts.app')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">POS Order History</h4>
                        <a href="{{ route('pos.index') }}" class="btn btn-sm btn-primary">
                            <iconify-icon icon="solar:shop-2-linear" class="me-1"></iconify-icon> Go to POS
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                    <tr>
                                        <td>
                                            <span class="fw-medium">{{ $order->order_reference_id }}</span>
                                        </td>
                                        <td>{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                        <td>{{ $currencySymbol }} {{ number_format($order->total_cost, 2) }}</td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">{{ ucfirst($order->payment_mode) }}</span>
                                            @if($order->payment_mode == 'online')
                                                <button type="button" class="btn btn-sm btn-link p-0 ms-1" data-bs-toggle="modal" data-bs-target="#qrModal{{ $order->id }}">
                                                    <iconify-icon icon="solar:qr-code-linear"></iconify-icon>
                                                </button>

                                                <!-- QR Modal -->
                                                <div class="modal fade" id="qrModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Payment QR Code</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(url('/pay/' . $order->order_reference_id)) }}" alt="QR Code" class="img-fluid">
                                                                <p class="mt-2 mb-0">Order: {{ $order->order_reference_id }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success">{{ $order->order_status }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('pos.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-soft-primary">
                                                <iconify-icon icon="solar:printer-linear" class="me-1"></iconify-icon> Invoice
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <h5 class="text-muted">No POS orders found</h5>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($orders->hasPages())
                    <div class="card-footer border-top-0">
                        {{ $orders->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
