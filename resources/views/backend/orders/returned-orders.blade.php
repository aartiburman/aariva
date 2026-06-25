@extends('backend.layouts.app')

@section('content')
<div class="page-content">

    <!-- Start Container Fluid -->
    <div class="container-fluid">

        <div class="row mt-2">
            <div class="col-xl-12">
                <div class="page-title-box">
                    <h4 class="mb-0 fs-18">Returned Orders List</h4>
                </div>
            </div>
        </div>

        <div class="row g-2 flex-nowrap overflow-auto pb-3">
            <!-- Pending Orders -->
            <div class="col">
                <a href="{{ route('pending.orders') }}" class="text-decoration-none">
                    <div class="card mb-0">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-1 fs-13">Pending</h5>
                                    <p class="text-muted fw-bold fs-16 mb-0">{{ $statusCounts->pending ?? 0 }}</p>
                                </div>
                                <div class="avatar-sm bg-soft-warning rounded">
                                    <iconify-icon icon="solar:clock-circle-linear" class="avatar-title fs-20 text-warning"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Confirmed -->
            <div class="col">
                <a href="{{ route('confirmed.orders') }}" class="text-decoration-none">
                    <div class="card mb-0">
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
            <div class="col">
                <a href="{{ route('shipped.orders') }}" class="text-decoration-none">
                    <div class="card mb-0">
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
            <div class="col">
                <a href="{{ route('delivered.orders') }}" class="text-decoration-none">
                    <div class="card mb-0">
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
            <div class="col">
                <a href="{{ route('cancelled.orders') }}" class="text-decoration-none">
                    <div class="card mb-0">
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

            <!-- Returned -->
            <div class="col">
                <a href="{{ route('returned.orders') }}" class="text-decoration-none">
                    <div class="card mb-0">
                        <div class="card-body p-2 border border-warning rounded">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-1 fs-13 text-warning">Returned</h5>
                                    <p class="text-warning fw-bold fs-16 mb-0">{{ $statusCounts->returned ?? 0 }}</p>
                                </div>
                                <div class="avatar-sm bg-soft-warning rounded">
                                    <iconify-icon icon="solar:arrow-return-linear" class="avatar-title fs-20 text-warning"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- In Dispute -->
            <div class="col">
                <a href="{{ route('dispute.orders') }}" class="text-decoration-none">
                    <div class="card mb-0">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title mb-1 fs-13">In Dispute</h5>
                                    <p class="text-muted fw-bold fs-16 mb-0">{{ $statusCounts->dispute ?? 0 }}</p>
                                </div>
                                <div class="avatar-sm bg-soft-danger rounded">
                                    <iconify-icon icon="solar:error-circle-linear" class="avatar-title fs-20 text-danger"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
            <div class="col-xl-12">
                @include('backend.orders.partials._filters', ['title' => 'Returned Orders List', 'use_ajax' => true])
                
                <div class="card">
                    <div class="card-header border-bottom-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Returned Orders List</h4>
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
                                        <th>Pay Mode</th>
                                        <th>Pay Status</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Order Status</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @include('backend.orders.partials.orders-table')
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <p class="mb-0">Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders</p>
                            </div>
                            <div class="col-auto ms-auto">
                                {{ $orders->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- End Container Fluid -->

</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        initAjaxFilter('#filter-form', 'tbody', null, 'orders');
    });
</script>
@endpush
