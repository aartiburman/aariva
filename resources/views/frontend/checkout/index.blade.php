@php
  $userId = Auth::id();
  $subTotal = $cartTotals['sub_total'] ?? 0;
  $totalDiscount = $cartTotals['total_discount'] ?? 0;
  $couponDiscount = $cartTotals['coupon_discounts'] ?? 0;
  $productDiscount = $cartTotals['product_discounts'] ?? 0;
  $deliveryCharges = $cartTotals['delivery_charges'] ?? 0;
  $taxAmount = $cartTotals['taxes'] ?? 0;
  $grandTotal = $cartTotals['total_cost'] ?? 0;
  $expressCharge = 99;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Checkout - {{ config('app.name') }}</title>
  <meta name="robots" content="noindex, nofollow">
  <link href="{{ asset('frontend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
  <link href="{{ asset('frontend/assets/css/checkout.css') }}" rel="stylesheet">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    body { background: #FFFDF8; }
    .checkout-page .header-wrapper { display: none; }
    .checkout-page .page-wrapper { padding-top: 0; }
    .checkout-page footer { display: none; }
    .checkout-page .back-to-top { display: none; }
  </style>
</head>
<body class="checkout-page">
  {{-- Logo Bar --}}
  <div class="checkout-logo">
    <a href="{{ route('frontend.home') }}">
      <img src="{{ asset('frontend/assets/images/logo-icon.png') }}" alt="{{ config('app.name') }}">
    </a>
  </div>
  <div class="checkout-header-title">
    Checkout
    <a href="{{ route('frontend.cart.index') }}" class="float-end me-3" style="font-size:13px;color:#C89B3C;text-decoration:none;">&larr; Back to Cart</a>
  </div>

  {{-- Toast --}}
  <div class="checkout-toast" id="checkoutToast">
    <i class="bx bx-check-circle" style="color:#28A745;font-size:18px;"></i>
    <span id="toastMessage"></span>
  </div>

  {{-- Main Content --}}
  <div class="checkout-container">
    <div class="checkout-grid">
      {{-- LEFT COLUMN --}}
      <div class="checkout-main">
        {{-- 1. LOGIN / GUEST --}}
        @guest
        <div class="checkout-section">
          <div class="checkout-section-body">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
              <div>
                <h6 style="margin:0;font-size:15px;font-weight:600;">{{ __('Already have an account?') }}</h6>
                <p style="margin:4px 0 0;font-size:13px;color:#666;">{{ __('Sign in for faster checkout') }}</p>
              </div>
              <button class="btn-gold-outline" onclick="$('#authModal').modal('show');">Sign In</button>
            </div>
            <hr style="margin:12px 0;">
            <div>
              <h6 style="font-size:14px;font-weight:600;margin-bottom:8px;">{{ __('Continue as Guest') }}</h6>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <input type="text" id="guestName" class="form-control" placeholder="Full Name *" style="border-radius:6px;border:1px solid #E5E5E5;padding:8px 12px;font-size:14px;">
                <input type="tel" id="guestPhone" class="form-control" placeholder="Phone *" style="border-radius:6px;border:1px solid #E5E5E5;padding:8px 12px;font-size:14px;">
                <input type="email" id="guestEmail" class="form-control" placeholder="Email (optional)" style="border-radius:6px;border:1px solid #E5E5E5;padding:8px 12px;font-size:14px;grid-column:1/-1;">
              </div>
            </div>
          </div>
        </div>
        @endguest

        {{-- 2. DELIVERY ADDRESS --}}
        <div class="checkout-section" id="addressSection">
          <div class="checkout-section-header" onclick="toggleSection('addressSection')">
            <h6><span class="section-icon" id="addrIcon">1</span> {{ __('Delivery Address') }}</h6>
            <span class="section-status" id="addrStatus" style="display:none;"><i class="bx bx-check-circle"></i> Done</span>
            <span class="section-edit" id="addrEdit" style="display:none;">Change</span>
          </div>
          <div class="checkout-section-body" id="addrBody">
            @auth
              @if ($shippingAddresses->count() > 0)
                @foreach ($shippingAddresses as $addr)
                <div class="address-card {{ $defaultAddress && $defaultAddress->id === $addr->id ? 'selected' : '' }}"
                     onclick="selectAddress(this, {{ $addr->id }})">
                  <input class="address-radio" type="radio" name="shipping_addr" value="{{ $addr->id }}"
                         {{ $defaultAddress && $defaultAddress->id === $addr->id ? 'checked' : '' }}>
                  <div class="address-content">
                    <div class="address-badge">{{ $addr->type ?? 'Home' }}</div>
                    <div class="address-name">{{ $addr->name }}</div>
                    <div class="address-phone">{{ $addr->phone }}</div>
                    <div class="address-detail">
                      {{ $addr->address }}, {{ $addr->city }}@if($addr->state), {{ $addr->state }}@endif @if($addr->zip) - {{ $addr->zip }}@endif
                    </div>
                    <div class="address-actions">
                      <a onclick="event.stopPropagation();editAddress({{ $addr->id }})">Edit</a>
                      <a onclick="event.stopPropagation();deleteAddress({{ $addr->id }})" style="color:#DC3545;">Remove</a>
                    </div>
                  </div>
                </div>
                @endforeach
              @else
                <p style="font-size:13px;color:#666;margin-bottom:12px;">{{ __('No saved addresses. Please add a delivery address.') }}</p>
              @endif

              <button class="btn-add-address" onclick="showAddressForm()" style="margin-top:8px;">
                <i class="bx bx-plus"></i> {{ __('Add New Address') }}
              </button>
            @else
              <p style="font-size:13px;color:#666;">{{ __('Please sign in or continue as guest to add a delivery address.') }}</p>
            @endauth
          </div>
        </div>

        {{-- 3. PIN CODE & DELIVERY --}}
        <div class="checkout-section" id="deliverySection">
          <div class="checkout-section-header" onclick="toggleSection('deliverySection')">
            <h6><span class="section-icon" id="delIcon">2</span> {{ __('Delivery Options') }}</h6>
            <span class="section-status" id="delStatus" style="display:none;"><i class="bx bx-check-circle"></i> Done</span>
          </div>
          <div class="checkout-section-body" id="delBody">
            <div class="pincode-check">
              <input type="text" id="pincodeInput" placeholder="Enter delivery PIN code" maxlength="6" inputmode="numeric">
              <button id="pincodeBtn" onclick="checkPincode()">Check</button>
            </div>
            <div id="pincodeResult" style="font-size:13px;margin-bottom:12px;"></div>

            <div class="delivery-option selected" onclick="selectDelivery(this, 'standard')">
              <label style="display:flex;align-items:center;gap:10px;cursor:pointer;width:100%;">
                <input class="delivery-radio" type="radio" name="delivery" value="standard" checked>
                <div style="flex:1;">
                  <div class="delivery-name">Standard Delivery</div>
                  <div class="delivery-time">
                    {{ $deliveryDates['standard']['start'] }} - {{ $deliveryDates['standard']['end'] }}
                    <span style="color:#666;margin-left:6px;">(3-5 Days)</span>
                  </div>
                </div>
                <div class="delivery-price free">FREE</div>
              </label>
            </div>
            <div class="delivery-option" onclick="selectDelivery(this, 'express')">
              <label style="display:flex;align-items:center;gap:10px;cursor:pointer;width:100%;">
                <input class="delivery-radio" type="radio" name="delivery" value="express">
                <div style="flex:1;">
                  <div class="delivery-name">Express Delivery</div>
                  <div class="delivery-time">
                    {{ $deliveryDates['express']['start'] }} - {{ $deliveryDates['express']['end'] }}
                    <span style="color:#666;margin-left:6px;">(1-2 Days)</span>
                  </div>
                </div>
                <div class="delivery-price">₹99</div>
              </label>
            </div>
          </div>
        </div>

        {{-- 4. COUPON --}}
        <div class="checkout-section" id="couponSection">
          <div class="checkout-section-header" onclick="toggleSection('couponSection')">
            <h6><span class="section-icon" id="coupIcon">3</span> {{ __('Coupons & Offers') }}</h6>
            <span class="section-status" id="coupStatus" style="display:none;"><i class="bx bx-check-circle"></i> Applied</span>
          </div>
          <div class="checkout-section-body" id="coupBody">
            <div class="coupon-input-group">
              <input type="text" id="couponInput" placeholder="Enter coupon code" value="{{ $sessionCoupon ?? '' }}" autocomplete="off">
              <button id="applyCouponBtn" onclick="applyCoupon()">{{ $sessionCoupon ? 'Change' : 'Apply' }}</button>
            </div>
            <div id="couponResult" style="font-size:13px;margin-top:8px;"></div>

            @if ($sessionCoupon)
            <div class="coupon-applied" id="appliedCoupon">
              <div>
                <div class="coupon-code">{{ $sessionCoupon }}</div>
                <div class="coupon-discount">- {{ PriceHelper::formatPrice($couponDiscount) }}</div>
              </div>
              <span class="coupon-remove" onclick="removeCoupon()">Remove</span>
            </div>
            @endif

            @if ($availableCoupons->count() > 0)
            <hr style="margin:12px 0;">
            <p style="font-size:13px;font-weight:600;margin-bottom:8px;">{{ __('Available Coupons') }}</p>
            <div id="availableCoupons">
              @foreach ($availableCoupons as $cp)
              @php
                $cpLabel = $cp['type'] == 1 ? $cp['value'] . '% OFF' : '₹' . $cp['value'] . ' OFF';
                $cpDesc = $cp['type'] == 1 ? $cp['value'] . '% discount on your order' : 'Flat ₹' . $cp['value'] . ' off on your order';
              @endphp
              <div class="available-coupon">
                <div class="coupon-info">
                  <div class="coupon-code">{{ $cp['code'] }}</div>
                  <div class="coupon-desc">{{ $cpLabel }} &middot; {{ $cpDesc }}</div>
                  @if ($cp['valid_until'])
                  <div class="coupon-valid">Valid till {{ $cp['valid_until'] }}</div>
                  @endif
                </div>
                <button class="btn-apply-coupon" onclick="quickApplyCoupon('{{ $cp['code'] }}')">Apply</button>
              </div>
              @endforeach
            </div>
            @endif
          </div>
        </div>

        {{-- 5. PAYMENT --}}
        <div class="checkout-section" id="paymentSection">
          <div class="checkout-section-header" onclick="toggleSection('paymentSection')">
            <h6><span class="section-icon" id="payIcon">4</span> {{ __('Payment Method') }}</h6>
            <span class="section-status" id="payStatus" style="display:none;"><i class="bx bx-check-circle"></i> Done</span>
          </div>
          <div class="checkout-section-body" id="payBody">
            <div class="payment-methods">
              {{-- UPI --}}
              <div class="payment-method-option" onclick="selectPayment(this, 'UPI')">
                <input class="pm-radio" type="radio" name="payment_mode" value="UPI">
                <span class="pm-icon"><i class="bx bxs-phone"></i></span>
                <span class="pm-name">UPI</span>
                <span class="pm-arrow"><i class="bx bx-chevron-right"></i></span>
              </div>
              <div class="payment-sub-options" id="upiOptions">
                <div class="upi-apps">
                  <div class="upi-app" onclick="selectUpiApp(this, 'PhonePe')">
                    <i class="bx bxs-phone" style="font-size:24px;color:#6C3CC9;"></i>
                    <span>PhonePe</span>
                  </div>
                  <div class="upi-app" onclick="selectUpiApp(this, 'Google Pay')">
                    <i class="bx bxs-wallet" style="font-size:24px;color:#4285F4;"></i>
                    <span>Google Pay</span>
                  </div>
                  <div class="upi-app" onclick="selectUpiApp(this, 'Paytm')">
                    <i class="bx bxs-wallet" style="font-size:24px;color:#00BAF2;"></i>
                    <span>Paytm</span>
                  </div>
                  <div class="upi-app" onclick="selectUpiApp(this, 'BHIM')">
                    <i class="bx bxs-flag" style="font-size:24px;color:#108D3E;"></i>
                    <span>BHIM</span>
                  </div>
                  <div class="upi-app" onclick="selectUpiApp(this, 'Amazon Pay')">
                    <i class="bx bxs-shopping-bag" style="font-size:24px;color:#FF9900;"></i>
                    <span>Amazon Pay</span>
                  </div>
                  <div class="upi-app" onclick="selectUpiApp(this, 'other')">
                    <i class="bx bxs-devices" style="font-size:24px;color:#666;"></i>
                    <span>Other UPI</span>
                  </div>
                </div>
                <div class="upi-input-group">
                  <input type="text" id="upiId" placeholder="Enter UPI ID (e.g. name@upi)" style="flex:1;">
                  <button onclick="setUpiId()">Verify</button>
                </div>
                <div id="upiVerified" style="font-size:12px;color:#28A745;margin-top:6px;display:none;">
                  <i class="bx bx-check-circle"></i> UPI ID verified
                </div>
              </div>

              {{-- CARDS --}}
              <div class="payment-method-option" onclick="selectPayment(this, 'Card')">
                <input class="pm-radio" type="radio" name="payment_mode" value="Card">
                <span class="pm-icon"><i class="bx bx-credit-card"></i></span>
                <span class="pm-name">Credit / Debit Card</span>
                <span class="pm-arrow"><i class="bx bx-chevron-right"></i></span>
              </div>
              <div class="payment-sub-options" id="cardOptions">
                <div class="card-row full">
                  <label class="card-label">Card Number</label>
                  <input class="card-input" type="text" id="cardNumber" placeholder="XXXX XXXX XXXX XXXX" maxlength="19" inputmode="numeric">
                </div>
                <div class="card-row">
                  <div>
                    <label class="card-label">Expiry</label>
                    <input class="card-input" type="text" id="cardExpiry" placeholder="MM/YY" maxlength="5">
                  </div>
                  <div>
                    <label class="card-label">CVV</label>
                    <input class="card-input" type="password" id="cardCvv" placeholder="***" maxlength="4" inputmode="numeric">
                  </div>
                </div>
                <div class="card-row full">
                  <label class="card-label">Name on Card</label>
                  <input class="card-input" type="text" id="cardName" placeholder="Cardholder name">
                </div>
                <label class="save-card">
                  <input type="checkbox" id="saveCard"> Save card for future
                </label>
              </div>

              {{-- NET BANKING --}}
              <div class="payment-method-option" onclick="selectPayment(this, 'NetBanking')">
                <input class="pm-radio" type="radio" name="payment_mode" value="NetBanking">
                <span class="pm-icon"><i class="bx bx-building"></i></span>
                <span class="pm-name">Net Banking</span>
                <span class="pm-arrow"><i class="bx bx-chevron-right"></i></span>
              </div>
              <div class="payment-sub-options" id="netbankingOptions">
                <div class="netbanking-grid">
                  <div class="netbanking-option" onclick="selectBank(this, 'SBI')">SBI</div>
                  <div class="netbanking-option" onclick="selectBank(this, 'HDFC')">HDFC</div>
                  <div class="netbanking-option" onclick="selectBank(this, 'ICICI')">ICICI</div>
                  <div class="netbanking-option" onclick="selectBank(this, 'Axis')">Axis</div>
                  <div class="netbanking-option" onclick="selectBank(this, 'Kotak')">Kotak</div>
                  <div class="netbanking-option" onclick="selectBank(this, 'Other')">Other Banks</div>
                </div>
              </div>

              {{-- WALLET --}}
              <div class="payment-method-option" onclick="selectPayment(this, 'Wallet')">
                <input class="pm-radio" type="radio" name="payment_mode" value="Wallet">
                <span class="pm-icon"><i class="bx bx-wallet"></i></span>
                <span class="pm-name">Wallet</span>
                <span class="pm-arrow"><i class="bx bx-chevron-right"></i></span>
              </div>
              <div class="payment-sub-options" id="walletOptions">
                <div class="wallet-grid">
                  <div class="wallet-option" onclick="selectWallet(this, 'Amazon Pay')">Amazon Pay</div>
                  <div class="wallet-option" onclick="selectWallet(this, 'Mobikwik')">Mobikwik</div>
                  <div class="wallet-option" onclick="selectWallet(this, 'Freecharge')">Freecharge</div>
                </div>
              </div>

              {{-- COD --}}
              <div class="payment-method-option" onclick="selectPayment(this, 'COD')">
                <input class="pm-radio" type="radio" name="payment_mode" value="COD" checked>
                <span class="pm-icon"><i class="bx bx-money"></i></span>
                <span class="pm-name">Cash on Delivery</span>
                <span class="pm-arrow"><i class="bx bx-chevron-right"></i></span>
              </div>
              <div class="payment-sub-options" id="codOptions">
                <div class="cod-info">
                  <i class="bx bx-info-circle"></i>
                  <span>Pay when you receive your order. <span class="cod-charge">₹30</span> additional charge applies.</span>
                </div>
                <div class="cod-info" style="margin-top:4px;">
                  <i class="bx bx-calendar"></i>
                  <span>Delivery within 3-5 Days</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- 6. ORDER REVIEW (Mobile visible) --}}
        <div class="checkout-section d-block d-lg-none">
          <div class="checkout-section-header">
            <h6><i class="bx bx-receipt"></i> {{ __('Order Review') }}</h6>
          </div>
          <div class="checkout-section-body">
            <div class="order-review">
              <table>
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($cartItems as $item)
                  @php
                    $prod = $item->product;
                    $img = $item->image ? App\Helpers\ImageHelper::getProductImage($item->image) : asset('frontend/assets/images/products/01.png');
                    $variant = $item->variant;
                  @endphp
                  <tr>
                    <td>
                      <div class="or-product">
                        <img src="{{ $img }}" alt="">
                        <div>
                          <div class="or-name">{{ $prod->name ?? 'Product' }}</div>
                          <div class="or-meta">
                            @if ($item->color) Color: {{ $item->color }} @endif
                            @if ($item->size) Size: {{ $item->size }} @endif
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>{{ $item->qty }}</td>
                    <td class="or-price">{{ PriceHelper::formatPrice($item->price) }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      {{-- RIGHT COLUMN: ORDER SUMMARY (Desktop sidebar) --}}
      <div class="checkout-sidebar">
        <div class="summary-card">
          <div class="summary-card-header">{{ __('Order Summary') }}</div>
          <div class="summary-items">
            @foreach ($cartItems as $item)
            @php
              $prod = $item->product;
              $img = $item->image ? App\Helpers\ImageHelper::getProductImage($item->image) : asset('frontend/assets/images/products/01.png');
            @endphp
            <div class="summary-item">
              <img src="{{ $img }}" alt="">
              <div class="item-info">
                <div class="item-name">{{ $prod->name ?? 'Product' }}</div>
                <div class="item-meta">Qty: {{ $item->qty }} @if($item->color) | {{ $item->color }} @endif @if($item->size) | {{ $item->size }} @endif</div>
              </div>
              <div class="item-price">{{ PriceHelper::formatPrice($item->price * $item->qty) }}</div>
            </div>
            @endforeach
          </div>
          <div class="summary-totals">
            <div class="summary-row">
              <span class="label">{{ __('Items Total') }}</span>
              <span class="value">{{ PriceHelper::formatPrice($subTotal) }}</span>
            </div>
            @if ($productDiscount > 0)
            <div class="summary-row">
              <span class="label">{{ __('Product Discount') }}</span>
              <span class="value red">-{{ PriceHelper::formatPrice($productDiscount) }}</span>
            </div>
            @endif
            <div class="summary-row">
              <span class="label">{{ __('Coupon Discount') }}</span>
              <span class="value green" id="summaryCouponDiscount">{{ $couponDiscount > 0 ? '-' . PriceHelper::formatPrice($couponDiscount) : '--' }}</span>
            </div>
            <div class="summary-row">
              <span class="label">{{ __('Shipping') }}</span>
              <span class="value green" id="summaryShipping">{{ $deliveryCharges > 0 ? PriceHelper::formatPrice($deliveryCharges) : 'FREE' }}</span>
            </div>
            <div class="summary-row">
              <span class="label">{{ __('GST') }}</span>
              <span class="value" id="summaryTax">{{ PriceHelper::formatPrice($taxAmount) }}</span>
            </div>
            <div class="summary-row total">
              <span class="label">{{ __('Total') }}</span>
              <span class="value" id="summaryTotal">{{ PriceHelper::formatPrice($grandTotal) }}</span>
            </div>

            <button class="place-order-btn" id="placeOrderBtn" onclick="placeOrder()">
              {{ __('Place Order') }}
              <div class="btn-subtitle" id="orderTotalDisplay">{{ PriceHelper::formatPrice($grandTotal) }}</div>
            </button>

            <div class="secure-badge">
              <i class="bx bx-lock-alt"></i>
              <span>{{ __('Secure Payment') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- MOBILE BOTTOM BAR --}}
  <div class="checkout-mobile-bar">
    <div class="mobile-total">
      <div class="total-label">{{ __('Total') }}</div>
      <div class="total-amount" id="mobileTotal">{{ PriceHelper::formatPrice($grandTotal) }}</div>
    </div>
    <button class="mobile-place-btn" onclick="placeOrder()">{{ __('Place Order') }}</button>
  </div>

  {{-- Payment Processing Overlay --}}
  <div class="payment-processing" id="paymentProcessing">
    <div class="pp-spinner"></div>
    <div class="pp-title">{{ __('Processing Payment') }}</div>
    <div class="pp-subtitle">{{ __('Please wait while we process your payment...') }}</div>
    <div class="pp-dont-refresh"><i class="bx bx-error-circle"></i> {{ __('Do not refresh or go back') }}</div>
  </div>

  {{-- Payment Result Overlay --}}
  <div class="payment-result" id="paymentResult">
    <div class="pr-icon success" id="resultIcon"><i class="bx bx-check"></i></div>
    <div class="pr-title" id="resultTitle">{{ __('Payment Successful') }}</div>
    <div class="pr-detail" id="resultDetail">{{ __('Your order has been placed successfully.') }}</div>
    <div class="pr-order-id" id="resultOrderId"></div>
    <div class="pr-amount" id="resultAmount"></div>
    <div>
      <button class="pr-btn primary" id="resultBtnPrimary" onclick="handleResultAction()">{{ __('Continue') }}</button>
      <button class="pr-btn outline" id="resultBtnSecondary" style="display:none;">{{ __('Try Again') }}</button>
    </div>
  </div>

  {{-- Address Form Modal --}}
  <div class="address-form-overlay" id="addressFormOverlay">
    <div class="address-form-modal" style="position:relative;">
      <button class="btn-close-modal" onclick="hideAddressForm()">&times;</button>
      <h5>{{ __('Add New Address') }}</h5>
      <form id="addressForm" onsubmit="return saveAddress(event)">
        @csrf
        <input type="hidden" name="address_id" id="editAddressId">
        <div style="margin-bottom:12px;">
          <label class="form-label">{{ __('Full Name') }}</label>
          <input type="text" name="name" class="form-control" id="addrName" required>
        </div>
        <div style="margin-bottom:12px;">
          <label class="form-label">{{ __('Phone Number') }}</label>
          <input type="tel" name="phone" class="form-control" id="addrPhone" required>
        </div>
        <div style="margin-bottom:12px;">
          <label class="form-label">{{ __('Email (optional)') }}</label>
          <input type="email" name="email" class="form-control" id="addrEmail">
        </div>
        <div style="margin-bottom:12px;">
          <label class="form-label">{{ __('Address') }}</label>
          <textarea name="address" class="form-control" id="addrAddress" rows="2" required></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
          <div>
            <label class="form-label">{{ __('City') }}</label>
            <input type="text" name="city" class="form-control" id="addrCity" required>
          </div>
          <div>
            <label class="form-label">{{ __('State') }}</label>
            <input type="text" name="state" class="form-control" id="addrState">
          </div>
          <div>
            <label class="form-label">{{ __('Pincode') }}</label>
            <input type="text" name="zip" class="form-control" id="addrZip" maxlength="6" inputmode="numeric">
          </div>
          <div>
            <label class="form-label">{{ __('Type') }}</label>
            <select name="type" class="form-select" id="addrType">
              <option value="Home">Home</option>
              <option value="Work">Work</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>
        <button type="submit" class="btn-gold" style="width:100%;">{{ __('Save Address') }}</button>
      </form>
    </div>
  </div>

  {{-- Auth Modal (for guest) --}}
  @guest
  <div class="modal fade auth-modal" id="authModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div class="w-100 text-center">
            <h4 class="fw-bold mb-0" style="color:#C89B3C;">Welcome to {{ config('app.name') }}</h4>
            <p class="text-muted mb-0 mt-1">{{ __('Sign in to continue') }}</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4 pt-2">
          <ul class="nav nav-pills nav-justified mb-4" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="loginTabBtn" data-bs-toggle="pill" data-bs-target="#loginTab" type="button" style="color:#C89B3C;">{{ __('Sign In') }}</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="registerTabBtn" data-bs-toggle="pill" data-bs-target="#registerTab" type="button">{{ __('Register') }}</button>
            </li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane fade show active" id="loginTab">
              <form id="loginForm">
                @csrf
                <div class="mb-3">
                  <label class="form-label">{{ __('Email Address') }}</label>
                  <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">{{ __('Password') }}</label>
                  <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn w-100" style="background:#C89B3C;color:#fff;">{{ __('Sign In') }}</button>
              </form>
            </div>
            <div class="tab-pane fade" id="registerTab">
              <form id="registerForm">
                @csrf
                <div class="mb-3">
                  <label class="form-label">{{ __('Name') }}</label>
                  <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">{{ __('Email') }}</label>
                  <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">{{ __('Phone') }}</label>
                  <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">{{ __('Password') }}</label>
                  <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn w-100" style="background:#C89B3C;color:#fff;">{{ __('Register') }}</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endguest

  <script src="{{ asset('frontend/assets/js/jquery.min.js') }}"></script>
  <script src="{{ asset('frontend/assets/js/bootstrap.bundle.min.js') }}"></script>
  <script>
    var csrfToken = '{{ csrf_token() }}';
    var selectedAddressId = {{ $defaultAddress ? $defaultAddress->id : 'null' }};
    var selectedPayment = 'COD';
    var selectedDelivery = '{{ $selectedDelivery }}';
    var appliedCoupon = '{{ $sessionCoupon ?? '' }}';
    var upiApp = '';
    var upiId = '';
    var selectedBank = '';
    var selectedWallet = '';

    // Toast
    function showToast(msg, type) {
      var $t = $('#checkoutToast');
      $t.removeClass('toast-error toast-success').addClass(type === 'error' ? 'toast-error' : 'toast-success');
      $('#toastMessage').text(msg);
      $t.addClass('show');
      setTimeout(function(){ $t.removeClass('show'); }, 4000);
    }

    // Toggle sections
    function toggleSection(id) {
      var $body = $('#' + id + ' .checkout-section-body');
      $body.slideToggle(200);
    }

    // Address selection
    function selectAddress(el, id) {
      $('.address-card').removeClass('selected');
      $(el).addClass('selected');
      $(el).find('.address-radio').prop('checked', true);
      selectedAddressId = id;
      $('#addrStatus').show();
    }

    // Show/hide address form
    function showAddressForm() {
      $('#editAddressId').val('');
      $('#addressForm')[0].reset();
      $('#addressFormOverlay').addClass('show');
    }
    function hideAddressForm() {
      $('#addressFormOverlay').removeClass('show');
    }

    // Save address
    function saveAddress(e) {
      e.preventDefault();
      var $btn = $(e.target).find('button[type="submit"]');
      $btn.prop('disabled', true).text('Saving...');

      $.ajax({
        url: '{{ route("frontend.checkout.address.add") }}',
        method: 'POST',
        data: $(e.target).serialize(),
        success: function(res) {
          if (res.status) {
            showToast('Address added successfully', 'success');
            hideAddressForm();
            setTimeout(function(){ location.reload(); }, 800);
          } else {
            showToast(res.message, 'error');
          }
        },
        error: function(xhr) {
          showToast(xhr.responseJSON?.message || 'Failed to save address', 'error');
        },
        complete: function() {
          $btn.prop('disabled', false).text('Save Address');
        }
      });
    }

    // Delete address
    function deleteAddress(id) {
      if (!confirm('Remove this address?')) return;
      $.ajax({
        url: '{{ url("/checkout/address") }}/' + id,
        method: 'DELETE',
        data: { _token: csrfToken },
        success: function(res) {
          if (res.status) {
            showToast('Address removed', 'success');
            setTimeout(function(){ location.reload(); }, 600);
          }
        }
      });
    }

    // PIN Code check
    function checkPincode() {
      var pincode = $('#pincodeInput').val().trim();
      if (pincode.length !== 6) {
        $('#pincodeResult').html('<span style="color:#DC3545;">Please enter a valid 6-digit PIN code</span>');
        return;
      }
      $('#pincodeBtn').prop('disabled', true).text('Checking...');
      $.ajax({
        url: '{{ route("frontend.checkout.check-pincode") }}',
        method: 'POST',
        data: { pincode: pincode, _token: csrfToken },
        success: function(res) {
          if (res.status) {
            $('#pincodeResult').html('<span style="color:#28A745;"><i class="bx bx-check-circle"></i> ' + res.message + ' (' + (res.data.city || '') + ')</span>');
            $('#delStatus').show();
          } else {
            $('#pincodeResult').html('<span style="color:#DC3545;"><i class="bx bx-error-circle"></i> ' + res.message + '</span>');
          }
        },
        error: function() {
          $('#pincodeResult').html('<span style="color:#DC3545;">Could not verify PIN code. Please try again.</span>');
        },
        complete: function() {
          $('#pincodeBtn').prop('disabled', false).text('Check');
        }
      });
    }

    // Delivery selection
    function selectDelivery(el, type) {
      $('.delivery-option').removeClass('selected');
      $(el).addClass('selected');
      $(el).find('.delivery-radio').prop('checked', true);
      selectedDelivery = type;

      $.ajax({
        url: '{{ route("frontend.checkout.set-delivery") }}',
        method: 'POST',
        data: { type: type, _token: csrfToken },
        success: function() {
          updateSummary();
        }
      });
    }

    // Coupon
    function applyCoupon() {
      var code = $('#couponInput').val().trim().toUpperCase();
      if (!code) { showToast('Please enter a coupon code', 'error'); return; }

      $('#applyCouponBtn').prop('disabled', true).text('Applying...');
      $.ajax({
        url: '{{ route("frontend.checkout.apply-coupon") }}',
        method: 'POST',
        data: { code: code, _token: csrfToken },
        success: function(res) {
          if (res.status) {
            appliedCoupon = code;
            showToast(res.message, 'success');
            updateTotals(res.data.totals);
            $('#coupStatus').show();
            $('#couponInput').val(code);
            renderAppliedCoupon(code, res.data.discount_label);
          } else {
            showToast(res.message, 'error');
          }
        },
        error: function() {
          showToast('Failed to apply coupon', 'error');
        },
        complete: function() {
          $('#applyCouponBtn').prop('disabled', false).text('Apply');
        }
      });
    }

    function quickApplyCoupon(code) {
      $('#couponInput').val(code);
      applyCoupon();
    }

    function removeCoupon() {
      $.ajax({
        url: '{{ route("frontend.checkout.remove-coupon") }}',
        method: 'POST',
        data: { _token: csrfToken },
        success: function(res) {
          if (res.status) {
            appliedCoupon = '';
            showToast('Coupon removed', 'success');
            updateTotals(res.data.totals);
            $('#coupStatus').hide();
            $('#couponInput').val('');
            $('#appliedCoupon').remove();
          }
        }
      });
    }

    function renderAppliedCoupon(code, label) {
      var html = '<div class="coupon-applied" id="appliedCoupon">' +
        '<div><div class="coupon-code">' + code + '</div><div class="coupon-discount">' + label + '</div></div>' +
        '<span class="coupon-remove" onclick="removeCoupon()">Remove</span></div>';
      $('#appliedCoupon').remove();
      $('#couponResult').after(html);
    }

    // Payment selection
    function selectPayment(el, mode) {
      $('.payment-method-option').removeClass('selected');
      $(el).addClass('selected');
      $(el).find('.pm-radio').prop('checked', true);
      selectedPayment = mode;

      $('.payment-sub-options').removeClass('show');
      var target = '';
      if (mode === 'UPI') target = '#upiOptions';
      else if (mode === 'Card') target = '#cardOptions';
      else if (mode === 'NetBanking') target = '#netbankingOptions';
      else if (mode === 'Wallet') target = '#walletOptions';
      else if (mode === 'COD') target = '#codOptions';
      if (target) $(target).addClass('show');

      $('#payStatus').show();
    }

    function selectUpiApp(el, app) {
      $('.upi-app').removeClass('selected');
      $(el).addClass('selected');
      upiApp = app;
    }

    function setUpiId() {
      var id = $('#upiId').val().trim();
      if (id && id.includes('@')) {
        upiId = id;
        $('#upiVerified').show();
      } else {
        showToast('Please enter a valid UPI ID (e.g. name@upi)', 'error');
      }
    }

    function selectBank(el, bank) {
      $('.netbanking-option').removeClass('selected');
      $(el).addClass('selected');
      selectedBank = bank;
    }

    function selectWallet(el, wallet) {
      $('.wallet-option').removeClass('selected');
      $(el).addClass('selected');
      selectedWallet = wallet;
    }

    // Update summary
    function updateSummary() {
      $.ajax({
        url: '{{ route("frontend.checkout.index") }}',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
          // handled by full page refresh
        }
      });
    }

    function updateTotals(totals) {
      if (!totals) return;
      var couponD = totals.coupon_discounts || 0;
      var shipping = totals.delivery_charges || 0;
      var tax = totals.taxes || 0;
      var total = totals.total_cost || 0;

      $('#summaryCouponDiscount').text(couponD > 0 ? '-' + formatPrice(couponD) : '--');
      $('#summaryShipping').text(shipping > 0 ? formatPrice(shipping) : 'FREE');
      $('#summaryTax').text(formatPrice(tax));
      $('#summaryTotal').text(formatPrice(total));
      $('#orderTotalDisplay').text(formatPrice(total));
      $('#mobileTotal').text(formatPrice(total));

      if ($('#summaryCouponDiscount').length) {
        // success
      }
    }

    function formatPrice(amount) {
      var symbol = '{{ session("currency_symbol", "₹") }}';
      return symbol + ' ' + parseFloat(amount).toFixed(2);
    }

    // Place order
    function placeOrder() {
      @guest
      var name = $('#guestName').val().trim();
      var phone = $('#guestPhone').val().trim();
      if (!name || !phone) {
        showToast('Please fill in your name and phone to continue', 'error');
        return;
      }
      @endguest

      if (!selectedAddressId) {
        showToast('Please select a delivery address', 'error');
        return;
      }

      $('#paymentProcessing').addClass('show');
      $('#placeOrderBtn').prop('disabled', true);

      var data = {
        _token: csrfToken,
        shipping_address_id: selectedAddressId,
        payment_mode: selectedPayment,
        @guest
        guest_name: $('#guestName').val().trim(),
        guest_phone: $('#guestPhone').val().trim(),
        guest_email: $('#guestEmail').val().trim(),
        @endguest
      };

      $.ajax({
        url: '{{ route("frontend.checkout.place-order") }}',
        method: 'POST',
        data: data,
        success: function(res) {
          if (res.status) {
            if (res.redirect) {
              $('#paymentProcessing').removeClass('show');
              window.location.href = res.redirect;
            } else {
              showResult('success', 'Payment Successful', 'Your order has been placed successfully.', res.order_id || '');
            }
          } else {
            $('#paymentProcessing').removeClass('show');
            showToast(res.message || 'Failed to place order', 'error');
            $('#placeOrderBtn').prop('disabled', false);
          }
        },
        error: function(xhr) {
          $('#paymentProcessing').removeClass('show');
          var msg = xhr.responseJSON?.message || 'Something went wrong. Please try again.';
          showToast(msg, 'error');
          $('#placeOrderBtn').prop('disabled', false);
        }
      });
    }

    // Show result overlay
    function showResult(type, title, detail, orderId) {
      var $r = $('#paymentResult');
      var $icon = $('#resultIcon');
      $icon.removeClass('success failed pending');

      if (type === 'success') {
        $icon.addClass('success').html('<i class="bx bx-check"></i>');
        $('#resultTitle').text(title);
        $('#resultDetail').text(detail);
        $('#resultBtnPrimary').text('Continue').show();
        $('#resultBtnSecondary').hide();
      } else if (type === 'failed') {
        $icon.addClass('failed').html('<i class="bx bx-x"></i>');
        $('#resultTitle').text(title);
        $('#resultDetail').text(detail);
        $('#resultBtnPrimary').text('Try Again');
        $('#resultBtnSecondary').text('Choose Another Method').show();
      }

      if (orderId) {
        $('#resultOrderId').text('Order #' + orderId).show();
      } else {
        $('#resultOrderId').hide();
      }

      $r.addClass('show');
    }

    function handleResultAction() {
      window.location.href = '{{ route("frontend.home") }}';
    }

    // Login/Register for guest
    @guest
    $(function() {
      $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
          url: '{{ route("frontend.login") }}',
          method: 'POST',
          data: $(this).serialize(),
          success: function(res) {
            window.location.reload();
          },
          error: function(xhr) {
            var msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : 'Invalid credentials';
            showToast(msg, 'error');
          }
        });
      });

      $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
          url: '{{ route("frontend.register") }}',
          method: 'POST',
          data: $(this).serialize(),
          success: function(res) {
            window.location.reload();
          },
          error: function(xhr) {
            var msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : 'Registration failed';
            showToast(msg, 'error');
          }
        });
      });
    });
    @endguest

    // Init: mark address status if already selected
    $(function() {
      if (selectedAddressId) { $('#addrStatus').show(); }
      if (selectedDelivery) { $('#delStatus').show(); }
      if (appliedCoupon) { $('#coupStatus').show(); }
      $('#payStatus').show();

      // Fix card number formatting
      $('#cardNumber').on('input', function() {
        var v = $(this).val().replace(/\D/g, '').substring(0, 16);
        v = v.replace(/(.{4})/g, '$1 ').trim();
        $(this).val(v);
      });

      $('#cardExpiry').on('input', function() {
        var v = $(this).val().replace(/\D/g, '').substring(0, 4);
        if (v.length > 2) { v = v.substring(0,2) + '/' + v.substring(2); }
        $(this).val(v);
      });
    });
  </script>
</body>
</html>
