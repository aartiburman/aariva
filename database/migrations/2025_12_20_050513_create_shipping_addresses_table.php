<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();

            // User relation
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Shipping Details
            $table->string('name');          // Receiver name
            $table->string('email')->nullable();
            $table->string('phone', 20);

            // Address
            $table->text('address');
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('zip', 20);
            $table->string('country', 100);

            // Default address
            $table->boolean('is_default')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};
