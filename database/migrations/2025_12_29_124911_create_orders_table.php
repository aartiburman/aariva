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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_reference_id')->unique();
            $table->string('transaction_id')->nullable();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_id')->nullable()->constrained('shipping_addresses')->nullOnDelete();

            $table->string('order_status')->default('0'); 
            $table->string('payment_status')->default('0');
            $table->string('payment_mode')->nullable(); // COD, Razorpay, Stripe
            $table->string('currency_code', 10)->default('INR');

            $table->decimal('sub_total', 10, 2);
            $table->decimal('delivery_charges', 10, 2)->default(0);
            $table->decimal('taxes', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2);

            $table->date('order_date');
            $table->date('delivery_date')->nullable();

            $table->boolean('is_apply_coupon')->default(false);
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();

            $table->string('refund_status')->nullable();
            $table->text('refund_reason')->nullable();

            $table->boolean('cancel_status')->default(false);
            $table->foreignId('cancel_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancel_reason')->nullable();

            $table->date('dispatched_date')->nullable();
            $table->boolean('tandc')->default(false);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
