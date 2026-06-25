<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_reference_id }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f0f4f8;
            line-height: 1.4;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 0;
            border-radius: 0;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        /* Wavy Header */
        .invoice-header-bg {
            background-color: #004792;
            color: white;
            padding: 40px 40px 80px 40px;
            position: relative;
        }

        .invoice-header-bg::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background: white;
            clip-path: ellipse(60% 50% at 50% 100%);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .header-content h1 {
            font-size: 48px;
            font-weight: 800;
            margin: 0;
            letter-spacing: 2px;
        }

        .invoice-no {
            font-size: 18px;
            font-weight: 700;
        }

        /* Address Section */
        .address-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            padding: 20px 40px;
            margin-top: -20px;
        }

        .address-box h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
        }

        .address-box p {
            font-size: 14px;
            color: #666;
            margin: 2px 0;
        }

        .text-right { text-align: right !important; }

        .date-section {
            padding: 0 40px 20px 40px;
            font-size: 16px;
            font-weight: 500;
            color: #666;
        }

        /* Table Section */
        .table-container {
            padding: 0 40px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd;
        }

        .items-table th {
            background-color: #004792;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 14px;
            border: 1px solid #004792;
        }

        .items-table td {
            padding: 10px;
            border: 1px solid #ddd;
            font-size: 14px;
            color: #444;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }

        /* Summary Section */
        .summary-container {
            display: flex;
            justify-content: flex-end;
            padding: 20px 40px;
        }

        .summary-box {
            background-color: #004792;
            color: white;
            display: flex;
            justify-content: space-between;
            width: 300px;
            padding: 10px 20px;
            font-weight: 700;
        }

        /* Footer Section */
        .footer-section {
            padding: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .footer-left h4 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .note-line {
            border-bottom: 1px solid #ddd;
            height: 25px;
            width: 200px;
        }

        .payment-info {
            margin-top: 30px;
        }

        .payment-info p {
            margin: 2px 0;
            font-size: 14px;
            font-weight: 600;
        }

        .payment-info span {
            font-weight: 400;
            color: #666;
            margin-left: 10px;
        }

        .thank-you {
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
            font-size: 36px;
            font-weight: 700;
            color: #004792;
        }

        .action-buttons {
            margin-bottom: 20px;
            text-align: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background-color: #004792;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
        }

        @media print {
            body { padding: 0; background-color: white; }
            .invoice-container { box-shadow: none; border: none; width: 100%; max-width: none; }
            .action-buttons { display: none; }
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
        @php
            $vendor = $order->vendor;
            if (!$vendor && $order->items->first()) {
                $vendor = $order->items->first()->vendor;
            }
            $contact = \App\Models\ContactDetail::where('status', 1)->first();
            
            $shopName = config('app.name', 'ECOMMERCE');
            $shopAddress = $contact ? $contact->address : '';
            $shopEmail = $contact ? $contact->email : '';
            $shopPhone = $contact ? $contact->phone : '';

            if ($vendor) {
                $shopName = $vendor->store_name ?: $vendor->business_name ?: $shopName;
                $shopAddress = $vendor->address ?: $shopAddress;
                $shopEmail = $vendor->email ?: $shopEmail;
                $shopPhone = $vendor->phone ?: $shopPhone;
            }
        @endphp

        <div class="invoice-header-bg">
            <div class="header-content">
                <h1>INVOICE</h1>
                <div class="invoice-no">NO: #{{ $order->order_reference_id }}</div>
            </div>
        </div>

        <div class="address-section">
            <div class="address-box">
                <h3>Bill To:</h3>
                <p><strong>{{ $order->user->name }}</strong></p>
                <p>{{ $order->user->phone }}</p>
                <p>{{ $order->user->address }}</p>
            </div>
            <div class="address-box text-right">
                <h3>From:</h3>
                <p><strong>{{ $shopName }}</strong></p>
                <p>{{ $shopPhone }}</p>
                <p>{{ $shopAddress }}</p>
            </div>
        </div>

        <div class="date-section">
            Date: {{ $order->created_at->format('d F Y') }}
        </div>

        <div class="table-container">
            <table class="items-table">
                <thead>
                    <tr text="center">
                        <th width="50%">Description</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $item->product->name }}</div>
                            @if($item->variant)
                            <div style="font-size: 12px; color: #777;">
                                Color: {{ $item->variant->color }} | SKU: {{ $item->variant->sku }}
                            </div>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-center">{{ number_format($item->price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="summary-container">
            <div class="summary-box">
                <span>Sub Total</span>
                <span>{{ number_format($order->total_cost, 2) }}</span>
            </div>
        </div>

        <div class="footer-section">
            <div class="footer-left">
                <h4>Note:</h4>
                <div class="note-line"></div>
                <div class="note-line"></div>
                
                <div class="payment-info">
                    <h4>Payment Information:</h4>
                    <p>Bank: <span>Name Bank</span></p>
                    <!-- <p>No Bank: <span>123-456-7890</span></p> -->
                    <p>Email: <span>{{ $shopEmail }}</span></p>
                </div>
            </div>
            <div class="thank-you">
                Thank You!
            </div>
        </div>
    </div>

</body>
</html>
