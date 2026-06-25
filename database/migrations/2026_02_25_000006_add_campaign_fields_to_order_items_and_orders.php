<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id')->nullable()->after('variant_id');
            $table->decimal('campaign_discount', 12, 2)->default(0)->after('discount'); // per-unit campaign discount
            $table->foreign('campaign_id')->references('id')->on('campaigns')->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('wallet_used', 12, 2)->default(0)->after('total_cost');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'campaign_id')) {
                $table->dropForeign(['campaign_id']);
                $table->dropColumn('campaign_id');
            }
            if (Schema::hasColumn('order_items', 'campaign_discount')) {
                $table->dropColumn('campaign_discount');
            }
        });
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'wallet_used')) {
                $table->dropColumn('wallet_used');
            }
        });
    }
};

