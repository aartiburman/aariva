<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .payment-container { max-width: 500px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .order-summary { margin-bottom: 30px; }
        .payment-methods .btn { width: 100%; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="payment-container">
            <h2 class="text-center mb-4">Complete Your Payment</h2>
            
            <div class="order-summary">
                <h4>Order Summary</h4>
                <p><strong>Order ID:</strong> {{ $order->order_reference_id }}</p>
                <p><strong>Total Amount:</strong> {{ \App\Helpers\GeneralHelper::get_setting('currency_symbol') ?? 'INR' }}{{ number_format($order->total_cost, 2) }}</p>
            </div>

            <div class="payment-methods">
                <h4>Select Payment Method</h4>
                <button class="btn btn-success">Pay with PhonePe</button>
                <button class="btn btn-dark">Pay with Stripe</button>
            </div>
        </div>
    </div>
</body>
</html>
