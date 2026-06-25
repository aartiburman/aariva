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
        Schema::create('products', function (Blueprint $table) {
    $table->id();

    // 🚨 MUST BE NULLABLE
    $table->unsignedBigInteger('vendor_id')->nullable();

    $table->unsignedBigInteger('category_id');
    $table->unsignedBigInteger('subcategory_id')->nullable();
    $table->unsignedBigInteger('child_category_id')->nullable();
    $table->unsignedBigInteger('brand_id')->nullable();

    $table->string('name');
    $table->string('slug')->unique();

    $table->decimal('price', 10, 2);
    $table->decimal('discount_price', 10, 2)->nullable();
    $table->decimal('actual_price', 10, 2)->nullable();

    $table->integer('stock')->default(0);

    $table->longText('specification')->nullable();
    $table->longText('notes')->nullable();
    $table->json('general_specification')->nullable();

    $table->boolean('is_featured')->default(0);
    $table->tinyInteger('status')->default(1);

    $table->timestamps();

    // 🔑 FOREIGN KEYS
    $table->foreign('vendor_id')
        ->references('id')
        ->on('users')
        ->onDelete('set null');

    $table->foreign('category_id')
        ->references('id')
        ->on('categories')
        ->onDelete('cascade');

    $table->foreign('subcategory_id')
        ->references('id')
        ->on('sub_categories')
        ->onDelete('set null');

    $table->foreign('child_category_id')
        ->references('id')
        ->on('child_categories')
        ->onDelete('set null');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
