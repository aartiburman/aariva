@extends('backend.layouts.app')
@section('content')

<div class="page-content">
     <!-- Start Container Fluid -->
     <div class="container-fluid">
          <div class="row mb-4 align-items-center">
               <div class="col-md-6">
                    <h4 class="fs-18 fw-bold mb-0">Dashboard Overview</h4>
               </div>
          </div>

          <div class="row">
               <!-- Total Vendors -->
               <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                         <div class="card-body">
                              <div class="d-flex align-items-center justify-content-between mb-3">
                                   <div>
                                        <p class="text-muted text-uppercase fs-12 fw-bold mb-1">{{ __('messages.total_vendors') }}</p>
                                        <h2 class="mb-0 fw-bold">{{ number_format($vendorStats->total) }}</h2>
                                   </div>
                                   <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                        <iconify-icon icon="solar:shop-linear" class="fs-32 text-primary"></iconify-icon>
                                   </div>
                              </div>
                              <div class="d-flex align-items-center gap-2 mt-3">
                                   <span class="badge {{ $vendorStats->growth_class }} fs-12">{{ $vendorStats->growth_prefix }}{{ $vendorStats->growth }}%</span>
                                   <span class="text-muted fs-12">{{ __('messages.last_month') }}</span>
                                   <a href="{{ route('vendors.list') }}" class="text-primary fs-12 fw-semibold ms-auto">{{ __('messages.view_more') }}</a>
                              </div>
                         </div>
                    </div>
               </div>

               <!-- Active Vendors -->
               <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                         <div class="card-body">
                              <div class="d-flex align-items-center justify-content-between mb-3">
                                   <div>
                                        <p class="text-muted text-uppercase fs-12 fw-bold mb-1">{{ __('messages.active_vendors') }}</p>
                                        <h2 class="mb-0 fw-bold">{{ number_format($vendorStats->active) }}</h2>
                                   </div>
                                   <div class="avatar-lg bg-success-subtle rounded-circle d-flex align-items-center justify-content-center">
                                        <iconify-icon icon="solar:check-read-linear" class="fs-32 text-success"></iconify-icon>
                                   </div>
                              </div>
                              <div class="d-flex align-items-center gap-2 mt-3">
                                   <span class="text-muted fs-12">{{ __('messages.currently_selling') }}</span>
                                   <a href="{{ route('vendors.list', ['status' => 1]) }}" class="text-primary fs-12 fw-semibold ms-auto">{{ __('messages.view_more') }}</a>
                              </div>
                         </div>
                    </div>
               </div>

               <!-- Pending Approval -->
               <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                         <div class="card-body">
                              <div class="d-flex align-items-center justify-content-between mb-3">
                                   <div>
                                        <p class="text-muted text-uppercase fs-12 fw-bold mb-1">{{ __('messages.pending_approval') }}</p>
                                        <h2 class="mb-0 fw-bold">{{ number_format($vendorStats->pending) }}</h2>
                                   </div>
                                   <div class="avatar-lg bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center">
                                        <iconify-icon icon="solar:hourglass-linear" class="fs-32 text-warning"></iconify-icon>
                                   </div>
                              </div>
                              <div class="d-flex align-items-center gap-2 mt-3">
                                   <span class="text-danger fs-12 fw-medium">{{ __('messages.requires_action') }}</span>
                                   <a href="{{ route('vendors.list', ['status' => 0]) }}" class="text-primary fs-12 fw-semibold ms-auto">{{ __('messages.view_more') }}</a>
                              </div>
                         </div>
                    </div>
               </div>

               <!-- Blocked / Rejected -->
               <div class="col-sm-6 col-lg-3 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                         <div class="card-body">
                              <div class="d-flex align-items-center justify-content-between mb-3">
                                   <div>
                                        <p class="text-muted text-uppercase fs-12 fw-bold mb-1">{{ __('messages.blocked_rejected') }}</p>
                                        <h2 class="mb-0 fw-bold">{{ number_format($vendorStats->blocked) }}</h2>
                                   </div>
                                   <div class="avatar-lg bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center">
                                        <iconify-icon icon="solar:forbidden-circle-linear" class="fs-32 text-danger"></iconify-icon>
                                   </div>
                              </div>
                              <div class="d-flex align-items-center gap-2 mt-3">
                                   <span class="text-danger fs-12 fw-medium">{{ __('messages.policy_violations') }}</span>
                                   <a href="{{ route('vendors.list', ['status' => 2]) }}" class="text-primary fs-12 fw-semibold ms-auto">{{ __('messages.view_more') }}</a>
                              </div>
                         </div>
                    </div>
               </div>
          </div>

          <div class="row">
               <div class="col-lg-6">
                    <div class="card " >
                         <div class="card-body">
                              <h5 class="card-title">Conversions</h5>
                              <div id="conversions" class="apex-charts mb-2 mt-n2" data-series="{{ round($revenueGrowth, 1) }}"></div>
                              <div class="row text-center">
                                   <div class="col-6">
                                        <p class="text-muted mb-2">This Week</p>
                                        <h3 class="mb-3">{{ number_format($thisWeekRevenue / 1000, 1) }}k</h3>
                                   </div> <!-- end col -->
                                   <div class="col-6">
                                        <p class="text-muted mb-2">Last Week</p>
                                        <h3 class="mb-3">{{ number_format($lastWeekRevenue / 1000, 1) }}k</h3>
                                   </div> <!-- end col -->
                              </div> <!-- end row -->
                              <div class="text-center">
                                   <a href="{{ route('sales.report') }}" class="btn btn-outline-secondary w-100">View Details</a>
                              </div> <!-- end row -->
                         </div>
                    </div>
               </div> <!-- end left chart card -->

               <div class="col-lg-6">
                    <div class="card ">
                         <div class="card-body">
                              <h5 class="card-title">Revenue by Country</h5>
                              <div id="world-map-markers" class="height-330">
                              </div>
                              <div class="row mt-3">
                                   @foreach($sessionsByCountry as $session)
                                   <div class="col-12 mb-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                             <p class="mb-0">{{ $session['country'] }} ({{ $session['orders'] }} Orders)</p>
                                             <p class="mb-0 fw-bold">{{ $session['currency'] }} {{ $session['revenue'] }}</p>
                                        </div>
                                        <div class="progress progress-sm mt-1">
                                             <div class="progress-bar bg-primary" role="progressbar" style="width: <?=  $session['percentage']  ?>%" aria-valuenow="{{ $session['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                   </div>
                                   @endforeach
                              </div> <!-- end row -->
                         </div>
                    </div> <!-- end card-->
               </div> <!-- end col -->

               <div class="col-lg-12">
                    <div class="card">
                         <div class="card-body">
                               <h5 class="card-title">Performance by Country (India)</h5>
                               <div id="dash-performance-chart" class="apex-charts" data-series='@json($indiaChartData)' data-currency="INR"></div>
                         </div>
                    </div>
               </div>

               <div class="col-lg-12">
                    <div class="card">
                         <div class="card-header d-flex align-items-center justify-content-between gap-2">
                              <h4 class="card-title flex-grow-1">Recent Orders</h4>
                              <div class="d-flex gap-2">
                                   <select id="bulk-status-select" class="form-select form-select-sm" style="width: auto; display: none;">
                                        <option value="">Bulk Action</option>
                                        <option value="0">Pending</option>
                                        <option value="1">Confirmed</option>
                                        <option value="2">Shipped</option>
                                        <option value="3">Delivered</option>
                                        <option value="4">Cancelled</option>
                                        <option value="5">Returned</option>
                                        <option value="6">In Dispute</option>
                                   </select>
                                   <button id="apply-bulk-status" class="btn btn-sm btn-primary" style="display: none;">Apply</button>
                                   <a href="{{ route('new.orders') }}" class="btn btn-sm btn-soft-primary">View All</a>
                              </div>
                         </div>
                         <div class="table-responsive">
                              <table class="table table-hover table-centered align-middle mb-0" id="recent-orders-table">
                                   <thead class="bg-light-subtle">
                                        <tr>
                                             <th class="ps-3" style="width: 20px;">
                                               Sno
                                             </th>
                                             <th>Order ID</th>
                                             <th>Customer</th>
                                             <th>Vendor / Store</th>
                                             <th>Product</th>
                                             <th>Amount</th>
                                             <th>Status</th>
                                             <th>Date</th>
                                             <th>Action</th>
                                        </tr>
                                   </thead>
                                   <tbody>
                                        @foreach($recentOrders as  $key => $item)
                                        <tr data-id="{{ $item->id }}">
                                             <td class="ps-3">
                                                  {{ $key + 1 }}
                                             </td>
                                             <td>
                                                  <a href="{{ route('orders.details', $item->order->order_reference_id ?? '') }}" class="fw-medium text-primary">#{{ $item->order->order_reference_id ?? $item->order_id }}</a>
                                             </td>
                                             <td>
                                                  <div class="d-flex align-items-center">
                                                       @if($item->order && $item->order->user)
                                                       <div class="avatar-xs me-2">
                                                            <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                                 {{ substr($item->order->user->name, 0, 1) }}
                                                            </div>
                                                       </div>
                                                       <div>
                                                            <h5 class="fs-13 mb-0">{{ $item->order->user->name }}</h5>
                                                            <p class="text-muted mb-0 fs-12">{{ $item->order->user->email }}</p>
                                                       </div>
                                                       @else
                                                       <div class="avatar-xs me-2">
                                                            <div class="avatar-title rounded-circle bg-secondary-subtle text-secondary">
                                                                 G
                                                            </div>
                                                       </div>
                                                       <div>
                                                            <h5 class="fs-13 mb-0">Guest Customer</h5>
                                                            <p class="text-muted mb-0 fs-12">N/A</p>
                                                       </div>
                                                       @endif
                                                  </div>
                                             </td>
                                             <td>
                                                  @if($item->vendor)
                                                  <div>
                                                       <h5 class="fs-13 mb-0">{{ $item->vendor->name }}</h5>
                                                       <p class="text-muted mb-0 fs-12">{{ $item->vendor->store_name ?? 'No Store' }}</p>
                                                  </div>
                                                  @else
                                                  <span class="text-muted">N/A</span>
                                                  @endif
                                             </td>
                                             <td>
                                                 <div class="d-flex align-items-center">
                                                     @if($item->product)
                                                     <div class="avatar-xs me-2">
                                                         <img src="{{ asset($item->product->thumb_image) }}" alt="" class="img-fluid rounded-circle">
                                                     </div>
                                                     <div>
                                                         <h5 class="fs-13 mb-0 text-truncate" style="max-width: 150px;">{{ $item->product->name }}</h5>
                                                         <p class="text-muted mb-0 fs-12">
                                                             Qty: {{ $item->quantity }}
                                                             @if($item->variant)
                                                                 | {{ $item->variant->color }} {{ $item->variant->size }}
                                                             @endif
                                                         </p>
                                                     </div>
                                                     @else
                                                     <p class="mb-0">Product Deleted</p>
                                                     @endif
                                                 </div>
                                             </td>
                                             <td>
                                                 @php
                                                                 $currency = $item->currency ?? '$';
                                                                 if ($item->vendor && $item->vendor->country && $item->vendor->country->currency_code) {
                                                                    $currency = $item->vendor->country->currency_code;
                                                                 }
                                                            @endphp
                                                 {{ $currency }} {{ number_format($item->total_actual_price, 2) }}
                                             </td>
                                             <td>
                                                  @php
                                                  $status_labels = [
                                                  0 => ['label' => 'Pending', 'class' => 'bg-soft-warning text-warning'],
                                                  1 => ['label' => 'Confirmed', 'class' => 'bg-soft-info text-info'],
                                                  2 => ['label' => 'Shipped', 'class' => 'bg-soft-primary text-primary'],
                                                  3 => ['label' => 'Delivered', 'class' => 'bg-soft-success text-success'],
                                                  4 => ['label' => 'Cancelled', 'class' => 'bg-soft-secondary text-secondary'],
                                                  5 => ['label' => 'Returned', 'class' => 'bg-soft-warning text-warning'],
                                                  6 => ['label' => 'In Dispute', 'class' => 'bg-soft-danger text-danger'],
                                                  ];
                                                  $currentStatus = $item->status ?? 0;
                                                  $status = $status_labels[$currentStatus] ?? ['label' => 'Unknown', 'class' => 'bg-soft-secondary text-secondary'];
                                                  @endphp
                                                  <div class="status-container">
                                                       <span class="badge {{ $status['class'] }} order-status-badge cursor-pointer" data-order-id="{{ $item->id }}">{{ $status['label'] }}</span>
                                                       <select class="form-select form-select-sm order-status-select d-none" data-order-id="{{ $item->id }}">
                                                            @if($currentStatus == 6)
                                                                 <!-- Only show Returned option for disputed orders -->
                                                                 <option value="5" {{ $currentStatus == 5 ? 'selected' : '' }}>Returned</option>
                                                            @else
                                                                 @foreach($status_labels as $val => $data)
                                                                 <option value="{{ $val }}" {{ $currentStatus == $val ? 'selected' : '' }}>{{ $data['label'] }}</option>
                                                                 @endforeach
                                                            @endif
                                                       </select>
                                                  </div>
                                             </td>
                                             <td>{{ $item->created_at->format('d M, Y') }}</td>
                                             <td>
                                                  <div class="d-flex gap-2">
                                                       <a href="{{ route('orders.details', $item->order->order_reference_id ?? '') }}" class="text-purple hover-opacity-100" data-bs-toggle="tooltip" title="View Detail">
                                                            <iconify-icon icon="solar:eye-linear" class="fs-20"></iconify-icon>
                                                       </a>
                                                  </div>
                                             </td>
                                        </tr>
                                        @endforeach
                                   </tbody>
                              </table>
                         </div>
                         <div class="card-footer border-top">
                              {{ $recentOrders->links() }}
                         </div>
                    </div>
               </div> <!-- end col -->
          </div> <!-- end row -->
     </div>
     <!-- End Container Fluid -->

     @push('chart-scripts')
     <!-- Vector Map Css -->
     <link href="{{ asset('backend/assets/css/plugins/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
     
     <!-- Vector Map Js -->
     <script src="{{ asset('backend/assets/vendor/jsvectormap/js/jsvectormap.min.js') }}"></script>
     <script src="{{ asset('backend/assets/vendor/jsvectormap/maps/world-merc.js') }}"></script>
     <script src="{{ asset('backend/assets/vendor/jsvectormap/maps/world.js') }}"></script>
     
     <!-- Dashboard Js -->
     <script src="{{ asset('backend/assets/js/pages/dashboard.js') }}"></script>
     @endpush


@endsection


