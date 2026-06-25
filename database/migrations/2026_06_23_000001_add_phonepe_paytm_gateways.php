<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PaymentGateway;

return new class extends Migration
{
    public function up(): void
    {
        $gateways = [
            [
                'name' => 'PhonePe',
                'slug' => 'phonepe',
                'status' => false,
                'mode' => 'sandbox',
                'sandbox_base_url' => 'https://api-preprod.phonepe.com/apis/pg-sandbox',
                'live_base_url' => 'https://api.phonepe.com/apis/hermes',
                'app_id' => '1',
            ],
            [
                'name' => 'Paytm',
                'slug' => 'paytm',
                'status' => false,
                'mode' => 'sandbox',
                'sandbox_base_url' => 'https://securegw-stage.paytm.in',
                'live_base_url' => 'https://securegw.paytm.in',
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentGateway::updateOrCreate(
                ['slug' => $gateway['slug']],
                $gateway
            );
        }
    }

    public function down(): void
    {
        PaymentGateway::whereIn('slug', ['phonepe', 'paytm'])->delete();
    }
};
