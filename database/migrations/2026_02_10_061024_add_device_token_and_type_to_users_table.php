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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'device_token')) {
                $table->string('device_token')->nullable()->after('onesignal_player_id');
            }
            if (!Schema::hasColumn('users', 'device_type')) {
                $table->string('device_type')->nullable()->after('device_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['device_token', 'device_type']);
        });
    }
};
