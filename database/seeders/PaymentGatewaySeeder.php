<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            ['name' => 'Cash on Delivery', 'slug' => 'cod', 'status' => true, 'mode' => 'live'],
            ['name' => 'Card Payment', 'slug' => 'card', 'status' => true, 'mode' => 'live'],
            ['name' => 'PhonePe', 'slug' => 'phonepe', 'status' => false, 'mode' => 'sandbox'],
            ['name' => 'Paytm', 'slug' => 'paytm', 'status' => false, 'mode' => 'sandbox'],
        ];

        foreach ($gateways as $gateway) {
            \App\Models\PaymentGateway::updateOrCreate(
                ['slug' => $gateway['slug']],
                $gateway
            );
        }

        \App\Models\PaymentGateway::whereNotIn('slug', ['cod', 'card', 'phonepe', 'paytm'])->update(['status' => false]);
    }
}
