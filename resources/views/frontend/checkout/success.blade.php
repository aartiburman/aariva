@php
  $paymentStatus = $order->payment_status ?? 0;
  $orderStatus = $order->status ?? 0;
  $isSuccess = $paymentStatus || $orderStatus >= 0;
  $deliveryDate = $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('d M Y') : $deliveryEnd ?? 'N/A';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Order Confirmed - {{ config('app.name') }}</title>
  <meta name="robots" content="noindex, nofollow">
  <link href="{{ asset('frontend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
  <link href="{{ asset('frontend/assets/css/checkout.css') }}" rel="stylesheet">
</head>
<body style="background:#FFFDF8;font-family:'Albert Sans',sans-serif;">
  <div class="checkout-logo">
    <a href="{{ route('frontend.home') }}">
      <img src="{{ asset('frontend/assets/images/logo-icon.png') }}" alt="{{ config('app.name') }}">
    </a>
  </div>

  <div class="confirmation-page">
    {{-- Confirmation Header --}}
    <div class="confirmation-header">
      <div class="check-icon"><i class="bx bx-check"></i></div>
      <h2>{{ __('Thank You!') }}</h2>
      <p>
        @auth
          {{ __('Hi') }} {{ Auth::user()->name }},
        @endauth
        {{ __('Your order has been confirmed.') }}
      </p>
    </div>

    {{-- Order Details --}}
    <div class="confirmation-details">
      <div class="detail-row">
        <span class="lbl">{{ __('Order Number') }}</span>
        <span class="val" style="color:#C89B3C;">{{ $order->order_reference_id }}</span>
      </div>
      <div class="detail-row">
        <span class="lbl">{{ __('Transaction ID') }}</span>
        <span class="val">{{ $order->transaction_id ?? 'N/A' }}</span>
      </div>
      <div class="detail-row">
        <span class="lbl">{{ __('Payment Mode') }}</span>
        <span class="val">{{ $order->payment_mode }}</span>
      </div>
      <div class="detail-row">
        <span class="lbl">{{ __('Order Date') }}</span>
        <span class="val">{{ $order->order_date ? date('d M Y, h:i A', strtotime($order->order_date)) : 'N/A' }}</span>
      </div>
      <div class="detail-row">
        <span class="lbl">{{ __('Total Amount') }}</span>
        <span class="val" style="font-size:18px;">{{ App\Helpers\PriceHelper::formatPrice($order->total_cost) }}</span>
      </div>
      <div class="detail-row">
        <span class="lbl">{{ __('Expected Delivery') }}</span>
        <span class="val" style="color:#28A745;">{{ $deliveryDate }}</span>
      </div>
      <div class="detail-row">
        <span class="lbl">{{ __('Delivery Address') }}</span>
        <span class="val" style="text-align:right;max-width:300px;">
          @if ($order->shippingAddress)
            {{ $order->shippingAddress->name }},<br>
            {{ $order->shippingAddress->address }},<br>
            {{ $order->shippingAddress->city }}@if($order->shippingAddress->state), {{ $order->shippingAddress->state }}@endif @if($order->shippingAddress->zip) - {{ $order->shippingAddress->zip }}@endif
          @else
            N/A
          @endif
        </span>
      </div>
    </div>

    {{-- Items Review --}}
    <div class="confirmation-details">
      <h5 style="font-size:15px;font-weight:600;margin-bottom:12px;">{{ __('Items Ordered') }}</h5>
      <div class="order-review">
        <table>
          <thead>
            <tr>
              <th>{{ __('Product') }}</th>
              <th>{{ __('Qty') }}</th>
              <th>{{ __('Price') }}</th>
              <th>{{ __('Delivery') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($order->items as $item)
            <tr>
              <td>
                <div class="or-product">
                  <img src="{{ App\Helpers\ImageHelper::getProductImage($item->product->thumbnail ?? '') }}" alt="">
                  <div>
                    <div class="or-name">{{ $item->product->name ?? 'Product' }}</div>
                    <div class="or-meta">
                      @if ($item->variant) 
                        @if ($item->variant->color) Color: {{ $item->variant->color }} @endif
                        @if ($item->variant->size) Size: {{ $item->variant->size }} @endif
                      @endif
                      Seller: {{ $item->vendor->store_name ?? 'AARIVA' }}
                    </div>
                  </div>
                </div>
              </td>
              <td>{{ $item->quantity }}</td>
              <td class="or-price">{{ App\Helpers\PriceHelper::formatPrice($item->price) }}</td>
              <td class="or-delivery">{{ $deliveryDate }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- Price Breakdown --}}
    <div class="confirmation-details">
      <h5 style="font-size:15px;font-weight:600;margin-bottom:12px;">{{ __('Price Breakdown') }}</h5>
      <div class="detail-row">
        <span class="lbl">{{ __('Items Total') }}</span>
        <span class="val">{{ App\Helpers\PriceHelper::formatPrice($order->sub_total) }}</span>
      </div>
      @if ($order->product_discounts > 0)
      <div class="detail-row">
        <span class="lbl">{{ __('Product Discount') }}</span>
        <span class="val" style="color:#DC3545;">-{{ App\Helpers\PriceHelper::formatPrice($order->product_discounts) }}</span>
      </div>
      @endif
      @if ($order->coupon_discounts > 0)
      <div class="detail-row">
        <span class="lbl">{{ __('Coupon Discount') }}</span>
        <span class="val" style="color:#28A745;">-{{ App\Helpers\PriceHelper::formatPrice($order->coupon_discounts) }}</span>
      </div>
      @endif
      <div class="detail-row">
        <span class="lbl">{{ __('Shipping') }}</span>
        <span class="val" style="color:#28A745;">{{ $order->delivery_charges > 0 ? App\Helpers\PriceHelper::formatPrice($order->delivery_charges) : 'FREE' }}</span>
      </div>
      <div class="detail-row">
        <span class="lbl">{{ __('GST') }}</span>
        <span class="val">{{ App\Helpers\PriceHelper::formatPrice($order->taxes) }}</span>
      </div>
      <div class="detail-row" style="border-top:2px solid #222;padding-top:12px;margin-top:8px;">
        <span class="lbl" style="font-weight:700;font-size:16px;">{{ __('Total') }}</span>
        <span class="val" style="font-weight:700;font-size:18px;">{{ App\Helpers\PriceHelper::formatPrice($order->total_cost) }}</span>
      </div>
    </div>

    {{-- Action Buttons --}}
    <div class="confirmation-actions">
      <a href="{{ route('frontend.user.orders') }}" class="btn-track">
        <i class="bx bx-package"></i> {{ __('Track Order') }}
      </a>
      <a href="javascript:;" class="btn-continue" onclick="alert('Invoice download coming soon')">
        <i class="bx bx-download"></i> {{ __('Download Invoice') }}
      </a>
      <a href="{{ route('frontend.home') }}" class="btn-continue">
        <i class="bx bx-shopping-bag"></i> {{ __('Continue Shopping') }}
      </a>
    </div>

    {{-- Cross Selling --}}
    @if (isset($crossSellProducts) && $crossSellProducts->count() > 0)
    <div class="cross-sell">
      <h5><i class="bx bx-star" style="color:#C89B3C;"></i> {{ __('Customers also bought') }}</h5>
      <div class="cross-sell-grid">
        @foreach ($crossSellProducts as $csProduct)
        @php
          $firstVar = $csProduct->lowestPriceVariant;
          $csPrice = $firstVar ? $firstVar->price : $csProduct->price;
          $csImg = $csProduct->thumbnail ? App\Helpers\ImageHelper::getProductImage($csProduct->thumbnail) : asset('frontend/assets/images/products/01.png');
          $csSlug = $csProduct->slug;
        @endphp
        <a href="{{ route('frontend.products.show', $csSlug) }}" class="cross-sell-item" style="text-decoration:none;color:inherit;">
          <img src="{{ $csImg }}" alt="">
          <div class="cs-name">{{ $csProduct->name }}</div>
          <div class="cs-price">{{ App\Helpers\PriceHelper::formatPrice($csPrice) }}</div>
          <span class="cs-add">{{ __('View') }} <i class="bx bx-chevron-right"></i></span>
        </a>
        @endforeach
      </div>
    </div>
    @endif
  </div>

  <script src="{{ asset('frontend/assets/js/jquery.min.js') }}"></script>
</body>
</html>
