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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('product_discounts', 10, 2)->default(0)->after('total_cost');
            $table->decimal('coupon_discounts', 10, 2)->default(0)->after('product_discounts');
            $table->decimal('campaign_discounts', 10, 2)->default(0)->after('coupon_discounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['product_discounts', 'coupon_discounts', 'campaign_discounts']);
        });
    }
};
