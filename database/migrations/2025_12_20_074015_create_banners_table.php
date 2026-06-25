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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug')->unique();

            $table->string('image');

            // Link settings
            $table->enum('link_type', ['product', 'category', 'brand', 'external'])->nullable();
            $table->unsignedBigInteger('link_id')->nullable();
            $table->string('link_url')->nullable();

            // Display settings
            $table->enum('position', ['top', 'middle', 'bottom', 'popup'])->default('top');
            $table->integer('order_by')->default(0);

            // Status & schedule
            $table->boolean('status')->default(1);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
