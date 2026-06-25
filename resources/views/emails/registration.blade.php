<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Credentials</title>
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
            padding: 50px 40px;
            line-height: 1.8;
            font-size: 16px;
            color: #444444;
        }
        .content h2 {
            color: #6c5ce7;
            margin-top: 0;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 25px;
        }
        .credentials-box {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            margin: 30px 0;
        }
        .credential-item {
            margin-bottom: 15px;
        }
        .credential-item:last-child {
            margin-bottom: 0;
        }
        .label {
            font-weight: 700;
            color: #1a1a1a;
            display: block;
            margin-bottom: 4px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .value {
            color: #444444;
            font-size: 16px;
            word-break: break-all;
        }
        .value a {
            color: #6c5ce7;
            text-decoration: none;
            font-weight: 600;
        }
        .footer {
            background-color: #1a1a1a;
            padding: 35px 30px;
            text-align: center;
            font-size: 13px;
            color: #ffffff;
            border-top: 1px solid #edf2f7;
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
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(108, 92, 231, 0.3);
            text-align: center;
        }
        @media only screen and (max-width: 600px) {
            .main {
                width: 95% !important;
                border-radius: 12px;
            }
            .content {
                padding: 35px 25px;
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
                    <h1>{{ $website_name ?? config('app.name') }}</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h2>Welcome to join as a vendor!</h2>
                    <p>Dear <strong>{{ $owner_name }}</strong>,</p>
                    <p>Congratulations! Your vendor account for <strong>{{ $store_name }}</strong> has been successfully created. We are excited to have you as part of our seller community.</p>
                    
                    <div class="credentials-box">
                        <div class="credential-item">
                            <span class="label">Login URL</span>
                            <span class="value"><a href="{{ $login_url }}">{{ $login_url }}</a></span>
                        </div>
                        <div class="credential-item">
                            <span class="label">Email Address</span>
                            <span class="value">{{ $email }}</span>
                        </div>
                        <div class="credential-item">
                            <span class="label">Password</span>
                            <span class="value">{{ $password }}</span>
                        </div>
                    </div>
                    
                    <p>You can now log in to your vendor dashboard to complete your profile, list your products, and manage your store operations.</p>
                    
                    <div style="text-align: center;">
                        <a href="{{ $login_url }}" class="button">Log In to Your Store</a>
                    </div>
                    
                    <p style="margin-top: 30px;">If you have any questions or need assistance setting up your store, our support team is always here to help.</p>
                    <p>Best Regards,<br>The {{ $website_name ?? config('app.name') }} Team</p>
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