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
        // 1. Rename the coupons table to offers
        Schema::rename('coupons', 'offers');

        // 2. Rename coupon_id columns in related tables
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('coupon_id', 'offer_id');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->renameColumn('coupon_id', 'offer_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('coupon_id', 'offer_id');
            $table->renameColumn('coupon_discounts', 'offer_discounts');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->renameColumn('coupon_discount', 'offer_discount');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->renameColumn('coupon_code', 'offer_code');
            $table->renameColumn('coupon_discount', 'offer_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('offers', 'coupons');

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('offer_id', 'coupon_id');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->renameColumn('offer_id', 'coupon_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('offer_id', 'coupon_id');
            $table->renameColumn('offer_discounts', 'coupon_discounts');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->renameColumn('offer_discount', 'coupon_discount');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->renameColumn('offer_code', 'coupon_code');
            $table->renameColumn('offer_discount', 'coupon_discount');
        });
    }
};
