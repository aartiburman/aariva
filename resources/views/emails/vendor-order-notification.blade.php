<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Received - {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f0f2f5; padding: 40px 0; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(108, 92, 231, 0.1); }
        .header { background: linear-gradient(135deg, #00b894 0%, #55efc4 100%); padding: 40px 30px; text-align: center; color: #ffffff; }
        .header img { max-width: 150px; margin-bottom: 15px; }
        .content { padding: 40px; line-height: 1.8; color: #444; }
        .order-info { background-color: #f8f9fa; border-radius: 12px; padding: 25px; margin: 25px 0; border: 1px solid #edf2f7; }
        .item-card { display: flex; align-items: center; padding: 15px; background: #fff; border-radius: 10px; margin-top: 15px; border: 1px solid #eee; }
        .item-image { width: 70px; height: 70px; border-radius: 8px; object-fit: cover; margin-right: 15px; border: 1px solid #ddd; }
        .item-details h4 { margin: 0; font-size: 15px; color: #1a1a1a; }
        .item-details p { margin: 4px 0 0 0; font-size: 13px; color: #718096; }
        .button { display: inline-block; padding: 14px 30px; background-color: #00b894; color: #ffffff !important; text-decoration: none; border-radius: 10px; font-weight: 600; margin-top: 30px; text-align: center; }
        .footer { background-color: #1a1a1a; padding: 35px 30px; text-align: center; font-size: 13px; color: #ffffff; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main" align="center" cellpadding="0" cellspacing="0">
            <tr>
                <td class="header">
                    <img src="{{ $logo_url }}" alt="{{ config('app.name') }}">
                    <h1 style="margin:0; font-size: 24px;">New Order Alert!</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h2>Hello {{ $vendor_name }},</h2>
                    <p>Great news! You have received a new order for your product(s). Please process the order as soon as possible.</p>
                    
                    <div class="order-info">
                        <p style="margin:0; font-weight: 600; color: #1a1a1a;">Order Details</p>
                        <p style="margin:5px 0; font-size: 14px;"><strong>Order ID:</strong> #{{ $order_id }}</p>
                        <p style="margin:5px 0; font-size: 14px;"><strong>Customer:</strong> {{ $customer_name }}</p>
                        
                        <div class="item-card">
                            <img src="{{ $product_image }}" class="item-image">
                            <div class="item-details">
                                <h4>{{ $product_name }}</h4>
                                <p>Qty: {{ $quantity }} | Earnings: {{ $earnings }}</p>
                            </div>
                        </div>
                    </div>

                    <p>Log in to your vendor dashboard to view full details and update the shipment status.</p>
                    
                    <div style="text-align: center;">
                        <a href="{{ $dashboard_url }}" class="button">Go to Dashboard</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p>Manage your store efficiently with {{ config('app.name') }}.</p>
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </td>
            </tr>
        </table>
    </div>
</body>
</html>