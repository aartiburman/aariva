@extends('frontend.layouts.app')

@section('content')
<section class="py-3 border-bottom border-top d-none d-md-flex bg-light">
    <div class="container">
        <div class="page-breadcrumb d-flex align-items-center">
            <h3 class="breadcrumb-title pe-3">Order Complete</h3>
            <div class="ms-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Order Complete</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>
<section class="py-4">
    <div class="container">
        <div class="card py-3 mt-sm-3">
            <div class="card-body text-center">
                <h2 class="h4 pb-3">Thank you for your order!</h2>
                <p class="fs-sm mb-2">Your order has been placed and will be processed as soon as possible.</p>
                <p class="fs-sm mb-2">
                    Your order number is <strong>{{ $order->order_reference_id }}</strong>.
                </p>
                <p class="fs-sm">You will be receiving an email shortly with confirmation of your order.</p>
                <a class="btn btn-light rounded-0 mt-3 me-3" href="{{ route('frontend.home') }}"><i class="bx bx-home"></i> Go back shopping</a>
                <a class="btn btn-dark rounded-0 mt-3" href="{{ route('frontend.user.orders') }}"><i class="bx bx-receipt"></i> View Orders</a>
            </div>
        </div>

        <div class="card mt-4 rounded-0">
            <div class="card-body">
                <h5 class="mb-3">Order Details</h5>
                <table class="table table-bordered">
                    <tr>
                        <th>Order Reference</th>
                        <td>{{ $order->order_reference_id }}</td>
                    </tr>
                    <tr>
                        <th>Transaction ID</th>
                        <td>{{ $order->transaction_id }}</td>
                    </tr>
                    <tr>
                        <th>Payment Mode</th>
                        <td>{{ $order->payment_mode }}</td>
                    </tr>
                    <tr>
                        <th>Order Date</th>
                        <td>{{ $order->order_date ? date('d M Y, h:i A', strtotime($order->order_date)) : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Total Cost</th>
                        <td>{{ App\Helpers\PriceHelper::formatPrice($order->total_cost) }}</td>
                    </tr>
                    <tr>
                        <th>Shipping Address</th>
                        <td>
                            @if ($order->shippingAddress)
                                {{ $order->shippingAddress->name }},
                                {{ $order->shippingAddress->address }},
                                {{ $order->shippingAddress->city }},
                                {{ $order->shippingAddress->state }} {{ $order->shippingAddress->zip }}
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                </table>

                <h5 class="mt-4 mb-3">Items</h5>
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? 'N/A' }}</td>
                            <td>{{ App\Helpers\PriceHelper::formatPrice($item->price) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ App\Helpers\PriceHelper::formatPrice($item->total_actual_price) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
