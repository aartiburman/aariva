<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;
use App\Models\Notification;
use App\Models\User;

use App\Models\NotificationSetting;

class NotificationController extends Controller
{
    /**
     * Serve dynamic Firebase Service Worker
     */
    public function firebase_sw()
    {
        $setting = NotificationSetting::first();
        
        $config = [
            'apiKey' => $setting->firebase_api_key ?? '',
            'authDomain' => $setting->firebase_auth_domain ?? '',
            'projectId' => $setting->firebase_project_id ?? '',
            'storageBucket' => $setting->firebase_storage_bucket ?? '',
            'messagingSenderId' => $setting->firebase_messaging_sender_id ?? '',
            'appId' => $setting->firebase_app_id ?? ''
        ];

        $content = "
            importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js');
            importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js');

            firebase.initializeApp(" . json_encode($config) . ");

            const messaging = firebase.messaging();

            messaging.onBackgroundMessage(function(payload) {
                console.log('[firebase-messaging-sw.js] Received background message ', payload);
                const notificationTitle = payload.notification.title;
                const notificationOptions = {
                    body: payload.notification.body,
                    icon: payload.notification.icon || '/backend/assets/images/logo-sm.png',
                    data: payload.data || {}
                };

                self.registration.showNotification(notificationTitle, notificationOptions);
            });

            self.addEventListener('notificationclick', function(event) {
                event.notification.close();
                const url = event.notification.data.url || '/';
                event.waitUntil(
                    clients.openWindow(url)
                );
            });
        ";

        return response($content)->header('Content-Type', 'application/javascript');
    }

    /**
     * Display a listing of notifications.
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest('updated_at')->paginate(20)->withQueryString();
        
        // Return different view based on role if needed, but for now we use the common one
        // or ensure the layout handles different sidebars correctly.
        return view('backend.notifications.index', compact('notifications'));
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);
        
        if (!$notification) {
            return back()->with('error', 'Notification not found.');
        }

        $notification->markAsRead();

        if (Auth::user()->role == 1) {
            if (isset($notification->data['url'])) {
                return redirect($notification->data['url']);
            }
            return redirect()->route('vendors.list');
        }

        if (isset($notification->data['url'])) {
            return redirect($notification->data['url']);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Remove the specified notification from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);
        if (!$notification) {
            return back()->with('error', 'Notification not found.');
        }
        $notification->delete();

        return back()->with('success', 'Notification deleted.');
    }

    /**
     * Send a test notification to the authenticated user.
     */
    public function sendTestNotification()
    {
        NotificationHelper::send(Auth::user(), [
            'title' => 'Test Notification',
            'message' => 'This is a test notification to verify the system is working correctly.',
            'type' => 'system',
            'url' => route('notifications.index'),
            'icon' => 'solar:bell-bing-bold-duotone',
            'priority' => 'high'
        ]);

        return back()->with('success', 'Test notification sent successfully!');
    }

    /**
     * Send a manual push notification from the web interface.
     */
    public function sendManualPush(Request $request)
    {
        $request->validate([
            'user_id' => 'required', // User ID, 'all', 'admins', 'vendors', 'customers'
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
            'type'    => 'nullable|string',
            'url'     => 'nullable|string',
            'icon'    => 'nullable|string',
        ]);

        $target = $request->user_id;
        $data = $request->only(['title', 'message', 'type', 'url', 'icon']);

        try {
            if ($target === 'all') {
                $users = User::all();
            } elseif ($target === 'admins') {
                $users = User::where('role', '1')->get();
            } elseif ($target === 'vendors') {
                $users = User::where('role', '2')->get();
            } elseif ($target === 'customers') {
                $users = User::where('role', '3')->get();
            } else {
                $users = User::find($target);
            }

            if (!$users) {
                return back()->with('error', 'Target user(s) not found.');
            }

            NotificationHelper::send($users, $data);

            return back()->with('success', 'Push notification sent successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send notification: ' . $e->getMessage());
        }
    }

    /**
     * Update Device Token for the authenticated user via AJAX.
     */
    public function updatePlayerId(Request $request)
    {
        $request->validate([
            'token' => 'nullable|string',
            'player_id' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $token = $request->token ?? $request->player_id;
        if (empty($token)) {
            return response()->json([
                'status' => false,
                'message' => 'Token or player_id is required'
            ], 422);
        }
        $type = $request->type ?? 'web';

        $updated = NotificationHelper::updateDeviceToken($user, $token, $type);

        return response()->json([
            'status' => $updated,
            'message' => $updated ? 'Device token updated successfully' : 'Failed to update token',
            'token' => $token
        ]);
    }

    public function poll()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['count' => 0, 'notifications' => []]);
        }

        $unreadCount = $user->unreadNotifications->count();
        $notifications = $user->notifications()->whereNull('read_at')->latest()->take(10)->get()->map(function ($n) {
            $data = $n->data;
            $priority = $data['priority'] ?? 'low';
            $color = $priority === 'critical' ? 'danger' : ($priority === 'medium' ? 'warning' : 'info');
            return [
                'id' => $n->id,
                'title' => $data['title'] ?? 'Notification',
                'message' => $data['message'] ?? '',
                'icon' => $data['icon'] ?? 'solar:bell-linear',
                'color' => $color,
                'time' => $n->created_at->diffForHumans(),
                'url' => route('notifications.markAsRead', $n->id),
            ];
        });

        return response()->json([
            'count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }
}
