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
         Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();

            // Tax Info
            $table->string('name');                 // GST, VAT, Sales Tax
            $table->string('slug')->unique();

            // Location based tax
            $table->string('country', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('city', 100)->nullable();

            // Tax Values
            $table->decimal('rate', 8, 2);          // Percentage
            $table->boolean('is_percentage')->default(true);

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
