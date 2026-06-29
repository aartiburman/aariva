<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('abandoned_carts')) {
            Schema::create('abandoned_carts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('ip_address')->nullable();
                $table->json('cart_data')->nullable();
                $table->decimal('total', 12, 2)->default(0);
                $table->string('status')->default('active')->comment('active, recovered, lost');
                $table->timestamp('notified_at')->nullable();
                $table->timestamp('recovered_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
    }
};
