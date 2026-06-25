<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\WebPushNotification;
use App\Models\NotificationSetting;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    /**
     * Send notification to user/users
     */
    public static function send($users, array $data)
    {
        if (empty($users)) {
            Log::info('NotificationHelper: No users provided');
            return;
        }

        // If numeric ID passed
        if (is_numeric($users)) {
            $users = User::find($users);
            if (!$users) {
                Log::warning('NotificationHelper: User ID ' . $users . ' not found');
                return;
            }
        }

        // Filter notification settings
        if ($users instanceof \Illuminate\Database\Eloquent\Model) {
            if (!self::shouldSendNotification($users, $data['type'] ?? 'system')) {
                Log::info('NotificationHelper: Filtered out user ' . $users->id . ' for type ' . ($data['type'] ?? 'system'));
                return;
            }
        } elseif ($users instanceof \Illuminate\Support\Collection) {
            $beforeCount = $users->count();
            $users = $users->filter(function ($user) use ($data) {
                return self::shouldSendNotification($user, $data['type'] ?? 'system');
            });

            if ($users->isEmpty()) {
                Log::info('NotificationHelper: All users filtered out from collection (before count: ' . $beforeCount . ')');
                return;
            }
        }

        // Standard data
        $notificationData = [
            'title'      => $data['title'] ?? 'New Notification',
            'message'    => $data['message'] ?? '',
            'type'       => $data['type'] ?? 'system',
            'url'        => $data['url'] ?? '#',
            'icon'       => $data['icon'] ?? 'solar:bell-outline',
            'priority'   => $data['priority'] ?? 'medium',
            'created_at' => now()->toDateTimeString(),
        ];

        try {
            /**
             * 1. Database Notification
             */
            if ($users instanceof \Illuminate\Database\Eloquent\Model) {
                $users->notify(new WebPushNotification($notificationData));
            } else {
                Notification::send($users, new WebPushNotification($notificationData));
            }
            Log::info('NotificationHelper: Database notification sent successfully');
        } catch (\Exception $e) {
            Log::error('NotificationHelper: Database notification failed: ' . $e->getMessage());
        }

        /**
         * 2. Firebase Push Notification
         */
        self::sendPush($users, $notificationData);
    }

    /**
     * Check user notification permission
     */
    protected static function shouldSendNotification($user, $type)
    {
        // Critical notifications should always be sent
        if (in_array($type, ['orders', 'system', 'helpdesk'])) {
            return true;
        }

        switch ($type) {
            case 'promotions':  
                return (bool) ($user->is_promotional_email_active ?? true);

            case 'newsletter':
                return (bool) ($user->is_newsletter_active ?? true);

            default:
                return true;
        }
    }

    /**
     * Send Push
     */
    protected static function sendPush($users, $data)
    {
        $setting = NotificationSetting::first();

        // Always attempt push if setting exists, or use default status check
        // If status is 0, we still might want to log or check tokens
        if (!$setting) {
            Log::warning('Notification setting record missing in DB');
            return;
        }

        $usersCollection = ($users instanceof \Illuminate\Database\Eloquent\Collection || $users instanceof \Illuminate\Support\Collection)
            ? $users
            : collect([$users]);

        self::sendFirebasePush($usersCollection, $data, $setting);
    }

    /**
     * Get Firebase Service Account
     */
    protected static function getServiceAccount($setting = null)
    {
        /**
         * Priority 1 → DB JSON
         */
        if ($setting && !empty($setting->firebase_service_account)) {
            $decoded = json_decode($setting->firebase_service_account, true);

            if (json_last_error() === JSON_ERROR_NONE && !empty($decoded)) {
                return $decoded;
            }
        }

        /**
         * Priority 2 → Local File
         */
        $paths = [
            base_path('resources/credential/firebase_auth.json'),
            storage_path('app/firebase/firebase-service-account.json'),
            storage_path('app/firebase/firebase_auth.json'),
        ];

        foreach ($paths as $filePath) {
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $decoded = json_decode($content, true);

                if (json_last_error() === JSON_ERROR_NONE && !empty($decoded)) {
                    return $decoded;
                }
            }
        }

        Log::error('Firebase service account not found');

        return null;
    }

    /**
     * Main Firebase Push Sender
     */
    protected static function sendFirebasePush($users, $data, $setting = null)
    {
        $serviceAccount = self::getServiceAccount($setting);

        if (!$serviceAccount) {
            Log::error('Firebase config missing');
            return;
        }

        try {
            $factory = (new Factory)
                ->withServiceAccount($serviceAccount);

            $messaging = $factory->createMessaging();

            foreach ($users as $user) {
                // Ensure we use the latest token from the database for each user
                if ($user instanceof \Illuminate\Database\Eloquent\Model) {
                    $user->refresh();
                }
                
                try {
                    $token = $user->fcm_token ?? $user->device_token ?? null;

                    if (empty($token)) {
                        Log::info("User {$user->id} has no FCM/Device token");
                        continue;
                    }

                    $message = CloudMessage::fromArray([
                        'token' => $token,
                        'notification' => [
                            'title' => (string) ($data['title'] ?? 'Notification'),
                            'body'  => (string) ($data['message'] ?? ''),
                        ],
                        'data' => [
                            'type' => (string) ($data['type'] ?? 'system'),
                            'url'  => (string) ($data['url'] ?? '#'),
                            'icon' => (string) ($data['icon'] ?? 'solar:bell-outline'),
                        ],
                    ]);

                    $messaging->send($message);

                    Log::info("FCM sent successfully to User ID {$user->id} using token: " . substr($token, 0, 10) . '...');
                } catch (\Exception $e) {
                    Log::error(
                        "FCM Send Error for User {$user->id}: " . $e->getMessage()
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error(
                'Firebase Initialization Error: ' . $e->getMessage()
            );
        }
    }

    /**
     * Direct Push By Token
     */
    public static function sendPushByToken($token, array $data)
    {
        if (empty($token)) {
            throw new \Exception('FCM token is empty');
        }

        $setting = NotificationSetting::first();

        $serviceAccount = self::getServiceAccount($setting);

        if (!$serviceAccount) {
            throw new \Exception('Firebase service account missing');
        }

        try {
            $factory = (new Factory)
                ->withServiceAccount($serviceAccount);

            $messaging = $factory->createMessaging();

            $message = CloudMessage::fromArray([
                'token' => $token,
                'notification' => [
                    'title' => (string) ($data['title'] ?? 'Notification'),
                    'body'  => (string) ($data['message'] ?? ''),
                ],
                'data' => [
                    'type' => (string) ($data['type'] ?? 'system'),
                    'url'  => (string) ($data['url'] ?? '#'),
                    'icon' => (string) ($data['icon'] ?? 'solar:bell-outline'),
                ],
            ]);

            $messaging->send($message);

            Log::info('Direct FCM push sent successfully');

            return true;
        } catch (\Exception $e) {
            Log::error(
                'Direct Firebase Error: ' . $e->getMessage()
            );

            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Notify Admins
     */
    public static function notifyAdmins(array $data)
    {
        $admins = User::where('role', '1')->get();

        self::send($admins, $data);
    }

    /**
     * Notify Vendor
     */
    public static function notifyVendor($vendorId, array $data)
    {
        $vendor = User::find($vendorId);

        if ($vendor) {
            self::send($vendor, $data);
        }
    }

    /**
     * Notify Customer
     */
    public static function notifyCustomer($customerId, array $data)
    {
        $customer = User::find($customerId);

        if ($customer) {
            self::send($customer, $data);
        }
    }

    /**
     * Store/Update Device Token for a User
     */
    public static function updateDeviceToken($user, $token, $type = 'web')
    {
        if (!$user || !$token) {
            return false;
        }

        // Handle numeric user ID
        if (is_numeric($user)) {
            $user = User::find($user);
            if (!$user) {
                return false;
            }
        }

        // Update user
        $user->device_token = $token;
        $user->device_type = $type;
        $user->save();

        // Optional: Send a verification push
        try {
            self::sendPushByToken($token, [
                'title' => 'Login Successful',
                'message' => 'Your device token has been registered via NotificationHelper.',
                'type' => 'system'
            ]);
        } catch (\Exception $e) {
            Log::warning('FCM Verification notification failed via helper: ' . $e->getMessage());
        }

        return true;
    }
}