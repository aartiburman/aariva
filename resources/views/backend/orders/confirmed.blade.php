@extends('backend.layouts.app')

@section('content')
<div class="page-content">

    <!-- Start Container Fluid -->
    <div class="container-fluid">

        <div class="row">
            <!-- All Orders -->
            <div class="col-md-6 col-xl-3">
                <a href="{{ route('new.orders') }}" class="text-decoration-none">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-2">All Orders</h4>
                                    <p class="text-muted fw-medium fs-22 mb-0">{{ $statusCounts->total ?? 0 }}</p>
                                </div>
                                <div class="avatar-md bg-soft-primary rounded">
                                    <iconify-icon icon="solar:clipboard-list-bold-duotone" class="avatar-title fs-32 text-primary"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Pending Orders -->
            <div class="col-md-6 col-xl-3">
                <a href="{{ route('pending.orders') }}" class="text-decoration-none">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-2">Pending</h4>
                                    <p class="text-muted fw-medium fs-22 mb-0">{{ $statusCounts->pending ?? 0 }}</p>
                                </div>
                                <div class="avatar-md bg-soft-warning rounded">
                                    <iconify-icon icon="solar:clock-circle-bold-duotone" class="avatar-title fs-32 text-warning"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Confirmed -->
            <div class="col-md-6 col-xl-3">
                <a href="{{ route('confirmed.orders') }}" class="text-decoration-none">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-2">Confirmed</h4>
                                    <p class="text-muted fw-medium fs-22 mb-0">{{ $statusCounts->confirmed ?? 0 }}</p>
                                </div>
                                <div class="avatar-md bg-soft-info rounded">
                                    <iconify-icon icon="solar:check-circle-bold-duotone" class="avatar-title fs-32 text-info"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Confirmed (again quick link) -->
            <div class="col-md-6 col-xl-3">
                <a href="{{ route('confirmed.orders') }}" class="text-decoration-none">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-2">Confirmed</h4>
                                    <p class="text-muted fw-medium fs-22 mb-0">{{ $statusCounts->confirmed ?? 0 }}</p>
                                </div>
                                <div class="avatar-md bg-soft-primary rounded">
                                    <iconify-icon icon="solar:refresh-circle-bold-duotone" class="avatar-title fs-32 text-primary"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Shipped -->
            <div class="col-md-6 col-xl-3">
                <a href="{{ route('shipped.orders') }}" class="text-decoration-none">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-2">Shipped</h4>
                                    <p class="text-muted fw-medium fs-22 mb-0">{{ $statusCounts->shipped ?? 0 }}</p>
                                </div>
                                <div class="avatar-md bg-soft-primary rounded">
                                    <iconify-icon icon="solar:box-bold-duotone" class="avatar-title fs-32 text-primary"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Delivered -->
            <div class="col-md-6 col-xl-3">
                <a href="{{ route('delivered.orders') }}" class="text-decoration-none">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-2">Delivered</h4>
                                    <p class="text-muted fw-medium fs-22 mb-0">{{ $statusCounts->delivered ?? 0 }}</p>
                                </div>
                                <div class="avatar-md bg-soft-success rounded">
                                    <iconify-icon icon="solar:check-circle-bold-duotone" class="avatar-title fs-32 text-success"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Cancelled -->
            <div class="col-md-6 col-xl-3">
                <a href="{{ route('cancelled.orders') }}" class="text-decoration-none">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-2">Cancelled</h4>
                                    <p class="text-muted fw-medium fs-22 mb-0">{{ $statusCounts->cancelled ?? 0 }}</p>
                                </div>
                                <div class="avatar-md bg-soft-danger rounded">
                                    <iconify-icon icon="solar:close-circle-bold-duotone" class="avatar-title fs-32 text-danger"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">All Orders List</h4>
                        <div class="d-flex align-items-center gap-2">
                            <form action="{{ route('new.orders') }}" method="GET" class="d-flex align-items-center">
                                <input type="text" name="search" class="form-control form-control-sm me-1" placeholder="Search orders..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-sm btn-primary">Search</button>
                                @if(request('search'))
                                    <a href="{{ route('new.orders') }}" class="btn btn-sm btn-secondary ms-1">Clear</a>
                                @endif
                            </form>
                            <button class="btn btn-sm btn-soft-primary">Filter By Month <i class="bx bx-chevron-down"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Created At</th>
                                        <th>Customer</th>
                                        <th>Priority</th>
                                        <th>Payment Status</th>
                                        <th>Items</th>
                                        <th>Delivery Number</th>
                                        <th>Order Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                    <tr>
                                        <td><a href="javascript:void(0);" class="fw-medium">#{{ $order->order_reference_id }}</a></td>
                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-2">
                                                    <div class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                        {{ substr($order->user->name ?? 'C', 0, 1) }}
                                                    </div>
                                                </div>
                                                {{ $order->user->name ?? 'Customer' }}
                                            </div>
                                        </td>
                                        <td><span class="badge bg-soft-info text-info">Normal</span></td>
                                        <td>
                                            <!-- Badge -->
                                            <span
                                                class="badge payment-status-badge {{ $order->payment_status == 1 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }}"
                                                data-order-id="{{ $order->id }}"
                                                style="cursor:pointer">
                                                {{ $order->payment_status == 1 ? 'Paid' : 'Unpaid' }}
                                            </span>

                                            <!-- Select -->
                                            <select
                                                class="form-select form-select-sm payment-status-select d-none"
                                                data-order-id="{{ $order->id }}">
                                                <option value="0" {{ $order->payment_status == 0 ? 'selected' : '' }}>Unpaid</option>
                                                <option value="1" {{ $order->payment_status == 1 ? 'selected' : '' }}>Paid</option>
                                            </select>
                                        </td>

                                        <td>{{ $order->items->count() }}</td>
                                        <td>#{{ $order->delivery_number ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                            $statusClass = 'bg-soft-primary text-primary';
                                            if($order->order_status == '0') $statusClass = 'bg-soft-warning text-warning';
                                            if($order->order_status == '1') $statusClass = 'bg-soft-info text-info';
                                            if($order->order_status == '2') $statusClass = 'bg-soft-primary text-primary';
                                            if($order->order_status == '3') $statusClass = 'bg-soft-info text-info';
                                            if($order->order_status == '4') $statusClass = 'bg-soft-success text-success';
                                            if($order->order_status == '5') $statusClass = 'bg-soft-danger text-danger';

                                            $order_status = "Unknown";
                                            if($order->order_status == '0') $order_status = "Pending";
                                            if($order->order_status == '1') $order_status = "Confirmed";
                                            if($order->order_status == '2') $order_status = "Processing";
                                            if($order->order_status == '3') $order_status = "Shipped";
                                            if($order->order_status == '4') $order_status = "Delivered";
                                            if($order->order_status == '5') $order_status = "Cancelled";
                                            @endphp

                                            <!-- Badge -->
                                            <span
                                                class="badge {{ $statusClass }} order-status-badge"
                                                data-order-id="{{ $order->id }}"
                                                style="cursor:pointer">
                                                {{ ucfirst($order_status) }}
                                            </span>

                                            <!-- Select (hidden) -->
                                            <select
                                                class="form-select form-select-sm order-status-select d-none"
                                                data-order-id="{{ $order->id }}">
                                                <option value="0" {{ $order->order_status == '0' ? 'selected' : '' }}>Pending</option>
                                                <option value="1" {{ $order->order_status == '1' ? 'selected' : '' }}>Confirmed</option>
                                                <option value="2" {{ $order->order_status == '2' ? 'selected' : '' }}>Processing</option>
                                                <option value="3" {{ $order->order_status == '3' ? 'selected' : '' }}>Shipped</option>
                                                <option value="4" {{ $order->order_status == '4' ? 'selected' : '' }}>Delivered</option>
                                                <option value="5" {{ $order->order_status == '5' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </td>

                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('orders.details', $order->order_reference_id) }}" class="btn btn-soft-primary btn-sm"><i class="bx bx-show fs-16"></i></a>
                                                <a href="javascript:void(0);" class="btn btn-soft-info btn-sm"><i class="bx bx-edit fs-16"></i></a>
                                                <a href="javascript:void(0);" class="btn btn-soft-danger btn-sm"><i class="bx bx-trash fs-16"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer border-top">
                        {{ $orders->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- End Container Fluid -->

</div>
@endsection
