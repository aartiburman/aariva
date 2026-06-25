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
      Schema::create('sub_categories', function (Blueprint $table) {
    $table->id(); // BIGINT UNSIGNED
    $table->foreignId('category_id')
          ->constrained('categories')
          ->cascadeOnDelete();

    $table->string('name');
    $table->boolean('is_active')->default(1);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcategories');
    }
};
