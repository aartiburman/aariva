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
            if (!Schema::hasColumn('orders', 'offer_discounts')) {
                $table->decimal('offer_discounts', 10, 2)->default(0)->after('campaign_discounts');
            }
            if (!Schema::hasColumn('orders', 'total_discount')) {
                $table->decimal('total_discount', 10, 2)->default(0)->after('offer_discounts');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'offer_discount')) {
                $table->decimal('offer_discount', 10, 2)->default(0)->after('campaign_discount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['offer_discounts', 'total_discount']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['offer_discount']);
        });
    }
};
