@extends('backend.layouts.app')
@section('content')

<div class="page-content">

     <!-- Start Container Fluid -->
     <div class="container-fluid">
          <!-- Featured Stats Cards -->
          <div class="row g-2 mb-4">
               
               <div class="col-md-4 col-xl-2">
                    
                    <a @if(Auth::user()->isDocumentsVerified()) href="{{ route('new.orders') }}" @else href="javascript:void(0);" style="cursor: not-allowed; opacity: 0.6;" @endif class="text-decoration-none">
                         <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-warning-subtle h-100">
                              <div class="card-body p-3">
                                   <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-sm bg-warning rounded">
                                             <iconify-icon icon="solar:clock-circle-linear" class="avatar-title fs-20 text-white"></iconify-icon>
                                        </div>
                                        <div>
                                             <h5 class="fs-14 mb-1 fw-bold text-warning">Pending</h5>
                                             <p class="mb-0 fs-12 text-muted">{{ $orderStats->pending ?? 0 }} Orders</p>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </a>
               </div>
                <div class="col-md-4 col-xl-2">
                    <a @if(Auth::user()->isDocumentsVerified()) href="{{ route('confirmed.orders') }}" @else href="javascript:void(0);" style="cursor: not-allowed; opacity: 0.6;" @endif class="text-decoration-none">
                         <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-info-subtle h-100">
                              <div class="card-body p-3">
                                   <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-sm bg-info rounded">
                                             <iconify-icon icon="solar:restart-linear" class="avatar-title fs-20 text-white"></iconify-icon>
                                        </div>
                                        <div>
                                             <h5 class="fs-14 mb-1 fw-bold text-info">Confirm</h5>
                                             <p class="mb-0 fs-12 text-muted">{{ $orderStats->confirmed ?? 0 }} Orders</p>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </a>
               </div>
                <div class="col-md-4 col-xl-2">
                    <a @if(Auth::user()->isDocumentsVerified()) href="{{ route('shipped.orders') }}" @else href="javascript:void(0);" style="cursor: not-allowed; opacity: 0.6;" @endif class="text-decoration-none">
                         <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-secondary-subtle h-100">
                              <div class="card-body p-3">
                                   <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-sm bg-secondary rounded">
                                             <iconify-icon icon="solar:transmission-square-linear" class="avatar-title fs-20 text-white"></iconify-icon>
                                        </div>
                                        <div>
                                             <h5 class="fs-14 mb-1 fw-bold text-secondary">Shipped</h5>
                                             <p class="mb-0 fs-12 text-muted">{{ $orderStats->shipped ?? 0 }} Orders</p>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </a>
               </div>
              
                <div class="col-md-4 col-xl-2">
                    <a @if(Auth::user()->isDocumentsVerified()) href="{{ route('delivered.orders') }}" @else href="javascript:void(0);" style="cursor: not-allowed; opacity: 0.6;" @endif class="text-decoration-none">
                         <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-success-subtle h-100">
                              <div class="card-body p-3">
                                   <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-sm bg-success rounded">
                                             <iconify-icon icon="solar:check-read-linear" class="avatar-title fs-20 text-white"></iconify-icon>
                                        </div>
                                        <div>
                                             <h5 class="fs-14 mb-1 fw-bold text-success">Delivered</h5>
                                             <p class="mb-0 fs-12 text-muted">{{ $orderStats->delivered ?? 0 }} Orders</p>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </a>
               </div>
               <div class="col-md-4 col-xl-2">
                    <a @if(Auth::user()->isDocumentsVerified()) href="{{ route('cancelled.orders') }}" @else href="javascript:void(0);" style="cursor: not-allowed; opacity: 0.6;" @endif class="text-decoration-none">
                         <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-danger-subtle h-100">
                              <div class="card-body p-3">
                                   <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-sm bg-danger rounded">
                                             <iconify-icon icon="solar:close-circle-linear" class="avatar-title fs-20 text-white"></iconify-icon>
                                        </div>
                                        <div>
                                             <h5 class="fs-14 mb-1 fw-bold text-danger">Cancelled</h5>
                                             <p class="mb-0 fs-12 text-muted">{{ $orderStats->cancelled ?? 0 }} Orders</p>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </a>
               </div>
               <div class="col-md-4 col-xl-2">
                    <a @if(Auth::user()->isDocumentsVerified()) href="{{ route('returned.orders') }}" @else href="javascript:void(0);" style="cursor: not-allowed; opacity: 0.6;" @endif class="text-decoration-none">
                         <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-dark-subtle h-100">
                              <div class="card-body p-3">
                                   <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-sm bg-dark rounded">
                                             <iconify-icon icon="solar:refresh-circle-linear" class="avatar-title fs-20 text-white"></iconify-icon>
                                        </div>
                                        <div>
                                             <h5 class="fs-14 mb-1 fw-bold text-dark">Returned</h5>
                                             <p class="mb-0 fs-12 text-muted">{{ $orderStats->returned ?? 0 }} Orders</p>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </a>
               </div>

               <div class="col-12">
                    <a @if(Auth::user()->isDocumentsVerified()) href="{{ route('vendor.support.center') }}" @else href="javascript:void(0);" style="cursor: not-allowed; opacity: 0.6;" @endif class="text-decoration-none">
                         <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100" style="background-color: #f3e8ff;">
                              <div class="card-body p-3">
                                   <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                                        <div class="d-flex align-items-center gap-3">
                                             <div class="avatar-sm rounded" style="background-color: #5d1a8f;">
                                                  <iconify-icon icon="solar:ticket-linear" class="avatar-title fs-20 text-white"></iconify-icon>
                                             </div>
                                             <h5 class="fs-14 mb-0 fw-bold" style="color: #5d1a8f;">Tickets Overview</h5>
                                        </div>
                                        
                                        <div class="d-flex gap-4 flex-wrap align-items-center">
                                             <div class="d-flex align-items-center gap-2">
                                                  <span class="fs-12 text-muted fw-semibold">New:</span>
                                                  <span class="fs-14 fw-bold" style="color: #5d1a8f;">{{ $ticketStats['new'] ?? 0 }}</span>
                                             </div>
                                             <div class="vr opacity-25"></div>
                                             <div class="d-flex align-items-center gap-2">
                                                  <span class="fs-12 text-muted fw-semibold">Open:</span>
                                                  <span class="fs-14 fw-bold" style="color: #5d1a8f;">{{ $ticketStats['open'] ?? 0 }}</span>
                                             </div>
                                             <div class="vr opacity-25"></div>
                                             <div class="d-flex align-items-center gap-2">
                                                  <span class="fs-12 text-muted fw-semibold">In Process:</span>
                                                  <span class="fs-14 fw-bold" style="color: #5d1a8f;">{{ $ticketStats['process'] ?? 0 }}</span>
                                             </div>
                                             <div class="vr opacity-25"></div>
                                             <div class="d-flex align-items-center gap-2">
                                                  <span class="fs-12 text-muted fw-semibold">Closed:</span>
                                                  <span class="fs-14 fw-bold" style="color: #5d1a8f;">{{ $ticketStats['closed'] ?? 0 }}</span>
                                             </div>
                                             <div class="vr opacity-25"></div>
                                             <div class="d-flex align-items-center gap-2">
                                                  <span class="fs-12 text-muted fw-semibold">Transfer:</span>
                                                  <span class="fs-14 fw-bold" style="color: #5d1a8f;">{{ $ticketStats['transfer'] ?? 0 }}</span>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </a>
               </div>
          </div>

          <div class="row">
               <div class="col-xxl-12">
                    <div class="row">
                         <div class="col-md-3">
                              <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                   <div class="card-body">
                                        <div class="row">
                                             <div class="col-6">
                                                  <div class="avatar-md bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center">
                                                       <iconify-icon icon="solar:cart-5-linear" class="fs-32 text-primary"></iconify-icon>
                                                  </div>
                                             </div> <!-- end col -->
                                             <div class="col-6 text-end">
                                                  <p class="text-muted mb-0 text-truncate fs-13">Total Orders</p>
                                                  <h3 class="mt-1 mb-0 fw-bold">{{ number_format($totalOrders) }}</h3>
                                             </div> <!-- end col -->
                                        </div> <!-- end row-->
                                   </div> <!-- end card body -->
                                   <div class="card-footer py-2 bg-light-subtle border-0">
                                        <div class="d-flex align-items-center justify-content-between">
                                             <div>
                                                  <span class="{{ $orderChange >= 0 ? 'text-success' : 'text-danger' }} fs-12 fw-bold">
                                                       <iconify-icon icon="solar:alt-arrow-{{ $orderChange >= 0 ? 'up' : 'down' }}-linear" class="align-middle"></iconify-icon>
                                                       {{ number_format(abs($orderChange), 1) }}%
                                                  </span>
                                                  <span class="text-muted ms-1 fs-12">Last Month</span>
                                             </div>
                                             <a @if(Auth::user()->isDocumentsVerified()) href="{{ route('new.orders') }}" @else href="javascript:void(0);" style="cursor: not-allowed; opacity: 0.6;" @endif class="text-primary fw-semibold fs-12">View More</a>
                                        </div>
                                   </div> <!-- end card footer -->
                              </div> <!-- end card -->
                         </div> <!-- end col -->
                         <div class="col-md-3">
                              <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                   <div class="card-body">
                                        <div class="row">
                                             <div class="col-6">
                                                  <div class="avatar-md bg-info-subtle rounded-3 d-flex align-items-center justify-content-center">
                                                       <iconify-icon icon="solar:users-group-two-rounded-linear" class="fs-32 text-info"></iconify-icon>
                                                  </div>
                                             </div> <!-- end col -->
                                             <div class="col-6 text-end">
                                                  <p class="text-muted mb-0 text-truncate fs-13">Total Customers</p>
                                                  <h3 class="mt-1 mb-0 fw-bold">{{ number_format($totalCustomers) }}</h3>
                                             </div> <!-- end col -->
                                        </div> <!-- end row-->
                                   </div> <!-- end card body -->
                                   <div class="card-footer py-2 bg-light-subtle border-0">
                                        <div class="d-flex align-items-center justify-content-between">
                                             <div>
                                                  <span class="text-success fs-12 fw-bold"> <iconify-icon icon="solar:add-circle-linear" class="align-middle"></iconify-icon> New</span>
                                                  <span class="text-muted ms-1 fs-12">Registered</span>
                                             </div>
                                             <a @if(Auth::user()->isDocumentsVerified()) href="{{ route('my.customer.list') }}" @else href="javascript:void(0);" style="cursor: not-allowed; opacity: 0.6;" @endif class="text-info fw-semibold fs-12">View More</a>
                                        </div>
                                   </div> <!-- end card footer -->
                              </div> <!-- end card -->
                         </div> <!-- end col -->
                         <div class="col-md-3">
                              <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                   <div class="card-body">
                                        <div class="row">
                                             <div class="col-6">
                                                  <div class="avatar-md bg-warning-subtle rounded-3 d-flex align-items-center justify-content-center">
                                                       <iconify-icon icon="solar:box-minimalistic-linear" class="fs-32 text-warning"></iconify-icon>
                                                  </div>
                                             </div> <!-- end col -->
                                             <div class="col-6 text-end">
                                                  <p class="text-muted mb-0 text-truncate fs-13">Products</p>
                                                  <h3 class="mt-1 mb-0 fw-bold">{{ number_format($totalProducts) }}</h3>
                                             </div> <!-- end col -->
                                        </div> <!-- end row-->
                                   </div> <!-- end card body -->
                                   <div class="card-footer py-2 bg-light-subtle border-0">
                                        <div class="d-flex align-items-center justify-content-between">
                                             <div>
                                                  <span class="text-muted ms-1 fs-12">Total Active Products</span>
                                             </div>
                                             <a @if(Auth::user()->isDocumentsVerified()) href="{{ route('product.list') }}" @else href="javascript:void(0);" style="cursor: not-allowed; opacity: 0.6;" @endif class="text-warning fw-semibold fs-12">View More</a>
                                        </div>
                                   </div> <!-- end card footer -->
                              </div> <!-- end card -->
                         </div> <!-- end col -->
                         <div class="col-md-3">
                              <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                   <div class="card-body">
                                        <div class="row">
                                             <div class="col-6">
                                                  <div class="avatar-md bg-purple-subtle rounded-3 d-flex align-items-center justify-content-center" style="background-color: #f3e8ff !important;">
                                                       <iconify-icon icon="solar:wad-of-money-linear" class="fs-32 text-purple" style="color: #5d1a8f !important;"></iconify-icon>
                                                  </div>
                                             </div> <!-- end col -->
                                             <div class="col-6 text-end">
                                                  <p class="text-muted mb-0 text-truncate fs-13">Total Revenue</p>
                                                  <h3 class="mt-1 mb-0 fw-bold" title="{{ $currency }} {{ number_format($totalRevenue, 2) }}">{{ $currency }} {{ \App\Helpers\PriceHelper::formatLargeNumber($totalRevenue) }}</h3>
                                             </div> <!-- end col -->
                                        </div> <!-- end row-->
                                   </div> <!-- end card body -->
                                   <div class="card-footer py-2 bg-light-subtle border-0">
                                        <div class="d-flex align-items-center justify-content-between">
                                             <div>
                                                  <span class="{{ $revenueChange >= 0 ? 'text-success' : 'text-danger' }} fs-12 fw-bold">
                                                       <iconify-icon icon="solar:alt-arrow-{{ $revenueChange >= 0 ? 'up' : 'down' }}-linear" class="align-middle"></iconify-icon>
                                                       {{ number_format(abs($revenueChange), 1) }}%
                                                  </span>
                                                  <span class="text-muted ms-1 fs-12">Last Month</span>
                                             </div>
                                             <a @if(Auth::user()->isDocumentsVerified()) href="{{ route('vendor.sales.report') }}" @else href="javascript:void(0);" style="cursor: not-allowed; opacity: 0.6;" @endif class="text-purple fw-semibold fs-12" style="color: #5d1a8f !important;">View More</a>
                                        </div>
                                   </div> <!-- end card footer -->
                              </div> <!-- end card -->
                         </div> <!-- end col -->
                    </div> <!-- end row -->
               </div> <!-- end col -->

               <div class="col-xxl-6">
                    <div class="card border-0 shadow-sm rounded-4 height-500px">
                         <div class="card-body">
                              <div class="d-flex justify-content-between align-items-center mb-4">
                                   <h4 class="card-title fw-bold">Performance</h4>
                                   <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-light text-dark border">ALL</button>
                                        <button type="button" class="btn btn-outline-light text-dark border">1M</button>
                                        <button type="button" class="btn btn-outline-light text-dark border">6M</button>
                                        <button type="button" class="btn btn-purple" style="background-color: #5d1a8f; color: white;">1Y</button>
                                   </div>
                              </div> <!-- end card-title-->

                              <div dir="ltr">
                                   <div id="dash-performance-chart" class="apex-charts" data-series='@json($chartRevenue)' data-months='@json($chartMonths)' data-currency="{{ $currency }}"></div>
                              </div>
                         </div> <!-- end card body -->
                    </div> <!-- end card -->
               </div> <!-- end col -->

               <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4">
                         <div class="card-body">
                              <h5 class="card-title fw-bold mb-4">Revenue Growth</h5>
                              <div id="conversions" class="apex-charts mb-2 mt-n2" data-series="{{ round($revenueGrowth, 1) }}"></div>
                              <div class="row text-center">
                                   <div class="col-6">
                                        <p class="text-muted mb-1 small text-uppercase fw-bold">Total Customers</p>
                                        <h3 class="mb-0 fw-bold">{{ number_format($totalVendorCustomersCount) }}</h3>
                                   </div> <!-- end col -->
                                   <div class="col-6">
                                        <p class="text-muted mb-1 small text-uppercase fw-bold">Total Products</p>
                                        <h3 class="mb-0 fw-bold">{{ number_format($totalVendorProductsCount) }}</h3>
                                   </div> <!-- end col -->
                              </div> <!-- end row -->
                            
                              <div class="text-center mt-4">
                                   <a @if(Auth::user()->isDocumentsVerified()) href="{{ route('product.list') }}" @else href="javascript:void(0);" style="cursor: not-allowed; opacity: 0.6;" @endif class="btn btn-soft-purple w-100 rounded-3" style="background-color: #f3e8ff; color: #5d1a8f;">View Products</a>
                              </div> <!-- end row -->
                         </div>
                    </div>
               </div>
          </div> <!-- end row -->

         <div class="row">

    <!-- ================= Orders Location ================= -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="card-title fw-bold">Orders Location</h4>
                </div>

                <!-- World Map -->
                <div id="world-map-markers"
                     class="height-330 mt-3"
                     data-countries='@json($sessionsByCountry)'>
                </div>

                <!-- States Progress -->
                <div class="pt-3">
                    @foreach($sessionsByState as $item)
                        <div class="d-flex align-items-center justify-content-between {{ !$loop->last ? 'mb-3' : '' }}">
                            
                            <p class="mb-0 fw-semibold fs-13">
                                {{ $item['state'] }}
                            </p>

                            <div class="d-flex align-items-center gap-2">

                                <div class="progress"
                                     style="width: 100px; height: 6px; border-radius: 10px;">
                                    <div class="progress-bar"
                                         role="progressbar"
                                         style="width: <?= number_format($item['percentage'], 2) ?>%; background-color: #5d1a8f;"
                                         aria-valuenow="{{ $item['percentage'] }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>

                                <span class="text-muted fs-12 fw-medium">
                                    {{ $item['percentage'] }}%
                                </span>

                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>


    <!-- ================= Recent Orders ================= -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

            <!-- Card Header -->
            <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
                <h5 class="mb-0 fw-bold">Recent Orders</h5>

                <div class="d-flex gap-2">
                    <select id="bulk-status-select"
                            class="form-select form-select-sm"
                            style="width: auto; display: none;">
                        <option value="">Bulk Action</option>
                        <option value="0">Pending</option>
                        <option value="1">Confirmed</option>
                        <option value="2">Shipped</option>
                        <option value="3">Delivered</option>
                        <option value="4">Cancelled</option>
                        <option value="5">Returned</option>
                    </select>

                    <button id="apply-bulk-status"
                            class="btn btn-sm btn-primary"
                            style="display: none;">
                        Apply
                    </button>

                    <a @if(Auth::user()->status == 1) href="{{ route('new.orders') }}" @endif
                       class="btn btn-sm btn-soft-primary">
                        View All
                    </a>
                </div>
            </div>

            <!-- Card Body -->
            <div class="card-body p-0">
                <div class="table-responsive">

                    <table class="table table-hover table-nowrap align-middle mb-0"
                           id="recent-orders-table">

                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-3" style="width: 20px;">
                                    <div class="form-check">
                                        <input type="checkbox"
                                               class="form-check-input"
                                               id="select-all-orders">
                                    </div>
                                </th>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($recentOrders as $item)
                                <tr data-id="{{ $item->id }}">

                                    <!-- Checkbox -->
                                    <td class="ps-3">
                                        <div class="form-check">
                                            <input type="checkbox"
                                                   class="form-check-input order-checkbox"
                                                   value="{{ $item->id }}">
                                        </div>
                                    </td>

                                    <!-- Order ID -->
                                    <td>
                                        <a href="{{ route('orders.details', $item->order->order_reference_id ?? $item->order_id) }}"
                                           class="fw-bold text-primary">
                                            #{{ $item->order->order_reference_id ?? $item->order_id }}
                                        </a>
                                    </td>

                                    <!-- Customer -->
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($item->order && $item->order->user)
                                                <div class="avatar-xs">
                                                    <span class="avatar-title rounded-circle bg-primary-subtle text-primary fw-bold">
                                                        {{ strtoupper(substr($item->order->user->name ?? 'G', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <span class="fw-medium">
                                                    {{ $item->order->user->name ?? 'Guest' }}
                                                </span>
                                            @else
                                                <span class="text-muted">Guest</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Product -->
                                    <td>
                                        <span class="text-truncate d-inline-block"
                                              style="max-width: 150px;">
                                            {{ $item->product->name ?? 'Product Deleted' }}
                                        </span>
                                    </td>

                                    <!-- Amount -->
                                    <td class="fw-bold text-dark">
                                        {{ $currency }} {{ number_format($item->total_actual_price, 2) }}
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        @php
                                            $statusClass = match($item->status) {
                                                0 => 'bg-warning-subtle text-warning',
                                                1 => 'bg-info-subtle text-info',
                                                2 => 'bg-secondary-subtle text-secondary',
                                                3 => 'bg-success-subtle text-success',
                                                4 => 'bg-danger-subtle text-danger',
                                                5 => 'bg-dark-subtle text-dark',
                                                default => 'bg-light text-muted'
                                            };

                                            $statusLabel = match($item->status) {
                                                0 => 'Pending',
                                                1 => 'Confirmed',
                                                2 => 'Shipped',
                                                3 => 'Delivered',
                                                4 => 'Cancelled',
                                                5 => 'Returned',
                                                default => 'Unknown'
                                            };
                                        @endphp

                                        <span class="badge {{ $statusClass }} rounded-pill px-3">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    <!-- Date -->
                                    <td>
                                        {{ $item->created_at->format('d M, Y') }}
                                    </td>

                                    <!-- Action -->
                                    <td class="text-end pe-4">
                                        <a href="{{ route('orders.details', $item->order->order_reference_id ?? $item->order_id) }}"
                                           style="color: #5d1a8f;">
                                            <iconify-icon icon="solar:eye-linear"
                                                          class="fs-20"></iconify-icon>
                                        </a>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>

        </div>
    </div>

</div>

     </div>
</div>

@push('chart-scripts')
<!-- Vector Map Js -->
<script src="{{ asset('backend/assets/vendor/jsvectormap/js/jsvectormap.min.js') }}"></script>
<script src="{{ asset('backend/assets/vendor/jsvectormap/maps/world-merc.js') }}"></script>
<script src="{{ asset('backend/assets/vendor/jsvectormap/maps/world.js') }}"></script>

<!-- Dashboard Js -->
<script src="{{ asset('backend/assets/js/pages/vendor-dashboard.js') }}"></script>
@endpush

@endsection

