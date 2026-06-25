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
        Schema::table('product_sizes', function (Blueprint $table) {
            if (!Schema::hasColumn('product_sizes', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
            }
            if (!Schema::hasColumn('product_sizes', 'name_ne')) {
                $table->string('name_ne')->nullable()->after('name_ar');
            }
        });

        Schema::table('shipping_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('shipping_addresses', 'city_ar')) {
                $table->string('city_ar')->nullable();
            }
            if (!Schema::hasColumn('shipping_addresses', 'city_ne')) {
                $table->string('city_ne')->nullable();
            }
            if (!Schema::hasColumn('shipping_addresses', 'state_ar')) {
                $table->string('state_ar')->nullable();
            }
            if (!Schema::hasColumn('shipping_addresses', 'state_ne')) {
                $table->string('state_ne')->nullable();
            }
            if (!Schema::hasColumn('shipping_addresses', 'country_ar')) {
                $table->string('country_ar')->nullable();
            }
            if (!Schema::hasColumn('shipping_addresses', 'country_ne')) {
                $table->string('country_ne')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_sizes', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_ne']);
        });

        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->dropColumn(['city_ar', 'city_ne', 'state_ar', 'state_ne', 'country_ar', 'country_ne']);
        });
    }
};
