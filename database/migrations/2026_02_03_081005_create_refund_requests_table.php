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
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('user_id'); // customer
            $table->unsignedBigInteger('vendor_id');
            $table->string('refund_reason');
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->decimal('amount', 15, 2);
            $table->tinyInteger('vendor_status')->default(0)->comment('0:pending, 1:initiated, 2:rejected');
            $table->text('vendor_message')->nullable();
            $table->tinyInteger('admin_status')->default(0)->comment('0:pending, 1:approved, 2:rejected');
            $table->text('admin_message')->nullable();
            $table->timestamps();

            // Foreign keys (optional but good practice if tables exist)
            // $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            // $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
