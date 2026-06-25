<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_gateways', 'live_public_key')) {
                $table->string('live_public_key')->nullable();
            }
            if (!Schema::hasColumn('payment_gateways', 'live_secret_key')) {
                $table->string('live_secret_key')->nullable();
            }
            if (!Schema::hasColumn('payment_gateways', 'test_public_key')) {
                $table->string('test_public_key')->nullable();
            }
            if (!Schema::hasColumn('payment_gateways', 'test_secret_key')) {
                $table->string('test_secret_key')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_gateways', function (Blueprint $table) {
            $columns = ['live_public_key', 'live_secret_key', 'test_public_key', 'test_secret_key'];
            $existing = array_filter($columns, fn($col) => Schema::hasColumn('payment_gateways', $col));
            if ($existing) {
                $table->dropColumn($existing);
            }
        });
    }
};
