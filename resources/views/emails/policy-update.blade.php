<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Update Notification - {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f2f5; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f0f2f5; padding: 40px 0; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(108, 92, 231, 0.1); }
        .header { background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%); padding: 40px 30px; text-align: center; color: #ffffff; }
        .header img { max-width: 150px; margin-bottom: 15px; }
        .content { padding: 40px; line-height: 1.8; color: #444; }
        .policy-info { background-color: #f8f9fa; border-radius: 12px; padding: 25px; margin: 25px 0; border: 1px solid #edf2f7; }
        .policy-title { font-size: 18px; font-weight: 700; color: #6c5ce7; margin-bottom: 10px; }
        .version-badge { display: inline-block; padding: 4px 12px; background-color: #e0e0ff; color: #6c5ce7; border-radius: 50px; font-size: 12px; font-weight: 600; margin-bottom: 15px; }
        .button { display: inline-block; padding: 14px 30px; background-color: #6c5ce7; color: #ffffff !important; text-decoration: none; border-radius: 10px; font-weight: 600; margin-top: 20px; text-align: center; }
        .footer { background-color: #1a1a1a; padding: 35px 30px; text-align: center; font-size: 13px; color: #ffffff; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main" align="center" cellpadding="0" cellspacing="0">
            <tr>
                <td class="header">
                    <img src="{{ $logo_url }}" alt="{{ config('app.name') }}">
                    <h1 style="margin:0; font-size: 24px;">Important Policy Update</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h2>Hello {{ $vendor_name }},</h2>
                    <p>We are writing to inform you that the vendor policy has been updated. Please review the changes to ensure your store remains compliant with our latest guidelines.</p>
                    
                    <div class="policy-info">
                        <div class="version-badge">Version {{ $version }}</div>
                        <div class="policy-title">{{ $policy_title }}</div>
                        <p style="margin: 0; font-size: 14px; color: #718096;">Effective Date: {{ date('F d, Y') }}</p>
                    </div>

                    <p>You can view the full policy details by logging into your vendor dashboard or clicking the button below.</p>
                    
                    <div style="text-align: center;">
                        <a href="{{ $policy_url }}" class="button">View Updated Policy</a>
                    </div>

                    <p style="margin-top: 30px; font-size: 14px; color: #718096;">Continued use of our platform constitutes acceptance of the updated terms. If you have any questions, please reach out to our support team.</p>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p>Working together for a better marketplace.</p>
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </td>
            </tr>
        </table>
    </div>
</body>
</html>