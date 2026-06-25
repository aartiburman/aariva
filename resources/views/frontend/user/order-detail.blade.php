@extends('frontend.layouts.app')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">Order Detail</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('frontend.user.orders') }}">My Orders</a></li>
                        <li class="breadcrumb-item active" aria-current="page">#{{ $order->order_reference_id }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="py-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="card rounded-0">
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('frontend.user.profile') }}" class="list-group-item list-group-item-action">My Profile</a>
                            <a href="{{ route('frontend.user.orders') }}" class="list-group-item list-group-item-action active">My Orders</a>
                            <a href="{{ route('frontend.wishlist.index') }}" class="list-group-item list-group-item-action">Wishlist</a>
                            <a class="list-group-item list-group-item-action text-danger" href="{{ route('frontend.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="card rounded-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h5 class="mb-0">Order #{{ $order->order_reference_id }}</h5>
                            <a href="{{ route('frontend.user.orders') }}" class="btn btn-light btn-ecomm"><i class="bx bx-arrow-back"></i> Back</a>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr><th>Transaction ID</th><td>{{ $order->transaction_id }}</td></tr>
                                    <tr><th>Payment Mode</th><td>{{ $order->payment_mode }}</td></tr>
                                    <tr><th>Payment Status</th>
                                        <td>
                                            @if ($order->payment_status)
                                            <span class="badge bg-success">Paid</span>
                                            @else
                                            <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr><th>Order Status</th>
                                        <td>
                                            @if ($order->status == 0) <span class="badge bg-warning">Pending</span>
                                            @elseif ($order->status == 1) <span class="badge bg-info">Processing</span>
                                            @elseif ($order->status == 2) <span class="badge bg-success">Completed</span>
                                            @elseif ($order->status == 3) <span class="badge bg-danger">Cancelled</span>
                                            @else <span class="badge bg-secondary">{{ $order->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr><th>Order Date</th><td>{{ $order->order_date ? date('d M Y, h:i A', strtotime($order->order_date)) : 'N/A' }}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Shipping Address</h6>
                                @if ($order->shippingAddress)
                                <p class="mb-0">{{ $order->shippingAddress->name }}</p>
                                <p class="mb-0">{{ $order->shippingAddress->phone }}</p>
                                <p class="mb-0">{{ $order->shippingAddress->address }}</p>
                                <p class="mb-0">{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }} {{ $order->shippingAddress->zip }}</p>
                                @else
                                <p class="text-muted">N/A</p>
                                @endif
                            </div>
                        </div>

                        <h6 class="mb-3">Order Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Product</th>
                                        <th>Variant</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Discount</th>
                                        <th>Tax</th>
                                        <th>Delivery</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                                        <td>
                                            @if ($item->variant)
                                                {{ $item->variant->size ?? '' }} {{ $item->variant->color ?? '' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ App\Helpers\PriceHelper::formatPrice($item->price) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ App\Helpers\PriceHelper::formatPrice($item->discount + $item->offer_discount + $item->campaign_discount) }}</td>
                                        <td>{{ App\Helpers\PriceHelper::formatPrice($item->tax_amount) }}</td>
                                        <td>{{ App\Helpers\PriceHelper::formatPrice($item->delivery_charges) }}</td>
                                        <td>{{ App\Helpers\PriceHelper::formatPrice($item->total_actual_price) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="7" class="text-end">Subtotal:</th>
                                        <th>{{ App\Helpers\PriceHelper::formatPrice($order->sub_total) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="7" class="text-end">Discount:</th>
                                        <th>- {{ App\Helpers\PriceHelper::formatPrice($order->total_discount) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="7" class="text-end">Delivery Charges:</th>
                                        <th>{{ App\Helpers\PriceHelper::formatPrice($order->delivery_charges) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="7" class="text-end">Tax:</th>
                                        <th>{{ App\Helpers\PriceHelper::formatPrice($order->taxes) }}</th>
                                    </tr>
                                    <tr class="table-active">
                                        <th colspan="7" class="text-end">Grand Total:</th>
                                        <th>{{ App\Helpers\PriceHelper::formatPrice($order->total_cost) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
