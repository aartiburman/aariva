<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_reference_id }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            margin: 0;
            padding: 40px;
            background-color: #f8fafc;
            line-height: 1.5;
        }

        .invoice-container {
            max-width: 850px;
            margin: auto;
            background: #fff;
            padding: 50px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f1f5f9;
        }

        .company-info img {
            max-height: 50px;
            margin-bottom: 15px;
        }

        .company-info h2 {
            margin: 0;
            color: #0f172a;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .company-details {
            font-size: 13px;
            color: #64748b;
            margin-top: 5px;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-meta h1 {
            margin: 0;
            color: #2563eb;
            font-size: 28px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .meta-item {
            margin-top: 8px;
            font-size: 14px;
        }

        .meta-label {
            color: #64748b;
            font-weight: 500;
        }

        .meta-value {
            color: #0f172a;
            font-weight: 600;
        }

        .address-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .address-box h3 {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
        }

        .address-box p {
            margin: 0;
            font-size: 14px;
            color: #334155;
            line-height: 1.6;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-all;
        }

        .address-box strong {
            color: #0f172a;
            font-size: 16px;
            display: block;
            margin-bottom: 4px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-all;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background-color: #f8fafc;
            padding: 12px 15px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }

        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            vertical-align: top;
        }

        .product-name {
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .product-variant {
            font-size: 12px;
            color: #64748b;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .summary-section {
            display: flex;
            justify-content: flex-end;
        }

        .summary-table {
            width: 300px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }

        .summary-row.total {
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px solid #f1f5f9;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .summary-label {
            color: #64748b;
            font-weight: 500;
        }

        .summary-value {
            color: #0f172a;
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-warning { background-color: #fef9c3; color: #854d0e; }
        .badge-danger { background-color: #fee2e2; color: #b91c1c; }

        .footer {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }

        .action-buttons {
            margin-bottom: 20px;
            text-align: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn:hover { background-color: #1d4ed8; }

        @media print {
            body { padding: 0; background-color: white; }
            .invoice-container { box-shadow: none; border: none; padding: 20px; width: 100%; max-width: none; }
            .action-buttons { display: none; }
            .badge { border: 1px solid #e2e8f0; }
        }
    </style>
</head>
<body>

    <div class="action-buttons">
        <button onclick="window.print()" class="btn">
            Print Invoice
        </button>
    </div>

    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-info">
                <img src="{{ asset('backend/assets/images/logo.png') }}" alt="Logo">
                <!-- <h2>{{ config('app.name', 'ECOMMERCE') }}</h2> -->
                <div class="company-details">
                    @php $contact = \App\Models\ContactDetail::where('status', 1)->first(); @endphp
                    @if($contact)
                        {{ $contact->address }}<br>
                        {{ $contact->city }}, {{ $contact->state }} {{ $contact->postal_code }}<br>
                        Email: {{ $contact->email }} | Phone: {{ $contact->phone }}
                    @endif
                </div>
            </div>
            <div class="invoice-meta">
                <h1>Invoice</h1>
                <div class="meta-item">
                    <span class="meta-label">Reference:</span>
                    <span class="meta-value">#{{ $order->order_reference_id }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Date:</span>
                    <span class="meta-value">{{ $order->created_at->format('M d, Y') }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Status:</span>
                    <span class="badge {{ $order->payment_status == 1 ? 'badge-success' : 'badge-warning' }}">
                        {{ $order->payment_status == 1 ? 'Paid' : 'Unpaid' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="address-section">
            <div class="address-box">
                <h3>Billed To</h3>
                <strong>{{ $order->user->name }}</strong>
                <p>
                    {{ $order->user->email }}<br>
                    {{ $order->user->phone }}
                </p>
            </div>
            <div class="address-box">
                <h3>Shipping Details</h3>
                @if($order->shippingAddress)
                    <strong>{{ $order->shippingAddress->name }}</strong>
                    <p>
                        @if($order->shippingAddress->email)
                            {{ $order->shippingAddress->email }}<br>
                        @endif
                        @if($order->shippingAddress->phone)
                            {{ $order->shippingAddress->phone }}<br>
                        @endif
                        {{ $order->shippingAddress->address }}<br>
                        {{ $order->shippingAddress->city->name ?? $order->shippingAddress->city }}, {{ $order->shippingAddress->state->name ?? $order->shippingAddress->state }} - {{ $order->shippingAddress->zip }}<br>
                        {{ $order->shippingAddress->country->name ?? $order->shippingAddress->country }}
                    </p>
                @else
                    <p>No shipping address provided.</p>
                @endif
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="50%">Item Description</th>
                    <th class="text-center">Price</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        <div class="product-name">{{ $item->product->name }}</div>
                        @if($item->variant)
                        <div class="product-variant">
                            Color: {{ $item->variant->color }} | SKU: {{ $item->variant->sku }}
                        </div>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->price, 2) }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-section">
            <div class="summary-table">
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value">{{ number_format($order->sub_total, 2) }}</span>
                </div>
                @if($order->product_discounts > 0)
                <div class="summary-row">
                    <span class="summary-label">Product Discounts</span>
                    <span class="summary-value">-{{ number_format($order->product_discounts, 2) }}</span>
                </div>
                @endif
                @if($order->offer_discounts > 0)
                <div class="summary-row">
                    <span class="summary-label">Offer Discount</span>
                    <span class="summary-value">-{{ number_format($order->offer_discounts, 2) }}</span>
                </div>
                @endif
                @if($order->coupon_discounts > 0)
                <div class="summary-row">
                    <span class="summary-label">Coupon Discount</span>
                    <span class="summary-value">-{{ number_format($order->coupon_discounts, 2) }}</span>
                </div>
                @endif
                @if($order->campaign_discounts > 0)
                <div class="summary-row">
                    <span class="summary-label">Campaign Discount</span>
                    <span class="summary-value">-{{ number_format($order->campaign_discounts, 2) }}</span>
                </div>
                @endif
                @if($order->delivery_charges > 0)
                <div class="summary-row">
                    <span class="summary-label">Shipping</span>
                    <span class="summary-value">{{ number_format($order->delivery_charges, 2) }}</span>
                </div>
                @endif
                @if($order->taxes > 0)
                <div class="summary-row">
                    <span class="summary-label">Taxes</span>
                    <span class="summary-value">{{ number_format($order->taxes, 2) }}</span>
                </div>
                @endif
                <div class="summary-row total">
                    <span>Total Amount</span>
                    <span>{{ number_format($order->total_cost, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
