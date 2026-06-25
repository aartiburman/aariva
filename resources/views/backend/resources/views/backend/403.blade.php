<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 Forbidden</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-box {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 0 30px rgba(0,0,0,0.05);
        }
        .error-code {
            font-size: 100px;
            font-weight: 700;
            color: #dc3545;
        }
        .error-text {
            font-size: 22px;
            margin-bottom: 10px;
        }
        .error-desc {
            color: #6c757d;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<div class="error-page">
    <div class="error-box col-md-6 col-lg-4">
        <div class="error-code">403</div>
        <div class="error-text">Access Forbidden</div>
        <p class="error-desc">
            Sorry, you don’t have permission to access this page.
        </p>

        <a href="{{route('login')}}" class="btn btn-primary me-2">Go Home</a>
        <a href="javascript:history.back()" class="btn btn-outline-secondary">Go Back</a>
    </div>
</div>

</body>
</html>
