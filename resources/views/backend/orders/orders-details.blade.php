@extends('backend.layouts.app')

@section('content')
@php
    $currency = 'NPR';
    if ($order->items->first() && $order->items->first()->vendor && $order->items->first()->vendor->country) {
        $currency = $order->items->first()->vendor->country->currency ?? 'NPR';
    }
@endphp
<div class="page-content">

    <!-- Start Container Fluid -->
    <div class="container-fluid">

        <div class="row">
            <div class="col-xl-9">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="card-title text-body">Order #{{ $order->order_reference_id }}</h4>
                            <div class="d-flex gap-2">
                                <a href="{{ route('new.orders') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bx bx-arrow-back fs-16"></i> Back to Order List
                                </a>
                                <a href="{{ route('orders.invoice', $order->order_reference_id) }}" target="_blank" class="btn btn-soft-primary btn-sm"><i class="bx bx-printer fs-16"></i> Print Invoice</a>
                                @if($order->items->where('logistics_provider', 'NCM')->isNotEmpty())
                                    <button type="button" id="refresh_tracking" 
                                        data-url="{{ route('admin.orders.sync_ncm', $order->order_reference_id) }}" 
                                        data-token="{{ csrf_token() }}"
                                        class="btn btn-soft-info btn-sm">
                                        <i class="bx bx-refresh fs-16"></i> Sync NCM Status
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Logistics</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th class="text-end">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    @php
                                        $variantImage = null;
                                        if ($item->variant && $item->variant->image) {
                                            $images = json_decode($item->variant->image, true);
                                            $variantImage = $images[0] ?? null;
                                        }

                                        // Fallback to product thumbnail if variant image is not available
                                        if (!$variantImage && $item->product && $item->product->thumbnail) {
                                            $variantImage = $item->product->thumbnail;
                                        }

                                        $item_status_val = $item->status ?? '0';
                                        $item_status_text = "Pending";
                                        $item_status_class = "bg-soft-warning text-warning";
                                        if($item_status_val == '1') { $item_status_text = "Confirmed"; $item_status_class = "bg-soft-info text-info"; }
                                        if($item_status_val == '2') { $item_status_text = "Shipped"; $item_status_class = "bg-soft-info text-info"; }
                                        if($item_status_val == '3') { $item_status_text = "Delivered"; $item_status_class = "bg-soft-success text-success"; }
                                        if($item_status_val == '4') { $item_status_text = "Cancelled"; $item_status_class = "bg-soft-danger text-danger"; }
                                        if($item_status_val == '5') { $item_status_text = "Returned"; $item_status_class = "bg-soft-danger text-danger"; }
                                        if($item_status_val == '6') { $item_status_text = "In Dispute"; $item_status_class = "bg-soft-danger text-danger"; }

                                        // Determine image path
                                        $finalImageUrl = null;
                                        if ($variantImage) {
                                            if (file_exists(public_path('uploads/products/' . $variantImage))) {
                                                $finalImageUrl = asset('uploads/products/' . $variantImage);
                                            } elseif (file_exists(public_path('storage/' . $variantImage))) {
                                                $finalImageUrl = asset('storage/' . $variantImage);
                                            } else {
                                                $finalImageUrl = asset('uploads/products/' . $variantImage); // Default to products path
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-md bg-light rounded">
                                                    @if($finalImageUrl)
                                                        <img src="{{ $finalImageUrl }}" alt="" class="avatar-sm">
                                                    @else
                                                        <div class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                            <i class="bx bx-package fs-24"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h5 class="fw-medium fs-14 mb-1">{{ $item->product->name ?? 'Product Deleted' }}</h5>
                                                    <p class="text-muted mb-0">SKU: {{ $item->variant->sku ?? ($item->product->sku ?? 'N/A') }}</p>
                                                    @if($item->variant)
                                                        @php
                                                            $sizeRaw = $item->variant->size ?? null;
                                                            $sizeDisplay = null;
                                                            if ($sizeRaw) {
                                                                $trim = trim($sizeRaw);
                                                                if ($trim !== '[]') {
                                                                    if (substr($trim, 0, 1) === '[' && substr($trim, -1) === ']') {
                                                                        $arr = json_decode($trim, true);
                                                                        if (is_array($arr) && count($arr) > 0) {
                                                                            $sizeDisplay = implode(', ', $arr);
                                                                        }
                                                                    } else {
                                                                        $sizeDisplay = $sizeRaw;
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        <small class="text-muted">
                                                            @if($item->variant->color) Color: {{ $item->variant->color }} @endif
                                                            @if($sizeDisplay) @if($item->variant->color) | @endif Size: {{ $sizeDisplay }} @endif
                                                        </small>
                                                    @endif
                                                    @if($item->vendor_tax)
                                                        <br><small class="text-info">VAT/Tax ID: {{ $item->vendor_tax }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>
                                            @if($item->logistics_provider == 'NCM')
                                                <div class="d-flex flex-column gap-1">
                                                    <span class="badge bg-soft-info text-info d-inline-block" style="width: fit-content;">NCM Tracking</span>
                                                    <small class="text-muted fw-bold">#{{ $item->tracking_id }}</small>
                                                    <span class="badge bg-soft-primary text-primary d-inline-block" style="width: fit-content;">{{ $item->logistics_status ?? 'Packed' }}</span>
                                                    <a href="https://nepalcanmove.com/track?tracking_id={{ $item->tracking_id }}" target="_blank" class="text-primary fs-12"><i class="bx bx-link-external"></i> Track on NCM</a>
                                                </div>
                                            @else
                                                <span class="text-muted small">Not Assigned</span>
                                            @endif
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

                                        <td>
                                            <!-- Badge -->
                                            <span
                                                class="badge {{ $item_status_class }} order-status-badge"
                                                data-order-id="{{ $item->id ?? '' }}"
                                                style="cursor:pointer">
                                                {{ $item_status_text }}
                                            </span>
                                            <!-- Select (hidden) -->
                                            <select
                                                class="form-select form-select-sm order-status-select d-none"
                                                data-order-id="{{ $item->id ?? '' }}">
                                                @if($item_status_val == '6')
                                                    <!-- Only Return option available for disputed orders -->
                                                    <option value="5" {{ $item_status_val == '5' ? 'selected' : '' }}>Returned</option>
                                                @else
                                                    <option value="0" {{ $item_status_val == '0' ? 'selected' : '' }}>Pending</option>
                                                    <option value="1" {{ $item_status_val == '1' ? 'selected' : '' }}>Confirmed</option>
                                                    <option value="2" {{ $item_status_val == '2' ? 'selected' : '' }}>Shipped</option>
                                                    <option value="3" {{ $item_status_val == '3' ? 'selected' : '' }}>Delivered</option>
                                                    <option value="4" {{ $item_status_val == '4' ? 'selected' : '' }}>Cancelled</option>
                                                    <option value="5" {{ $item_status_val == '5' ? 'selected' : '' }}>Returned</option>
                                                    <option value="6" {{ $item_status_val == '6' ? 'selected' : '' }}>In Dispute</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td class="text-end">{{ $currency }} {{ number_format($item->price * $item->quantity, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row justify-content-end mt-2">
                            <div class="col-lg-5 col-sm-6">
                                <table class="table table-borderless align-middle mb-0 summary-table">
                                    <tbody>
                                        <tr>
                                            <td class="text-body">Sub Total :</td>
                                            <td class="text-end">{{ $currency }} {{ number_format($order->sub_total, 2) }}</td>
                                        </tr>
                                        @if(($order->product_discounts ?? 0) > 0)
                                        <tr>
                                            <td class="text-body">Product Discounts : </td>
                                            <td class="text-end text-danger">- {{ $currency }} {{ number_format($order->product_discounts, 2) }}</td>
                                        </tr>
                                        @endif
                                        @if(($order->offer_discounts ?? 0) > 0)
                                        <tr>
                                            <td class="text-body">Offer Discount : </td>
                                            <td class="text-end text-danger">- {{ $currency }} {{ number_format($order->offer_discounts, 2) }}</td>
                                        </tr>
                                        @endif
                                        @if(($order->coupon_discounts ?? 0) > 0)
                                        <tr>
                                            <td class="text-body">Coupon Discount : </td>
                                            <td class="text-end text-danger">- {{ $currency }} {{ number_format($order->coupon_discounts, 2) }}</td>
                                        </tr>
                                        @endif
                                        @if($order->campaign_discounts > 0)
                                        <tr>
                                            <td class="text-body">Campaign Discount : </td>
                                            <td class="text-end text-danger">- {{ $currency }} {{ number_format($order->campaign_discounts, 2) }}</td>
                                        </tr>
                                        @endif
                                        @if(($order->delivery_charges ?? 0) > 0)
                                        <tr>
                                            <td class="text-body">Shipping Charge :</td>
                                            <td class="text-end">{{ $currency }} {{ number_format($order->delivery_charges, 2) }}</td>
                                        </tr>
                                        @endif
                                        @if(($order->taxes ?? 0) > 0)
                                        <tr>
                                            <td class="text-body">Estimated Tax :</td>
                                            <td class="text-end">{{ $currency }} {{ number_format($order->taxes, 2) }}</td>
                                        </tr>
                                        @endif
                                        <tr class="grand-total">
                                            <td class="fw-bold text-body">Total :</td>
                                            <td class="text-end fw-bold text-body">{{ $currency }} {{ number_format($order->total_cost, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-body">Order Timeline</h4>
                    </div>
                    <div class="card-body">
                        <div class="track-order-list">
                            <div class="track-order-item completed">
                                <span class="track-icon">
                                    <i class="bx bx-shopping-bag"></i>
                                </span>
                                <div class="track-content">
                                    <h5 class="mt-0">Order Placed</h5>
                                    <p class="mb-0">{{ $order->created_at->format('d M, Y - h:i A') }}</p>
                                </div>
                            </div>
                            @php
                                $representative_status = $order->items->first()->status ?? 0;
                                $ncmItems = $order->items->where('logistics_provider', 'NCM');
                                $latestLogisticsStatus = $ncmItems->pluck('logistics_status')->unique()->last();
                            @endphp
                            
                            @if($ncmItems->isNotEmpty())
                                <div class="track-order-item {{ $latestLogisticsStatus ? 'completed' : '' }}">
                                    <span class="track-icon">
                                        <i class="bx bx-package"></i>
                                    </span>
                                    <div class="track-content">
                                        <h5 class="mt-0">Assigned to NCM</h5>
                                        <p class="mb-0">Order sent to NCM Logistics.</p>
                                    </div>
                                </div>
                                @if(in_array($latestLogisticsStatus, ['Dispatched', 'In Transit', 'Delivered']))
                                    <div class="track-order-item completed">
                                        <span class="track-icon">
                                            <i class="bx bx-transfer"></i>
                                        </span>
                                        <div class="track-content">
                                            <h5 class="mt-0">NCM Status: {{ $latestLogisticsStatus }}</h5>
                                            <p class="mb-0">Updated via NCM Real-time tracking.</p>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            @if($representative_status >= 1)
                            <div class="track-order-item completed">
                                <span class="track-icon">
                                    <i class="bx bx-check-circle"></i>
                                </span>
                                <div class="track-content">
                                    <h5 class="mt-0">Order Confirmed</h5>
                                    <p class="mb-0">Your order has been confirmed.</p>
                                </div>
                            </div>
                            @endif

                            @if($representative_status >= 2)
                            <div class="track-order-item completed">
                                <span class="track-icon">
                                    <i class="bx bx-truck"></i>
                                </span>
                                <div class="track-content">
                                    <h5 class="mt-0">Shipped</h5>
                                    <p class="mb-0">Your order has been shipped.</p>
                                </div>
                            </div>
                            @endif

                            @if($representative_status >= 3)
                            <div class="track-order-item completed">
                                <span class="track-icon">
                                    <i class="bx bx-home-alt"></i>
                                </span>
                                <div class="track-content">
                                    <h5 class="mt-0">Delivered</h5>
                                    <p class="mb-0">Your order has been delivered.</p>
                                </div>
                            </div>
                            @endif

                            @if($representative_status == 4)
                            <div class="track-order-item completed">
                                <span class="track-icon bg-danger border-danger text-white">
                                    <i class="bx bx-x-circle"></i>
                                </span>
                                <div class="track-content">
                                    <h5 class="mt-0 text-danger">Cancelled</h5>
                                    <p class="mb-0">Your order has been cancelled.</p>
                                </div>
                            </div>
                            @endif

                            @if($representative_status == 5)
                            <div class="track-order-item completed">
                                <span class="track-icon bg-warning border-warning text-white">
                                    <i class="bx bx-undo"></i>
                                </span>
                                <div class="track-content">
                                    <h5 class="mt-0 text-warning">Returned</h5>
                                    <p class="mb-0">Your order has been returned.</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-body">Customer Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md bg-soft-primary rounded">
                                <div class="avatar-title rounded-circle bg-soft-primary text-primary">
                                    {{ substr($order->user->name ?? 'C', 0, 1) }}
                                </div>
                            </div>
                            <div>
                                <h5 class="fw-medium fs-14 mb-1">{{ $order->user->name ?? 'Guest Customer' }}</h5>
                                <p class="text-muted mb-0">Customer</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="text-muted mb-1"><i class="bx bx-envelope align-middle me-1"></i> {{ $order->user->email ?? 'N/A' }}</p>
                            <p class="text-muted mb-0"><i class="bx bx-phone align-middle me-1"></i> {{ $order->user->phone ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="card shipping-address-box">
                    <div class="card-header">
                        <h4 class="card-title text-body">Shipping Address</h4>
                    </div>
                    <div class="card-body">
                        @if($order->shippingAddress)
                        <h5 class="fs-14 mb-2">{{ $order->shippingAddress->name }}</h5>
                        <p class="text-muted mb-1"><i class="bx bx-envelope align-middle me-1"></i> {{ $order->shippingAddress->email ?? 'N/A' }}</p>
                        <p class="text-muted mb-1"><i class="bx bx-phone align-middle me-1"></i> {{ $order->shippingAddress->phone ?? 'N/A' }}</p>
                        <p class="text-muted mb-1"><i class="bx bx-map align-middle me-1"></i> {{ $order->shippingAddress->address }}</p>
                        <p class="text-muted mb-1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $order->shippingAddress->city->name ?? $order->shippingAddress->city }}, {{ $order->shippingAddress->state->name ?? $order->shippingAddress->state }} - {{ $order->shippingAddress->zip }}</p>
                        <p class="text-muted mb-0">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $order->shippingAddress->country->name ?? $order->shippingAddress->country }}</p>
                        @else
                        <p class="text-muted mb-0">No shipping address provided.</p>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-body">Payment Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar-sm bg-soft-primary rounded">
                                <iconify-icon icon="solar:card-linear" class="avatar-title fs-24 text-primary"></iconify-icon>
                            </div>
                            <div>
                                <h5 class="fs-14 mb-0">{{ strtoupper($order->payment_mode) }}</h5>
                                <p class="text-muted mb-0">Payment Mode</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-sm bg-soft-success rounded">
                                <iconify-icon icon="solar:check-circle-linear" class="avatar-title fs-24 text-success"></iconify-icon>
                            </div>
                            <div>
                                <h5 class="fs-14 mb-0">{{ ucfirst($order->payment_status == 1 ? 'Paid' : 'Unpaid') }}</h5>
                                <p class="text-muted mb-0">Status</p>
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
<script src="{{ asset('backend/assets/js/custom.js') }}"></script>
@endpush
