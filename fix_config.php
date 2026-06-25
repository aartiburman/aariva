<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\NotificationSetting;

$setting = NotificationSetting::first() ?? new NotificationSetting();
$setting->fill([
    'firebase_api_key' => 'AIzaSyCBozqKSO6IqmmHVlRvTVQYtQV7RIgGpUY',
    'firebase_auth_domain' => 'nepoora-auth.firebaseapp.com',
    'firebase_project_id' => 'nepoora-auth',
    'firebase_storage_bucket' => 'nepoora-auth.firebasestorage.app',
    'firebase_messaging_sender_id' => '288333381789',
    'fcm_sender_id' => '288333381789',
    'firebase_app_id' => '1:288333381789:web:e8d02fd0f0f899cb729474',
    'fcm_vapid_key' => 'BP8qy7CPNkYGkQ4l-Fgb-AfNMFh6EOU3x-A-biLz7C6gk20506EcJg_ET9tUVwC_I56RIUwKIIRHJ8ik', // Set if you have one
    'measurementId' => 'G-W0MZC761Q3',
    'status' => 1,
]);
$setting->save();
echo "DATABASE_UPDATED_WITH_CORRECT_FIREBASE_CONFIG" . PHP_EOL;
