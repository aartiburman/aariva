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
            $table->dropColumn(['vendor_warranty', 'vendor_payment', 'vendor_return', 'vendor_delivery']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->tinyInteger('vendor_warranty')->default(0);
            $table->tinyInteger('vendor_payment')->default(0);
            $table->tinyInteger('vendor_return')->default(0);
            $table->tinyInteger('vendor_delivery')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['vendor_warranty', 'vendor_payment', 'vendor_return', 'vendor_delivery']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('vendor_warranty')->nullable();
            $table->string('vendor_payment')->nullable();
            $table->string('vendor_return')->nullable();
            $table->string('vendor_delivery')->nullable();
        });
    }
};
