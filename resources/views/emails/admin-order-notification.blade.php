<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Marketplace Order - {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f0f2f5; padding: 40px 0; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(108, 92, 231, 0.1); }
        .header { background: linear-gradient(135deg, #4834d4 0%, #686de0 100%); padding: 40px 30px; text-align: center; color: #ffffff; }
        .header img { max-width: 150px; margin-bottom: 15px; }
        .content { padding: 40px; line-height: 1.8; color: #444; }
        .order-info { background-color: #f8f9fa; border-radius: 12px; padding: 25px; margin: 25px 0; border: 1px solid #edf2f7; }
        .item-list { margin-top: 15px; }
        .item-row { border-bottom: 1px solid #eee; padding: 10px 0; }
        .item-row:last-child { border-bottom: none; }
        .button { display: inline-block; padding: 14px 30px; background-color: #4834d4; color: #ffffff !important; text-decoration: none; border-radius: 10px; font-weight: 600; margin-top: 30px; text-align: center; }
        .footer { background-color: #1a1a1a; padding: 35px 30px; text-align: center; font-size: 13px; color: #ffffff; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main" align="center" cellpadding="0" cellspacing="0">
            <tr>
                <td class="header">
                    <img src="{{ $logo_url }}" alt="{{ config('app.name') }}">
                    <h1 style="margin:0; font-size: 24px;">Marketplace Order Alert</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h2>Hello Admin,</h2>
                    <p>A new order has been placed on the marketplace. Below are the summary details:</p>
                    
                    <div class="order-info">
                        <p style="margin:0; font-weight: 600; color: #1a1a1a;">Summary</p>
                        <p style="margin:5px 0; font-size: 14px;"><strong>Order ID:</strong> #{{ $order_id }}</p>
                        <p style="margin:5px 0; font-size: 14px;"><strong>Customer:</strong> {{ $customer_name }}</p>
                        <p style="margin:5px 0; font-size: 14px;"><strong>Total Cost:</strong> {{ $total_cost }}</p>
                        <p style="margin:5px 0; font-size: 14px;"><strong>Payment Mode:</strong> {{ $payment_mode }}</p>
                    </div>

                    <p>Log in to the admin panel to manage orders and track vendor fulfillment.</p>
                    
                    <div style="text-align: center;">
                        <a href="{{ $admin_url }}" class="button">Go to Admin Panel</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p>{{ config('app.name') }} Admin Portal</p>
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
