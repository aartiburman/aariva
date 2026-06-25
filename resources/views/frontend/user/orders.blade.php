@extends('frontend.layouts.app')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">My Orders</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">My Orders</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="py-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif

                @if ($orders->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bx bx-package" style="font-size: 80px; color: #ddd;"></i>
                    </div>
                    <h4 class="text-muted mb-3">No orders yet</h4>
                    <p class="text-muted mb-4">You haven't placed any orders yet. Start shopping!</p>
                    <a href="{{ route('frontend.products.index') }}" class="btn btn-dark btn-lg px-5 rounded-pill">Start Shopping</a>
                </div>
                @else
                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold">Your Orders ({{ $orders->total() }})</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        @php $currentStatus = request('status'); @endphp
                        <a href="{{ route('frontend.user.orders') }}" class="btn btn-sm {{ !$currentStatus ? 'btn-dark' : 'btn-outline-dark' }} rounded-pill px-3">All</a>
                        <a href="{{ route('frontend.user.orders', ['status' => 'pending']) }}" class="btn btn-sm {{ $currentStatus == 'pending' ? 'btn-warning text-white' : 'btn-outline-warning' }} rounded-pill px-3">Pending</a>
                        <a href="{{ route('frontend.user.orders', ['status' => 'processing']) }}" class="btn btn-sm {{ $currentStatus == 'processing' ? 'btn-info text-white' : 'btn-outline-info' }} rounded-pill px-3">Processing</a>
                        <a href="{{ route('frontend.user.orders', ['status' => 'completed']) }}" class="btn btn-sm {{ $currentStatus == 'completed' ? 'btn-success' : 'btn-outline-success' }} rounded-pill px-3">Completed</a>
                        <a href="{{ route('frontend.user.orders', ['status' => 'cancelled']) }}" class="btn btn-sm {{ $currentStatus == 'cancelled' ? 'btn-danger' : 'btn-outline-danger' }} rounded-pill px-3">Cancelled</a>
                    </div>
                </div>

                @foreach ($orders as $order)
                @php
                    $statusLabels = ['pending', 'processing', 'completed', 'cancelled'];
                    $statusBadges = ['pending' => 'bg-warning text-dark', 'processing' => 'bg-info text-white', 'completed' => 'bg-success', 'cancelled' => 'bg-danger'];
                    $statusIcons = ['pending' => 'bx-time-five', 'processing' => 'bx-loader-circle', 'completed' => 'bx-check-circle', 'cancelled' => 'bx-x-circle'];
                    $currentLabel = $statusLabels[$order->status] ?? 'unknown';
                @endphp
                <div class="card border-0 shadow-sm mb-3 overflow-hidden">
                    <div class="card-header bg-white border-bottom d-flex flex-wrap align-items-center justify-content-between py-3 px-4">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div>
                                <small class="text-muted d-block">ORDER REFERENCE</small>
                                <strong>#{{ $order->order_reference_id }}</strong>
                            </div>
                            <div class="text-muted d-none d-md-block">|</div>
                            <div>
                                <small class="text-muted d-block">PLACED ON</small>
                                <span>{{ $order->order_date ? date('d M Y, h:i A', strtotime($order->order_date)) : 'N/A' }}</span>
                            </div>
                            <div class="text-muted d-none d-md-block">|</div>
                            <div>
                                <small class="text-muted d-block">{{ $order->items->count() }} ITEM(S)</small>
                                <span>{{ App\Helpers\PriceHelper::formatPrice($order->total_cost) }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                            <span class="badge {{ $statusBadges[$currentLabel] ?? 'bg-secondary' }} rounded-pill px-3 py-2">
                                <i class="bx {{ $statusIcons[$currentLabel] ?? 'bx-help-circle' }} me-1"></i>
                                {{ ucfirst($currentLabel) }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body px-4 py-3">
                        @foreach ($order->items as $item)
                        @php
                            $itemImage = $item->product && $item->product->thumbnail
                                ? App\Helpers\ImageHelper::getProductImage($item->product->thumbnail)
                                : asset('frontend/assets/images/products/01.png');
                            $itemName = $item->product->name ?? 'Product';
                            $itemSlug = $item->product->slug ?? $item->product_id;
                            $variantText = '';
                            if ($item->variant) {
                                $variantText = trim(($item->variant->size ?? '') . ' ' . ($item->variant->color ?? ''));
                            }
                        @endphp
                        <div class="row align-items-center py-2 {{ !$loop->last ? 'border-bottom mb-2' : '' }}">
                            <div class="col-auto">
                                <a href="{{ route('frontend.products.show', $itemSlug) }}">
                                    <img src="{{ $itemImage }}" alt="{{ $itemName }}" class="rounded" style="width: 64px; height: 64px; object-fit: cover;">
                                </a>
                            </div>
                            <div class="col">
                                <a href="{{ route('frontend.products.show', $itemSlug) }}" class="text-decoration-none text-dark fw-semibold">{{ $itemName }}</a>
                                @if ($variantText)
                                <div class="text-muted small">{{ $variantText }}</div>
                                @endif
                                <div class="text-muted small mt-1">Qty: {{ $item->quantity }}</div>
                            </div>
                            <div class="col-auto text-end">
                                <div class="fw-bold">{{ App\Helpers\PriceHelper::formatPrice($item->price) }}</div>
                                @if (($item->discount + $item->offer_discount + $item->campaign_discount) > 0)
                                <small class="text-success">Saved {{ App\Helpers\PriceHelper::formatPrice($item->discount + $item->offer_discount + $item->campaign_discount) }}</small>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="card-footer bg-white border-top px-4 py-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-3">
                                @if ($order->status == 0)
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill px-3 py-2">
                                    <i class="bx bx-time me-1"></i> Awaiting Confirmation
                                </span>
                                @elseif ($order->status == 1)
                                <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3 py-2">
                                    <i class="bx bx-package me-1"></i> Preparing to Ship
                                </span>
                                @elseif ($order->status == 2)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-2">
                                    <i class="bx bx-check-double me-1"></i> Delivered Successfully
                                </span>
                                @elseif ($order->status == 3)
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3 py-2">
                                    <i class="bx bx-x me-1"></i> Order Cancelled
                                </span>
                                @endif

                                <small class="text-muted d-none d-md-inline">
                                    @if ($order->payment_status)
                                    <i class="bx bx-check-circle text-success me-1"></i> Paid via {{ $order->payment_mode }}
                                    @else
                                    <i class="bx bx-time text-warning me-1"></i> Payment Pending ({{ $order->payment_mode }})
                                    @endif
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('frontend.user.order-detail', $order->id) }}" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                                    <i class="bx bx-show me-1"></i> View Details
                                </a>
                            </div>
                        </div>

                        @if ($order->status == 0 || $order->status == 1)
                        <div class="mt-3">
                            <div class="d-flex align-items-center gap-2">
                                @php $progress = $order->status == 0 ? 25 : 50; @endphp
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">{{ $order->status == 0 ? 'Order Placed' : 'Processing' }}</small>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mt-1 px-1">
                                <span class="text-success"><i class="bx bx-check-circle"></i> Placed</span>
                                <span class="{{ $order->status >= 1 ? 'text-success' : '' }}"><i class="bx {{ $order->status >= 1 ? 'bx-check-circle' : 'bx-circle' }}"></i> Processing</span>
                                <span><i class="bx bx-circle"></i> Shipped</span>
                                <span><i class="bx bx-circle"></i> Delivered</span>
                            </div>
                        </div>
                        @elseif ($order->status == 2)
                        <div class="mt-3">
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mt-1 px-1">
                                <span class="text-success"><i class="bx bx-check-circle"></i> Placed</span>
                                <span class="text-success"><i class="bx bx-check-circle"></i> Processing</span>
                                <span class="text-success"><i class="bx bx-check-circle"></i> Shipped</span>
                                <span class="text-success"><i class="bx bx-check-double"></i> Delivered</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach

                <div class="mt-4">
                    {{ $orders->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.progress { background-color: #e9ecef; border-radius: 10px; }
.progress-bar { border-radius: 10px; transition: width 0.6s ease; }
.card { border-radius: 12px !important; }
.card-header:first-child { border-radius: 12px 12px 0 0 !important; }
.card-footer:last-child { border-radius: 0 0 12px 12px !important; }
.page-link { color: #212529; }
.page-item.active .page-link { background-color: #212529; border-color: #212529; }
</style>
@endpush
