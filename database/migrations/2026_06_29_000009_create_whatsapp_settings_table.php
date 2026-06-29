<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('whatsapp_settings')) {
            Schema::create('whatsapp_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });

            // Default settings
            DB::table('whatsapp_settings')->insert([
                ['key' => 'api_url', 'value' => ''],
                ['key' => 'api_token', 'value' => ''],
                ['key' => 'phone_number_id', 'value' => ''],
                ['key' => 'business_account_id', 'value' => ''],
                ['key' => 'is_active', 'value' => '0'],
                ['key' => 'order_confirmation_template', 'value' => ''],
                ['key' => 'order_shipped_template', 'value' => ''],
                ['key' => 'order_delivered_template', 'value' => ''],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_settings');
    }
};
