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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id')->unique();
            $table->unsignedBigInteger('user_id'); // Creator (Customer or Vendor)
            $table->unsignedBigInteger('receiver_id')->nullable(); // Receiver (Vendor or Admin)
            $table->unsignedBigInteger('order_id')->nullable(); // Related Order
            $table->unsignedBigInteger('order_item_id')->nullable(); // Related Order Item
            $table->string('subject');
            $table->enum('status', ['Open', 'In Progress', 'Resolved', 'Closed', 'Escalated'])->default('Open');
            $table->enum('priority', ['Low', 'Medium', 'High'])->default('Medium');
            $table->timestamp('last_reply_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
