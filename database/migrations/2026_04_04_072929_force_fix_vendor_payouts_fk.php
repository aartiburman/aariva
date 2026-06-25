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
        // First drop the old foreign key by name if it exists
        try {
            DB::statement('ALTER TABLE vendor_payouts DROP FOREIGN KEY vendor_payouts_order_id_foreign');
        } catch (\Exception $e) {
            // If dropping by name fails, try dropping by Laravel's standard naming convention
            try {
                Schema::table('vendor_payouts', function (Blueprint $table) {
                    $table->dropForeign(['order_id']);
                });
            } catch (\Exception $e) {
                // Ignore if it doesn't exist at all
            }
        }

        // Now drop the column completely and re-add it to ensure it's pointing to 'orders'
        if (Schema::hasColumn('vendor_payouts', 'order_id')) {
            Schema::table('vendor_payouts', function (Blueprint $table) {
                $table->dropColumn('order_id');
            });
        }

        // Re-add the column with the correct constraint pointing to 'orders'
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
