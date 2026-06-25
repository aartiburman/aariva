@extends('backend.layouts.app')

@section('content')
<div class="page-content">

    <!-- Start Container Fluid -->
    <div class="container-fluid">

        <div class="row">
            <div class="col-xl-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Pending Orders List</h4>
                </div>
            </div>
        </div>

        <div class="row g-2">
            <!-- All Orders -->
            <div class="col-md-4 col-xl-2">
                <a href="{{ route('new.orders') }}" class="text-decoration-none">
                    <div class="card">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-1 fs-13">All Orders</h5>
                                    <p class="text-muted fw-bold fs-16 mb-0">{{ $statusCounts->total ?? 0 }}</p>
                                </div>
                                <div class="avatar-sm bg-soft-primary rounded">
                                    <iconify-icon icon="solar:clipboard-list-linear" class="avatar-title fs-20 text-primary"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Confirmed -->
            <div class="col-md-4 col-xl-2">
                <a href="{{ route('confirmed.orders') }}" class="text-decoration-none">
                    <div class="card ">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-1 fs-13">Confirmed</h5>
                                    <p class="text-muted fw-bold fs-16 mb-0">{{ $statusCounts->confirmed ?? 0 }}</p>
                                </div>
                                <div class="avatar-sm bg-soft-info rounded">
                                    <iconify-icon icon="solar:check-circle-linear" class="avatar-title fs-20 text-info"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>


            <!-- Shipped -->
            <div class="col-md-4 col-xl-2">
                <a href="{{ route('shipped.orders') }}" class="text-decoration-none">
                    <div class="card ">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-1 fs-13">Shipped</h5>
                                    <p class="text-muted fw-bold fs-16 mb-0">{{ $statusCounts->shipped ?? 0 }}</p>
                                </div>
                                <div class="avatar-sm bg-soft-primary rounded">
                                    <iconify-icon icon="solar:box-linear" class="avatar-title fs-20 text-primary"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Delivered -->
            <div class="col-md-4 col-xl-2">
                <a href="{{ route('delivered.orders') }}" class="text-decoration-none">
                    <div class="card ">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-1 fs-13">Delivered</h5>
                                    <p class="text-muted fw-bold fs-16 mb-0">{{ $statusCounts->delivered ?? 0 }}</p>
                                </div>
                                <div class="avatar-sm bg-soft-success rounded">
                                    <iconify-icon icon="solar:check-circle-linear" class="avatar-title fs-20 text-success"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Cancelled -->
            <div class="col-md-4 col-xl-2">
                <a href="{{ route('cancelled.orders') }}" class="text-decoration-none">
                    <div class="card ">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-1 fs-13">Cancelled</h5>
                                    <p class="text-muted fw-bold fs-16 mb-0">{{ $statusCounts->cancelled ?? 0 }}</p>
                                </div>
                                <div class="avatar-sm bg-soft-danger rounded">
                                    <iconify-icon icon="solar:close-circle-linear" class="avatar-title fs-20 text-danger"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4 col-xl-2">
                <a href="{{ route('returned.orders') }}" class="text-decoration-none">
                    <div class="card ">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-1 fs-13">Returned</h5>
                                    <p class="text-muted fw-bold fs-16 mb-0">{{ $statusCounts->returned ?? 0 }}</p>
                                </div>
                                <div class="avatar-sm bg-soft-danger rounded">
                                    <iconify-icon icon="solar:close-circle-linear" class="avatar-title fs-20 text-danger"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                @include('backend.orders.partials._filters', ['title' => 'Pending Orders List', 'hide_status_filter' => true])
                
                <div class="card">
                    <div class="card-header border-bottom-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Pending Orders List</h4>
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
                                        <th>Payment Status</th>
                                        <th>Items</th>
                                        <th>Order Status</th>
                                        <th>Created At</th>

                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $key=> $item)

                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td> <a href="javascript:void(0);" class="fw-medium">#{{ $item->order->order_reference_id ?? 'N/A' }}</a>
                                            <br>
                                            <span>Vendor: {{ $item->vendor->name ?? 'N/A' }}</span><br>
                                            <span>Store Name: {{ $item->vendor->store_name ?? 'N/A' }}</span>
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
                                        <td>
                                            <!-- Badge -->
                                            <span
                                                class="badge payment-status-badge {{ ($item->payment_status ?? 0) == 1 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }}"
                                                data-order-id="{{ $item->id ?? '' }}"
                                                style="cursor:pointer">
                                                {{ ($item->payment_status ?? 0) == 1 ? 'Paid' : 'Unpaid' }}
                                            </span>
                                            <!-- Select -->
                                            <select
                                                class="form-select form-select-sm payment-status-select d-none"
                                                data-order-id="{{ $item->id ?? '' }}">
                                                <option value="0" {{ ($item->payment_status ?? 0) == 0 ? 'selected' : '' }}>Unpaid</option>
                                                <option value="1" {{ ($item->payment_status ?? 0) == 1 ? 'selected' : '' }}>Paid</option>
                                            </select>
                                        </td>

                                        <td>{{ $item->product->name ?? 'Product' }} (Qty: {{ $item->quantity }})</td>
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

                                            <!-- Badge -->
                                            <span
                                                class="badge {{ $statusClass }} order-status-badge"
                                                data-order-id="{{ $item->id ?? '' }}"
                                                style="cursor:pointer">
                                                {{ ucfirst($order_status_text) }}
                                            </span>

                                            <!-- Select (hidden) -->
                                            <select
                                                class="form-select form-select-sm order-status-select d-none"
                                                data-order-id="{{ $item->id ?? '' }}">
                                                <option value="0" {{ $order_status_val == '0' ? 'selected' : '' }}>Pending</option>
                                                <option value="1" {{ $order_status_val == '1' ? 'selected' : '' }}>Confirmed</option>
                                                <option value="2" {{ $order_status_val == '2' ? 'selected' : '' }}>Shipped</option>
                                                <option value="3" {{ $order_status_val == '3' ? 'selected' : '' }}>Delivered</option>
                                                <option value="4" {{ $order_status_val == '4' ? 'selected' : '' }}>Cancelled</option>
                                                <option value="5" {{ $order_status_val == '5' ? 'selected' : '' }}>Returned</option>
                                            </select>
                                        </td>
                                        <td>{{ $item->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('orders.details', $item->order->order_reference_id ?? '') }}" class="btn btn-soft-primary btn-sm"><i class="bx bx-show fs-16"></i></a>
                                                <!-- <a href="javascript:void(0);" class="btn btn-soft-info btn-sm"><i class="bx bx-edit fs-16"></i></a>
                                                <a href="javascript:void(0);" class="btn btn-soft-danger btn-sm"><i class="bx bx-trash fs-16"></i></a> -->
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
