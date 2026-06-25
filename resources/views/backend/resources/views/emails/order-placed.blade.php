<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Your Order - {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f0f2f5; padding: 40px 0; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(108, 92, 231, 0.1); }
        .header { background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%); padding: 40px 30px; text-align: center; color: #ffffff; }
        .header img { max-width: 150px; margin-bottom: 15px; }
        .content { padding: 40px; line-height: 1.8; color: #444; }
        .order-summary { background-color: #f8f9fa; border-radius: 12px; padding: 25px; margin: 25px 0; border: 1px solid #edf2f7; }
        .item-row { display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee; }
        .item-row:last-child { border-bottom: none; }
        .item-image { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; margin-right: 15px; border: 1px solid #ddd; }
        .item-details { flex-grow: 1; }
        .item-name { font-weight: 600; color: #1a1a1a; margin: 0; }
        .item-meta { font-size: 13px; color: #718096; margin: 2px 0; }
        .totals { margin-top: 20px; border-top: 2px solid #edf2f7; padding-top: 15px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .grand-total { font-size: 18px; font-weight: 700; color: #6c5ce7; margin-top: 10px; }
        .button { display: inline-block; padding: 14px 30px; background-color: #6c5ce7; color: #ffffff !important; text-decoration: none; border-radius: 10px; font-weight: 600; margin-top: 30px; text-align: center; }
        .footer { background-color: #1a1a1a; padding: 35px 30px; text-align: center; font-size: 13px; color: #ffffff; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main" align="center" cellpadding="0" cellspacing="0">
            <tr>
                <td class="header">
                    <img src="{{ $logo_url ?? asset('backend/assets/images/logo.png') }}" alt="{{ config('app.name') }}">
                    <h1 style="margin:0; font-size: 24px;">Order Confirmed!</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h2>Hello {{ $customer_name }},</h2>
                    <p>Thank you for shopping with us! Your order <strong>#{{ $order_id }}</strong> has been placed successfully and is being processed.</p>
                    
                    <div class="order-summary">
                        <h3 style="margin-top:0; font-size: 16px; color: #1a1a1a;">Order Items</h3>
                        @foreach($items as $item)
                        <div class="item-row">
                            <img src="{{ $item['image'] }}" class="item-image">
                            <div class="item-details">
                                <p class="item-name">{{ $item['name'] }}</p>
                                <p class="item-meta">Qty: {{ $item['qty'] }} | Price: {{ $item['price'] }}</p>
                            </div>
                        </div>
                        @endforeach

                        <div class="totals">
                            <div class="total-row"><span>Subtotal:</span> <span>{{ $sub_total }}</span></div>
                            <div class="total-row"><span>Delivery:</span> <span>{{ $delivery_charges }}</span></div>
                            @if($discount > 0)
                            <div class="total-row" style="color: #2ecc71;"><span>Discount:</span> <span>-{{ $discount }}</span></div>
                            @endif
                            <div class="total-row grand-total"><span>Total Amount:</span> <span>{{ $total_cost }}</span></div>
                        </div>
                    </div>

                    <p>We'll notify you as soon as your items are shipped. You can track your order status in your dashboard.</p>
                    
                    <div style="text-align: center;">
                        <a href="{{ $order_url }}" class="button">Track Your Order</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p><strong>Need help?</strong> Contact our support team at any time.</p>
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </td>
            </tr>
        </table>
    </div>
</body>
</html>