<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\NotificationSetting;

class NotificationApiController extends Controller
{
    /**
     * Send a notification via API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send_notification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required', // Can be single ID, array of IDs, or 'all_admins', 'all_vendors', 'all_customers'
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
            'type'    => 'nullable|string', // orders, promotions, helpdesk, system
            'url'     => 'nullable|string',
            'icon'    => 'nullable|string',
            'priority'=> 'nullable|in:critical,medium,low'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $userIds = $request->user_id;
        $data = $request->only(['title', 'message', 'type', 'url', 'icon', 'priority']);

        try {
            if ($userIds === 'all_admins') {
                NotificationHelper::notifyAdmins($data);
            } elseif ($userIds === 'all_vendors') {
                $vendors = User::where('role', '2')->get();
                NotificationHelper::send($vendors, $data);
            } elseif ($userIds === 'all_customers') {
                $customers = User::where('role', '3')->get();
                NotificationHelper::send($customers, $data);
            } else {
                // Single ID or array of IDs
                NotificationHelper::send($userIds, $data);
            }

            return response()->json([
                'status' => true,
                'message' => 'Notification(s) sent successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification history for a user
     */
    public function get_notifications(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::find($request->user_id);
        $notifications = $user->notifications()->latest()->paginate(20);

        return response()->json([
            'status' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Mark notification as read
     */
    public function mark_as_read(Request $request)
    {
        $request->validate([
            'notification_id' => 'required',
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::find($request->user_id);
        $notification = $user->notifications()->where('id', $request->notification_id)->first();

        if ($notification) {
            $notification->markAsRead();
            return response()->json([
                'status' => true,
                'message' => 'Notification marked as read'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Notification not found'
        ], 404);
    }

    /**
     * Quick test for API push notifications
     */
    public function test_push_notification(Request $request)
    {
        $userId = $request->user_id;
        
        if (!$userId) {
            return response()->json([
                'status' => false,
                'message' => 'Please provide a user_id as a query parameter (e.g. ?user_id=1)'
            ], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        NotificationHelper::send($user, [
            'title' => 'API Test Push',
            'message' => 'This is a test notification from the API route.',
            'type' => 'test',
            'url' => '#',
            'icon' => 'solar:bolt-bold-duotone'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Test push notification sent successfully to user ' . $user->name
        ]);
    }

    /**
     * Test a specific device token
     */
    public function test_device_token_notification(Request $request)
    {
        $userId = $request->user_id;
        if (!$userId) {
            return response()->json([
                'status' => false,
                'message' => 'user_id is required. Example: ?user_id=1'
            ], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $token = $user->device_token;
        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Device token not found for this user'
            ], 404);
        }

        try {
            NotificationHelper::sendPushByToken($token, [
                'title' => 'Test User Notification',
                'message' => 'Hello ' . $user->name . ', if you see this, your token is working!',
                'type' => 'test'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Notification sent to user ' . $user->name . ' using token: ' . substr($token, 0, 10) . '...'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register a device token for a user
     */
    public function register_device_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'device_token' => 'required|string',
            'device_type' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = User::find($request->user_id);
        NotificationHelper::updateDeviceToken($user, $request->device_token, $request->device_type ?? 'api');

        return response()->json([
            'status' => true,
            'message' => 'Device token registered successfully'
        ]);
    }

    /**
     * Get the FCM configuration for the frontend to generate a real device token
     */
    public function get_fcm_config()
    {
        $setting = NotificationSetting::first();
        if (!$setting) {
            return response()->json([
                'status' => false,
                'message' => 'Notification settings not found in database.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'firebase_config' => [
                'apiKey' => $setting->firebase_api_key,
                'authDomain' => $setting->firebase_auth_domain,
                'projectId' => $setting->firebase_project_id,
                'storageBucket' => $setting->firebase_storage_bucket,
                'messagingSenderId' => $setting->firebase_messaging_sender_id,
                'appId' => $setting->firebase_app_id,
                'measurementId' => $setting->measurementId,
            ],
            'vapidKey' => $setting->fcm_vapid_key ?? $setting->firebase_messaging_sender_id,
            'instructions' => 'Use this config in your frontend (JavaScript/Flutter/Swift) to initialize Firebase and call getToken() to receive an actual device token.'
        ]);
    }

    /**
     * Generate or register a device token and immediately send a test notification
     */
    public function generate_test_token(Request $request)
    {
        $userId = $request->user_id;
        $actualToken = $request->token; // Accept actual token if provided

        if (!$userId) {
            return response()->json([
                'status' => false,
                'message' => 'user_id is required.'
            ], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Use provided actual token or generate a mock one
        $token = $actualToken ?? ('TEST_TOKEN_' . bin2hex(random_bytes(16)) . '_' . time());
        
        NotificationHelper::updateDeviceToken($user, $token, $request->device_type ?? 'web');

        // Immediately send push notification using settings from NotificationSetting table
        try {
            NotificationHelper::sendPushByToken($token, [
                'title' => 'Test Notification',
                'message' => 'This test notification was triggered via generate_test_token using database settings.',
                'type' => 'test'
            ]);
            $pushStatus = 'Push sent successfully';
        } catch (\Exception $e) {
            $pushStatus = 'Push failed: ' . $e->getMessage();
        }

        return response()->json([
            'status' => true,
            'message' => 'Token saved and push notification attempted.',
            'user_id' => $user->id,
            'device_token' => $token,
            'device_type' => $user->device_type,
            'push_status' => $pushStatus
        ]);
    }

    /**
     * Get a user's device token
     */
    public function get_user_token(Request $request)
    {
        $userId = $request->user_id;
        if (!$userId) {
            return response()->json([
                'status' => false,
                'message' => 'user_id is required. Example: ?user_id=1'
            ], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'user_id' => $user->id,
            'name' => $user->name,
            'device_token' => $user->device_token,
            'device_type' => $user->device_type
        ]);
    }

    /**
     * Send Push Notification directly using a device token (FCM/OneSignal)
     */
    public function send_push_by_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token'   => 'required|string',
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
            'type'    => 'nullable|string',
            'url'     => 'nullable|string',
            'icon'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $token = $request->token;
        $data = $request->only(['title', 'message', 'type', 'url', 'icon']);

        try {
            $success = NotificationHelper::sendPushByToken($token, $data);

            return response()->json([
                'status' => true,
                'message' => 'Push notification request sent successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('API Send Push Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
