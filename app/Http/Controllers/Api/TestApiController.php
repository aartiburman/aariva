<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\EmailHelper;
use Illuminate\Support\Facades\Validator;

class TestApiController extends Controller
{
    /**
     * Test email sending functionality
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function test_mail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $to = $request->email ?? 'support@aariva.com';
        $subject = 'Brevo API Test Mail';
        $message = '<h1>Brevo Integration Test</h1>
                    <p>This is a test email to verify that the Brevo SMTP configuration is working correctly on your website.</p>
                    <p><strong>Status:</strong> Configuration Applied Successfully.</p>
                    <p><strong>Sender:</strong> support@aariva.com</p>';
        
        $result = EmailHelper::sendWithReason($to, $subject, $message);
        
        if ($result['success']) {
            return response()->json([
                'status' => true,
                'message' => 'Test email sent successfully to ' . $to,
                'sender' => 'support@aariva.com'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send test email.',
                'error' => $result['error'] ?? 'Unknown error occurred.',
                'suggestion' => 'Please check your Brevo SMTP relay status and API key in .env'
            ], 500);
        }
    }
}
