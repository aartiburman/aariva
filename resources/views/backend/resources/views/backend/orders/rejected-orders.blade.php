@extends('backend.layouts.app')

@section('content')
<div class="page-content">

    <!-- Start Container Fluid -->
    <div class="container-fluid">

        <div class="row">
            <div class="col-xl-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Rejected Orders List</h4>
                </div>
            </div>
        </div>

        <div class="mb-4 row row-cols-xxl-5 row-cols-xl-5 row-cols-lg-3 row-cols-md-2 row-cols-1 g-2">
            <div class="col-md-6 col-xl-3">
                <a href="{{ route('pending.orders') }}" class="text-decoration-none">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-2">Pending</h4>
                                    <p class="text-muted fw-medium fs-22 mb-0">{{ $statusCounts->pending ?? 0 }}</p>
                                </div>
                                <div class="avatar-md bg-soft-primary rounded">
                                    <iconify-icon icon="solar:cart-large-2-linear" class="avatar-title fs-32 text-primary"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
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
                                    <iconify-icon icon="solar:checklist-linear" class="avatar-title fs-32 text-primary"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        

            <div class="col-md-6 col-xl-3">
                <a href="{{ route('cancelled.orders') }}" class="text-decoration-none">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-2">Cancelled</h4>
                                    <p class="text-muted fw-medium fs-22 mb-0">{{ $statusCounts->cancelled ?? 0 }}</p>
                                </div>
                                <div class="avatar-md bg-soft-primary rounded">
                                    <iconify-icon icon="solar:bill-cross-linear" class="avatar-title fs-32 text-primary"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                @include('backend.orders.partials._filters', ['title' => 'Rejected Orders List', 'hide_status_filter' => true])
                
                <div class="card">
                    <div class="card-header border-bottom-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Rejected Orders List</h4>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th style="width: 20px;">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="customCheck1">
                                                <label class="form-check-label" for="customCheck1">&nbsp;</label>
                                            </div>
                                        </th>
                                        <th>Order ID</th>
                                        <th>Vendor</th>
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
                                    @foreach($orders as $item)
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
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="customCheck2">
                                                <label class="form-check-label" for="customCheck2">&nbsp;</label>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('orders.details', $item->order->order_reference_id) }}" class="text-body fw-medium">#{{ $item->order->order_reference_id }}</a>
                                        </td>
                                        <td>{{ $item->vendor->name ?? 'N/A' }}</td>
                                        <td>{{ $item->created_at->format('d M, Y') }}</td>
                                        <td>{{ $item->order->user->name ?? 'Guest' }}</td>
                                        <td>
                                            <span class="badge bg-soft-info text-info">Normal</span>
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
                                        <td>{{ $item->quantity }}</td>
                                        <td>#{{ $item->id }}</td>
                                        <td>
                                            <span class="badge {{ $statusClass }}">{{ $order_status_text }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('orders.details', $item->order->order_reference_id) }}" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:eye-linear" class="align-middle fs-18"></iconify-icon></a>
                                                <!-- <a href="#!" class="btn btn-soft-danger btn-sm"><iconify-icon icon="solar:trash-bin-minimalistic-2-linear" class="align-middle fs-18"></iconify-icon></a> -->
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- end table-responsive -->
                    </div>
                    <div class="card-footer border-top">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>

        </div>

    </div>
    <!-- End Container Fluid -->

</div>
@endsection
