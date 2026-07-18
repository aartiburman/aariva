<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\NotificationSetting;

$setting = NotificationSetting::first() ?? new NotificationSetting();
$setting->fill([
    'firebase_api_key' => 'AIzaSyAo-SIuKQ5a6ZIlOjQV7JrpdaAzsApMDTU',
    'firebase_auth_domain' => 'aariva-11652.firebaseapp.com',
    'firebase_project_id' => 'aariva-11652',
    'firebase_storage_bucket' => 'aariva-11652.firebasestorage.app',
    'firebase_messaging_sender_id' => '517682638610',
    'fcm_sender_id' => '517682638610',
    'firebase_app_id' => '1:517682638610:web:396059362d4a7f74a30dcb',
    'fcm_vapid_key' => 'BEsQVJTjEy6442551hVj5okt-jYDZRuiJvoLxs7ph2iqnAWyKLg9XNGUhrKDUE0UwI8LfxKb7HOyd_mwQ7_vuFo',
    'measurementId' => 'G-5BSNZ5ZNN6',
    'status' => 1,
]);
$setting->save();
echo "DATABASE_UPDATED_WITH_CORRECT_FIREBASE_CONFIG" . PHP_EOL;
