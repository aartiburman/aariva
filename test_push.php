<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Helpers\NotificationHelper;

$token = 'efobNQDKHuFFVktb5rgIL5:APA91bGtVi6pT9cB1LPGuXMLC8IgUGRNimpHsqL8bAeIPpXw2tS12JBptFAlES2bqbSLHfGWVO5LpsXjfRNJbLg5P9uO7_DtZn769-BMj85ZApjcjCimzls';
$data = [
    'title' => 'Direct Token Test',
    'message' => 'This is a test notification from the assistant.',
    'type' => 'test'
];

try {
    $res = NotificationHelper::sendPushByToken($token, $data);
    echo 'RESULT: ' . ($res ? 'SUCCESS' : 'FAILED') . PHP_EOL;
} catch (\Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
