@extends('backend.layouts.app')

@section('content')
<style>
    .shipping-address-box p, .shipping-address-box h5 {
        word-wrap: break-word;
        overflow-wrap: break-word;
        word-break: break-all;
    }
</style>
@php
    $currency = 'INR';
    if ($order->items->first() && $order->items->first()->vendor && $order->items->first()->vendor->country) {
        $currency = $order->items->first()->vendor->country->currency ?? 'INR';
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
                            <h4 class="card-title">Order #{{ $order->order_reference_id }}</h4>
                            <div class="d-flex gap-2">
                                <a href="{{ route('orders.invoice', $order->order_reference_id) }}" target="_blank" class="btn btn-soft-primary btn-sm"><i class="bx bx-printer fs-16"></i> Print Invoice</a>

                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Logistics</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th class="text-end">Total</th>
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
                                                    <h5 class="text-dark fw-medium fs-14 mb-1">{{ $item->product->name ?? 'Product Deleted' }}</h5>
                                                    <p class="text-muted mb-0">SKU: {{ $item->variant->sku ?? ($item->product->sku ?? 'N/A') }}</p>
                                                    @if($item->variant)
                                                        <small class="text-muted">
                                                            @if($item->variant->color) Color: {{ $item->variant->color }} @endif
                                                            @if($item->variant->size) | Size: {{ $item->variant->size }} @endif
                                                        </small>
                                                    @endif
                                                    @if($item->vat_or_tax)
                                                        <br><small class="text-info">VAT/Tax ID: {{ $item->vat_or_tax }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $currency }}{{ number_format($item->price, 2) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>
                                            <span class="text-muted small">{{ $item->tracking_id ? '#' . $item->tracking_id : 'Not Assigned' }}</span>
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
                                                <option value="0" {{ $item_status_val == '0' ? 'selected' : '' }}>Pending</option>
                                                <option value="1" {{ $item_status_val == '1' ? 'selected' : '' }}>Confirmed</option>
                                                <option value="2" {{ $item_status_val == '2' ? 'selected' : '' }}>Shipped</option>
                                                <option value="3" {{ $item_status_val == '3' ? 'selected' : '' }}>Delivered</option>
                                                <option value="4" {{ $item_status_val == '5' ? 'selected' : '' }}>Cancelled</option>
                                                <option value="5" {{ $item_status_val == '6' ? 'selected' : '' }}>Returned</option>
                                            </select>
                                        </td>
                                        <td class="text-end">{{ $currency }}{{ number_format($item->price * $item->quantity, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row justify-content-end mt-3">
                            <div class="col-lg-4 col-sm-6">
                                <div class="table-responsive">
                                    <table class="table table-borderless table-nowrap align-middle mb-0">
                                        <tbody>
                                            <tr>
                                                <td>Sub Total :</td>
                                                <td class="text-end">{{ $currency }}{{ number_format($order->sub_total, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Product Discounts : </td>
                                                <td class="text-end text-danger">- {{ $currency }}{{ number_format($order->product_discounts, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Offer Discount : </td>
                                                <td class="text-end text-danger">- {{ $currency }}{{ number_format($order->offer_discounts, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Coupon Discount : </td>
                                                <td class="text-end text-danger">- {{ $currency }}{{ number_format($order->coupon_discounts, 2) }}</td>
                                            </tr>
                                            @if($order->campaign_discounts > 0)
                                            <tr>
                                                <td>Campaign Discount : </td>
                                                <td class="text-end text-danger">- {{ $currency }}{{ number_format($order->campaign_discounts, 2) }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td>Shipping Charge :</td>
                                                <td class="text-end">{{ $currency }}{{ number_format($order->delivery_charges, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Estimated Tax :</td>
                                                <td class="text-end">{{ $currency }}{{ number_format($order->taxes, 2) }}</td>
                                            </tr>
                                            <tr class="border-top">
                                                <td class="fw-bold">Total :</td>
                                                <td class="text-end fw-bold">{{ $currency }}{{ number_format($order->total_cost, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Order Timeline</h4>
                    </div>
                    <div class="card-body">
                        <div class="position-relative">
                            <div class="track-order-list">
                                <ul class="list-unstyled">
                                    <li class="completed">
                                        <span class="active-dot dot"></span>
                                        <h5 class="mt-0 mb-1">Order Placed</h5>
                                        <p class="text-muted fs-13 mb-0">{{ $order->created_at->format('d M, Y - h:i A') }}</p>
                                    </li>
                                    @php
                                        $representative_status = $order->items->first()->status ?? 0;
                                    @endphp

                                    @if($representative_status >= 1)
                                    <li class="completed">
                                        <span class="active-dot dot"></span>
                                        <h5 class="mt-0 mb-1">Order Confirmed</h5>
                                        <p class="text-muted fs-13 mb-0">Your order has been confirmed.</p>
                                    </li>
                                    @endif
                                    @if($representative_status >= 2)
                                    <li class="completed">
                                        <span class="active-dot dot"></span>
                                        <h5 class="mt-0 mb-1">Shipped</h5>
                                        <p class="text-muted fs-13 mb-0">Your order has been shipped.</p>
                                    </li>
                                    @endif
                                    @if($representative_status >= 3)
                                    <li class="completed">
                                        <span class="active-dot dot"></span>
                                        <h5 class="mt-0 mb-1">Delivered</h5>
                                        <p class="text-muted fs-13 mb-0">Your order has been delivered.</p>
                                    </li>
                                    @endif
                                    @if($representative_status == 4)
                                    <li class="completed">
                                        <span class="active-dot dot"></span>
                                        <h5 class="mt-0 mb-1">Cancelled</h5>
                                        <p class="text-muted fs-13 mb-0">Your order has been cancelled.</p>
                                    </li>
                                    @endif
                                    @if($representative_status == 5)
                                    <li class="completed">
                                        <span class="active-dot dot"></span>
                                        <h5 class="mt-0 mb-1">Returned</h5>
                                        <p class="text-muted fs-13 mb-0">Your order has been returned.</p>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Customer Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-md bg-soft-primary rounded">
                                <div class="avatar-title rounded-circle bg-soft-primary text-primary">
                                    {{ substr($order->user->name ?? 'C', 0, 1) }}
                                </div>
                            </div>
                            <div>
                                <h5 class="text-dark fw-medium fs-14 mb-1">{{ $order->user->name ?? 'Guest Customer' }}</h5>
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
                        <h4 class="card-title">Shipping Address</h4>
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
                        <h4 class="card-title">Payment Information</h4>
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
@endpush
