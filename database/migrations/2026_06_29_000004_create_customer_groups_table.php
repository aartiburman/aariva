<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_groups')) {
            Schema::create('customer_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('customer_group_customer')) {
            Schema::create('customer_group_customer', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_group_id')->constrained('customer_groups')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['customer_group_id', 'customer_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_group_customer');
        Schema::dropIfExists('customer_groups');
    }
};
