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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();

            $table->string('name'); 
            // Example: Order Placed, Vendor Payout, Password Reset

            $table->string('slug')->unique(); 
            // Example: order_placed, vendor_payout

            $table->string('subject'); 
            // Email subject

            $table->longText('body'); 
            // Email content (HTML allowed)

            $table->json('variables')->nullable(); 
            // Example: ["{{name}}", "{{order_id}}"]

            $table->boolean('is_active')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
