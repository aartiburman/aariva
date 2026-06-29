<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WhatsAppSetting;
use App\Models\WhatsAppMessage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class WhatsAppController extends Controller
{
    public function settings()
    {
        $settings = WhatsAppSetting::pluck('value', 'key')->toArray();
        return view('backend.admin.whatsapp.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $keys = [
            'api_url', 'api_token', 'phone_number_id', 'business_account_id',
            'is_active', 'order_confirmation_template', 'order_shipped_template',
            'order_delivered_template',
        ];

        foreach ($keys as $key) {
            WhatsAppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->input($key, '')]
            );
        }

        return redirect()->back()->with('success', 'WhatsApp settings updated successfully');
    }

    public function messages(Request $request)
    {
        $query = WhatsAppMessage::with(['user', 'order']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('recipient', 'LIKE', "%{$s}%")
                  ->orWhere('message', 'LIKE', "%{$s}%");
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereDate('created_at', '>=', $dates[0])
                      ->whereDate('created_at', '<=', $dates[1]);
            }
        }

        $messages = $query->latest()->paginate(30);

        if ($request->ajax()) {
            return view('backend.admin.whatsapp.partials.messages-table', compact('messages'))->render();
        }

        return view('backend.admin.whatsapp.messages', compact('messages'));
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'recipient' => 'required|string|max:20',
            'message' => 'required|string|max:2000',
        ]);

        $log = WhatsAppMessage::create([
            'recipient' => $request->recipient,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        // Attempt to send via API
        $result = $this->sendViaApi($request->recipient, $request->message);

        $log->update([
            'status' => $result['status'] ? 'sent' : 'failed',
            'message_id' => $result['message_id'] ?? null,
            'error_message' => $result['error'] ?? null,
            'sent_at' => $result['status'] ? now() : null,
            'user_id' => Auth::id(),
        ]);

        if ($result['status']) {
            return redirect()->back()->with('success', 'Test message sent successfully');
        }

        return redirect()->back()->with('error', 'Failed to send: ' . ($result['error'] ?? 'Unknown error'));
    }

    public function sendOrderNotification(Request $request, $orderId)
    {
        $request->validate([
            'template' => 'required|in:confirmation,shipped,delivered',
        ]);

        $order = Order::with('user')->findOrFail($orderId);
        $customer = $order->user;
        $phone = $customer->phone;

        if (!$phone) {
            return redirect()->back()->with('error', 'Customer has no phone number');
        }

        $templateName = WhatsAppSetting::getValue("order_{$request->template}_template", '');

        $message = match ($request->template) {
            'confirmation' => "Your order #{$order->order_reference_id} has been confirmed. Thank you for shopping with us!",
            'shipped' => "Your order #{$order->order_reference_id} has been shipped! Track it on our website.",
            'delivered' => "Your order #{$order->order_reference_id} has been delivered. Enjoy your purchase!",
            default => "Update on your order #{$order->order_reference_id}",
        };

        $log = WhatsAppMessage::create([
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'recipient' => $phone,
            'message' => $message,
            'template_name' => $templateName,
            'status' => 'pending',
        ]);

        $result = $this->sendViaApi($phone, $message, $templateName);

        $log->update([
            'status' => $result['status'] ? 'sent' : 'failed',
            'message_id' => $result['message_id'] ?? null,
            'error_message' => $result['error'] ?? null,
            'sent_at' => $result['status'] ? now() : null,
        ]);

        return redirect()->back()->with(
            $result['status'] ? 'success' : 'error',
            $result['status'] ? 'WhatsApp notification sent' : 'Failed: ' . ($result['error'] ?? 'Unknown')
        );
    }

    private function sendViaApi($recipient, $message, $templateName = '')
    {
        $isActive = WhatsAppSetting::getValue('is_active', '0');
        if ($isActive !== '1') {
            return ['status' => false, 'error' => 'WhatsApp API is not active'];
        }

        $apiUrl = WhatsAppSetting::getValue('api_url', '');
        $apiToken = WhatsAppSetting::getValue('api_token', '');
        $phoneNumberId = WhatsAppSetting::getValue('phone_number_id', '');

        if (empty($apiUrl) || empty($apiToken)) {
            return ['status' => false, 'error' => 'WhatsApp API not configured'];
        }

        // Format phone number
        $recipient = ltrim($recipient, '+');
        if (!str_starts_with($recipient, '977') && !str_starts_with($recipient, '91')) {
            $recipient = '977' . $recipient;
        }

        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $recipient,
                'type' => 'text',
                'text' => ['body' => $message],
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => rtrim($apiUrl, '/') . '/' . $phoneNumberId . '/messages',
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiToken,
                    'Content-Type: application/json',
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return ['status' => false, 'error' => $error];
            }

            $data = json_decode($response, true);

            if ($httpCode === 200 || $httpCode === 201) {
                return [
                    'status' => true,
                    'message_id' => $data['messages'][0]['id'] ?? null,
                ];
            }

            return [
                'status' => false,
                'error' => $data['error']['message'] ?? 'HTTP ' . $httpCode,
            ];
        } catch (\Exception $e) {
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }
}
