<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .success-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; max-width: 500px; width: 90%; }
        .icon-circle { width: 100px; height: 100px; background-color: #d4edda; color: #28a745; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 50px; margin: 0 auto 20px; }
        .order-ref { font-weight: bold; color: #6c5ce7; font-size: 1.2rem; margin: 15px 0; padding: 10px; background: #f0edff; border-radius: 10px; display: inline-block; }
        .btn-primary { background-color: #6c5ce7; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 600; }
        .btn-primary:hover { background-color: #5b4cc4; }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="icon-circle">
            <i class="fas fa-check"></i>
        </div>
        <h2 class="fw-bold mb-3">Payment Successful!</h2>
        @if(isset($message))
            <p class="text-success fw-semibold">{{ $message }}</p>
        @endif
        <p class="text-muted">Thank you for your purchase. Your payment has been verified and your order is now being processed.</p>
        
        @if(isset($orderReference))
            <div class="order-ref">Order #{{ $orderReference }}</div>
        @endif

        <div class="mt-4">
            <p class="small text-muted mb-4">You will receive an email confirmation shortly.</p>
            <a href="/" class="btn btn-primary">Return to Homepage</a>
        </div>
    </div>
</body>
</html>
