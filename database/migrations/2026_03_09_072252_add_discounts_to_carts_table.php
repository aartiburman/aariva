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
        Schema::table('carts', function (Blueprint $table) {
            $table->decimal('product_discount', 10, 2)->default(0)->after('total_price');
            $table->string('coupon_code')->nullable()->after('product_discount');
            $table->decimal('coupon_discount', 10, 2)->default(0)->after('coupon_code');
            $table->decimal('campaign_discount', 10, 2)->default(0)->after('coupon_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn(['product_discount', 'coupon_code', 'coupon_discount', 'campaign_discount']);
        });
    }
};
