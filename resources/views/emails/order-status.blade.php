<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Order Status Update' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f0f2f5;
            padding: 40px 0;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            color: #1a1a1a;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(108, 92, 231, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
            padding: 40px 30px;
            text-align: center;
            border-bottom: 4px solid #4834d4;
        }
        .header img {
            max-width: 180px;
            height: auto;
            margin-bottom: 15px;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .content {
            padding: 40px;
            line-height: 1.8;
            font-size: 16px;
            color: #444444;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            background-color: #6c5ce7;
            color: #ffffff;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .order-info {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            border: 1px solid #edf2f7;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #edf2f7;
        }
        .product-details {
            padding-left: 15px;
        }
        .product-name {
            font-weight: 600;
            color: #1a1a1a;
            margin: 0;
        }
        .product-meta {
            font-size: 14px;
            color: #718096;
            margin: 4px 0 0 0;
        }
        .footer {
            background-color: #1a1a1a;
            padding: 35px 30px;
            text-align: center;
            font-size: 13px;
            color: #ffffff;
            border-top: 1px solid #edf2f7;
        }
        .footer strong {
            color: #a29bfe;
        }
        .footer-strip {
            height: 6px;
            background: linear-gradient(90deg, #6c5ce7 0%, #a29bfe 100%);
            width: 100%;
        }
        .button {
            display: inline-block;
            padding: 14px 30px;
            background-color: #6c5ce7;
            color: #ffffff;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            margin-top: 30px;
            box-shadow: 0 4px 12px rgba(108, 92, 231, 0.3);
            text-align: center;
        }
        @media only screen and (max-width: 600px) {
            .main {
                width: 95% !important;
            }
            .content {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main" align="center">
            <tr>
                <td class="header">
                    <img src="{{ $logo_url }}" alt="{{ $website_name ?? config('app.name') }}">
                </td>
            </tr>
            <tr>
                <td class="content">
                    <div class="status-badge">{{ $status_text }}</div>
                    <h2>Hello {{ $customer_name }},</h2>
                    <p>Good news! The status of your order item has been updated. Here are the details of the update:</p>
                    
                    <div class="order-info">
                        <table width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="80" valign="top">
                                    <img src="{{ $product_image }}" class="product-image" alt="{{ $product_name }}">
                                </td>
                                <td class="product-details" valign="top">
                                    <p class="product-name">{{ $product_name }}</p>
                                    <p class="product-meta">Order ID: #{{ $order_id }}</p>
                                    <p class="product-meta">Quantity: {{ $quantity }}</p>
                                    <p class="product-meta">Price: {{ $price }}</p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <p>Your item is now <strong>{{ strtolower($status_text) }}</strong>. We'll keep you updated as it moves through the next stages.</p>
                    
                    <div style="text-align: center;">
                        <a href="{{ $order_url }}" class="button">Track Your Order</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <div style="margin-bottom: 15px;">
                        <strong>Need help?</strong> Contact our support team at any time.
                    </div>
                    &copy; {{ date('Y') }} {{ $website_name ?? config('app.name') }}. All rights reserved.<br>
                    <span style="display: inline-block; margin-top: 10px; font-size: 11px; opacity: 0.8;">
                        This is an automated notification from our secure server.
                    </span>
                </td>
            </tr>
            <tr>
                <td class="footer-strip"></td>
            </tr>
        </table>
    </div>
</body>
</html>