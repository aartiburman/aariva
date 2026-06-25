<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE vendor_payouts MODIFY COLUMN status ENUM('pending', 'processing', 'approved', 'paid', 'failed', 'unpaid') DEFAULT 'pending'");
        
        Schema::table('vendor_payouts', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_payouts', 'payout_frequency')) {
                $table->string('payout_frequency', 20)->nullable()->after('payout_amount');
            }
            if (!Schema::hasColumn('vendor_payouts', 'order_item_id')) {
                $table->unsignedBigInteger('order_item_id')->nullable()->after('order_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE vendor_payouts MODIFY COLUMN status ENUM('pending', 'processing', 'approved', 'paid', 'failed') DEFAULT 'pending'");
        
        Schema::table('vendor_payouts', function (Blueprint $table) {
            $table->dropColumn(['payout_frequency', 'order_item_id']);
        });
    }
};
