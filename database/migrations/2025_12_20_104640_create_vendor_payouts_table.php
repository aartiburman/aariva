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
       Schema::create('vendor_payouts', function (Blueprint $table) {
            $table->id();

            // Vendor relation
            $table->foreignId('vendor_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Order relation (optional)
            $table->foreignId('order_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // Financials
            $table->decimal('order_amount', 10, 2);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->decimal('payout_amount', 10, 2);

            // Payment info
            $table->string('payment_method')->nullable(); // bank / upi / paypal
            $table->string('transaction_id')->nullable();

            // Status
            $table->enum('status', ['pending', 'processing', 'paid', 'failed'])
                  ->default('pending');

            // Notes
            $table->text('note')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_payouts');
    }
};
