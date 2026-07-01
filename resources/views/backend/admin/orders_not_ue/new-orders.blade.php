@extends('backend.layouts.app')

@section('content')
<div class="page-content">

    <!-- Start Container Fluid -->
    <div class="container-fluid">

        <div class="row">
            <div class="col-xl-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">New Orders List</h4>
                </div>
            </div>
        </div>

        <div class="row row-cols-xxl-6 row-cols-md-3 row-cols-1 g-3 mb-4">
            <div class="col">
                <a href="{{ route('pending.orders') }}" class="text-decoration-none h-100">
                    <div class="card border-0 shadow-sm h-100 mb-0">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 text-uppercase fs-11 fw-bold">Pending</p>
                                    <h2 class="text-dark mb-2 fw-bold fs-20">{{ $statusCounts->pending ?? 0 }}</h2>
                                    <div class="mt-4">
                                        <span class="text-warning fs-11 fw-medium">Requires Action</span>
                                    </div>
                                </div>
                                <div class="avatar-lg bg-soft-warning d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:clock-circle-linear" class="fs-32 text-warning"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Confirmed -->
            <div class="col">
                <a href="{{ route('confirmed.orders') }}" class="text-decoration-none h-100">
                    <div class="card border-0 shadow-sm h-100 mb-0">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 text-uppercase fs-11 fw-bold">Confirmed</p>
                                    <h2 class="text-dark mb-2 fw-bold fs-20">{{ $statusCounts->confirmed ?? 0 }}</h2>
                                    <div class="mt-4">
                                        <span class="text-info fs-11">Order Confirmed</span>
                                    </div>
                                </div>
                                <div class="avatar-lg bg-soft-info d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:check-circle-linear" class="fs-32 text-info"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Shipped -->
            <div class="col">
                <a href="{{ route('shipped.orders') }}" class="text-decoration-none h-100">
                    <div class="card border-0 shadow-sm h-100 mb-0">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 text-uppercase fs-11 fw-bold">Shipped</p>
                                    <h2 class="text-dark mb-2 fw-bold fs-20">{{ $statusCounts->shipped ?? 0 }}</h2>
                                    <div class="mt-4">
                                        <span class="text-primary fs-11">In Transit</span>
                                    </div>
                                </div>
                                <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-centerr">
                                    <iconify-icon icon="solar:box-linear" class="fs-32 text-primary"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Delivered -->
            <div class="col">
                <a href="{{ route('delivered.orders')}}" class="text-decoration-none h-100">
                    <div class="card border-0 shadow-sm h-100 mb-0">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 text-uppercase fs-11 fw-bold">Delivered</p>
                                    <h2 class="text-dark mb-2 fw-bold fs-20">{{ $statusCounts->delivered ?? 0 }}</h2>
                                    <div class="mt-4">
                                        <span class="text-success fs-11">Successfully Delivered</span>
                                    </div>
                                </div>
                                <div class="avatar-lg bg-soft-success d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:check-circle-linear" class="fs-32 text-success"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Cancelled -->
            <div class="col">
                <a href="{{ route('cancelled.orders') }}" class="text-decoration-none h-100">
                    <div class="card border-0 shadow-sm h-100 mb-0">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 text-uppercase fs-11 fw-bold">Cancelled</p>
                                    <h2 class="text-dark mb-2 fw-bold fs-20">{{ $statusCounts->cancelled ?? 0 }}</h2>
                                    <div class="mt-4">
                                        <span class="text-danger fs-11">Order Cancelled</span>
                                    </div>
                                </div>
                                <div class="avatar-lg bg-soft-danger d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:close-circle-linear" class="fs-32 text-danger"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Returned -->
            <div class="col">
                <a href="{{ route('returned.orders') }}" class="text-decoration-none h-100">
                    <div class="card border-0 shadow-sm h-100 mb-0">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 text-uppercase fs-11 fw-bold">Returned</p>
                                    <h2 class="text-dark mb-2 fw-bold fs-20">{{ $statusCounts->returned ?? 0 }}</h2>
                                    <div class="mt-4">
                                        <span class="text-warning fs-11">Items Returned</span>
                                    </div>
                                </div>
                                <div class="avatar-lg bg-soft-warning d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:arrow-return-linear" class="fs-32 text-warning"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>


        <div class="row">
            <div class="col-xl-12">
                @include('backend.orders.partials._filters')
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-bottom-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">New Orders List</h4>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>#</th>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Payment Mode</th>
                                        <th>Payment Status</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Order Status</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $key => $item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td> 
                                            <a href="{{ route('orders.details', $item->order->order_reference_id ?? '') }}" class="fw-medium">#{{ $item->order->order_reference_id ?? 'N/A' }}</a>
                                            <br>
                                            <small class="text-muted">Vendor: {{ $item->vendor->name ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-2">
                                                    <div class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                        {{ substr($item->order->user->name ?? 'C', 0, 1) }}
                                                    </div>
                                                </div>
                                                {{ $item->order->user->name ?? 'Customer' }}
                                            </div>
                                        </td>
                                        <td>{{ $item->order->payment_mode ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ ($item->payment_status ?? 0) == 1 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }}">
                                                {{ ($item->payment_status ?? 0) == 1 ? 'Paid' : 'Unpaid' }}
                                            </span>
                                        </td>
                                        <td>{{ $item->product->name ?? 'Product' }} (Qty: {{ $item->quantity }})</td>
                                        <td class="fw-bold text-dark">
                                            {{ optional(optional($item->vendor)->country)->currency_code ?? 'INR' }} 
                                            {{ number_format($item->total_actual_price, 2) }}
                                        </td>
                                        <td>
                                            @php
                                            $order_status_val = $item->status ?? '0';
                                            $statusClass = 'bg-soft-primary text-primary';
                                            if($order_status_val == '0') $statusClass = 'bg-soft-warning text-warning';
                                            if($order_status_val == '1') $statusClass = 'bg-soft-info text-info';
                                            if($order_status_val == '2') $statusClass = 'bg-soft-primary text-primary';
                                            if($order_status_val == '3') $statusClass = 'bg-soft-success text-success';
                                            if($order_status_val == '4') $statusClass = 'bg-soft-secondary text-secondary';
                                            if($order_status_val == '5') $statusClass = 'bg-soft-danger text-danger';

                                            $order_status_text = "Unknown";
                                            if($order_status_val == '0') $order_status_text = "Pending";
                                            if($order_status_val == '1') $order_status_text = "Confirmed";
                                            if($order_status_val == '2') $order_status_text = "Shipped";
                                            if($order_status_val == '3') $order_status_text = "Delivered";
                                            if($order_status_val == '4') $order_status_text = "Cancelled";
                                            if($order_status_val == '5') $order_status_text = "Returned";
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ $order_status_text }}</span>
                                        </td>
                                        <td>{{ $item->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('orders.details', $item->order->order_reference_id ?? '') }}" class="btn btn-soft-primary btn-sm"><i class="bx bx-show fs-16"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No orders found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($orders->hasPages())
                    <div class="card-footer border-top">
                        {{ $orders->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
