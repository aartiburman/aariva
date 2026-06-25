<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notification_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('notification_settings', 'fcm_server_key')) {
                $table->string('fcm_server_key')->nullable();
            }
            if (!Schema::hasColumn('notification_settings', 'fcm_sender_id')) {
                $table->string('fcm_sender_id')->nullable();
            }
            if (!Schema::hasColumn('notification_settings', 'firebase_api_key')) {
                $table->string('firebase_api_key')->nullable();
            }
            if (!Schema::hasColumn('notification_settings', 'firebase_auth_domain')) {
                $table->string('firebase_auth_domain')->nullable();
            }
            if (!Schema::hasColumn('notification_settings', 'firebase_storage_bucket')) {
                $table->string('firebase_storage_bucket')->nullable();
            }
            if (!Schema::hasColumn('notification_settings', 'fcm_vapid_key')) {
                $table->string('fcm_vapid_key')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('notification_settings', function (Blueprint $table) {
            $table->dropColumn([
                'fcm_server_key',
                'fcm_sender_id',
                'firebase_api_key',
                'firebase_auth_domain',
                'firebase_storage_bucket',
                'fcm_vapid_key'
            ]);
        });
    }
};
