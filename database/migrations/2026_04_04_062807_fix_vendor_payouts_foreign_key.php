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
        // Try to drop foreign key by column name (it works in most DBs)
        try {
            Schema::table('vendor_payouts', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
            });
        } catch (\Exception $e) {
            // Ignore if it doesn't exist
        }

        // Now drop the column if it exists
        if (Schema::hasColumn('vendor_payouts', 'order_id')) {
            Schema::table('vendor_payouts', function (Blueprint $table) {
                $table->dropColumn('order_id');
            });
        }

        // Finally re-add the column with the correct constraint pointing to 'orders'
        Schema::table('vendor_payouts', function (Blueprint $table) {
            $table->foreignId('order_id')
                  ->nullable()
                  ->after('vendor_id')
                  ->constrained('orders')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_payouts', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });

        Schema::table('vendor_payouts', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->after('vendor_id');
        });
    }
};
