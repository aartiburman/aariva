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
            if (!Schema::hasColumn('notification_settings', 'measurementId')) {
                $table->string('measurementId')->nullable();
            }
            if (!Schema::hasColumn('notification_settings', 'firebase_service_account')) {
                $table->text('firebase_service_account')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_settings', function (Blueprint $table) {
            $table->dropColumn(['measurementId', 'firebase_service_account']);
        });
    }
};
