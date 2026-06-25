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

            // Basic Info
            $table->string('name');                 // GST / VAT / Sales Tax
            $table->string('slug')->unique();

            // Tax Details
            $table->decimal('tax_percentage', 8, 2); // 18.00
            $table->string('country')->nullable();   // India, USA
            $table->string('state')->nullable();     // State-wise tax

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
