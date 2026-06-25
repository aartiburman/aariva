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
         Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();

            $table->string('name');               // Zone Name (North India, Metro Cities)
            $table->string('slug')->unique();
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->decimal('free_shipping_above', 10, 2)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
