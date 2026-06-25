<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .error-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; max-width: 500px; width: 90%; }
        .icon-circle { width: 100px; height: 100px; background-color: #f8d7da; color: #dc3545; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 50px; margin: 0 auto 20px; }
        .btn-primary { background-color: #6c5ce7; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 600; }
        .btn-primary:hover { background-color: #5b4cc4; }
        .btn-outline { border: 2px solid #6c5ce7; color: #6c5ce7; padding: 10px 30px; border-radius: 10px; font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-circle">
            <i class="fas fa-times"></i>
        </div>
        <h2 class="fw-bold mb-3">Payment Failed</h2>
        @if(isset($error))
            <p class="text-danger fw-semibold">{{ $error }}</p>
        @endif
        <p class="text-muted">We're sorry, but your transaction could not be completed. This might be due to insufficient funds, an expired card, or a temporary issue with the payment gateway.</p>
        
        <div class="mt-4">
            <p class="small text-muted mb-4">Please try again or choose a different payment method.</p>
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="/" class="btn btn-primary">Try Again</a>
                <a href="/" class="btn btn-outline">Back to Cart</a>
            </div>
        </div>
    </div>
</body>
</html>
