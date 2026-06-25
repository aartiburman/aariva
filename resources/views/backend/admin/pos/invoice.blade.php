<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_reference_id }}</title>
    <style>
        body { 
            font-family: 'Courier New', Courier, monospace; 
            color: #000; 
            margin: 0; 
            padding: 0; 
            background: #fff;
            font-size: 14px;
            line-height: 1.4;
        }
        .invoice-box { 
            width: 80mm; 
            margin: auto; 
            padding: 10px; 
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        
        .header { margin-bottom: 15px; }
        .header img { max-width: 150px; margin-bottom: 10px; }
        .header h2 { margin: 5px 0; font-size: 18px; }
        
        .info-table { width: 100%; margin-bottom: 10px; font-size: 12px; }
        .info-table td { padding: 2px 0; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .items-table th { border-bottom: 1px solid #000; text-align: left; padding: 5px 0; font-size: 13px; }
        .items-table td { padding: 5px 0; vertical-align: top; font-size: 12px; }
        
        .total-section { margin-top: 10px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .total-row.grand-total { font-weight: bold; font-size: 16px; border-top: 1px solid #000; padding-top: 5px; margin-top: 5px; }
        
        .savings { color: #000; font-weight: bold; font-style: italic; font-size: 12px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; }
        .qr-code { text-align: center; margin-top: 15px; }
        .qr-code img { width: 120px; height: 120px; }
        
        @media print { 
            .no-print { display: none; } 
            .invoice-box { width: 100%; padding: 0; }
            @page { 
                size: 80mm auto; 
                margin: 0; 
            }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 10px; background: #f4f4f4;">
        <button onclick="window.print()" style="padding: 8px 15px; cursor: pointer; background: #007bff; color: #fff; border: none; border-radius: 4px;">Print Receipt (80mm)</button>
        <button onclick="window.close()" style="padding: 8px 15px; cursor: pointer; background: #6c757d; color: #fff; border: none; border-radius: 4px; margin-left: 5px;">Close</button>
    </div>

    <div class="invoice-box">
        <div class="header text-center">
            @php
                $logoValue = $websiteLogo->value;
            @endphp
            <img src="{{ $logoValue }}">
            <h2>{{ $websiteName }}</h2>
            <div style="font-size: 12px;">
                {!! nl2br(e($address->value ?? '')) !!}<br>
                Tel: {{ $contactPhone->value ?? '' }}
            </div>
        </div>

        <div class="divider"></div>

        <table class="info-table">
            <tr>
                <td>Receipt #:</td>
                <td class="text-right">{{ $order->order_reference_id }}</td>
            </tr>
            <tr>
                <td>Date:</td>
                <td class="text-right">{{ $order->created_at->format('d/m/Y h:i A') }}</td>
            </tr>
            <!-- <tr>
                <td>Customer:</td>
                <td class="text-right">{{ $order->user->name ?? 'Walk-in' }}</td>
            </tr> -->
            <tr>
                <td>Payment:</td>
                <td class="text-right">{{ ucfirst($order->payment_mode) }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        <div style="font-weight: bold;">{{ $item->product->name }}</div>
                        @if($item->variant)
                            <small>
                                {{ $item->variant->color }} 
                                @php 
                                    $size = $item->variant->size ?? '';
                                    // Clean up blank array or null
                                    if ($size === '[]' || empty($size)) {
                                        $size = '';
                                    } else {
                                        // If it's a JSON string, try to decode it
                                        if (str_starts_with($size, '[') && str_ends_with($size, ']')) {
                                            $decoded = json_decode($size, true);
                                            $size = !empty($decoded) ? (is_array($decoded) ? implode(', ', $decoded) : $decoded) : '';
                                        }
                                    }
                                @endphp
                                @if(!empty($size))
                                    | {{ $size }}
                                @endif
                            </small>
                        @endif
                        <br>
                        <small>{{ $item->quantity }} x {{ $currencySymbol }} {{ number_format($item->actual_price ?? $item->price, 2) }}</small>
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format(($item->total_actual_price ?: ($item->actual_price ?: $item->price) * $item->quantity), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>{{ $currencySymbol }} {{ number_format($order->sub_total, 2) }}</span>
            </div>
            @if($order->total_discount > 0)
            <div class="total-row savings">
                <span>You Saved:</span>
                <span>{{ $currencySymbol }} {{ number_format($order->total_discount, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>{{ $currencySymbol }} {{ number_format($order->total_cost, 2) }}</span>
            </div>
        </div>

        @if($order->payment_mode == 'online')
        <div class="qr-code">
            <p><strong>Scan to Pay</strong></p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(url('/pay/' . $order->order_reference_id)) }}" alt="QR Code">
        </div>
        @endif

        <div class="footer">
            <p><strong>THANK YOU FOR SHOPPING!</strong></p>
            <p>Please visit us again.</p>
        </div>
    </div>
</body>
</html>
