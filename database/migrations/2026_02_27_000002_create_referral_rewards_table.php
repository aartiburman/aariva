<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id');
            $table->unsignedBigInteger('referred_id');
            $table->unsignedBigInteger('order_id');
            $table->decimal('referrer_amount', 12, 2)->default(0);
            $table->decimal('referred_amount', 12, 2)->default(0);
            $table->timestamps();

            $table->unique('referred_id'); // One reward per referred user
            $table->foreign('referrer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('referred_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_rewards');
    }
};
