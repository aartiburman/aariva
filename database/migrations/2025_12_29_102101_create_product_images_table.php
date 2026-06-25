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
       Schema::create('product_images', function (Blueprint $table) {
        $table->id();

        $table->foreignId('product_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('image'); // image path / filename

        $table->boolean('is_primary')->default(false); // main product image
        $table->integer('sort_order')->default(0); // image ordering
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
