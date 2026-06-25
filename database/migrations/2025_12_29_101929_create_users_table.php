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
     Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Unique User ID
            $table->string('uqid')->unique();

            // Basic Info
            $table->string('name');
            $table->string('store_name')->nullable();
            $table->string('role')->default('user'); // admin, vendor, user

            // Contact
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();

            // Auth
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('otp', 10)->nullable();
            $table->rememberToken();

            // Address
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip', 15)->nullable();

            // Business Info
            $table->string('business_name')->nullable();
            $table->string('tax_id')->nullable();

            // Bank Info
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();

            // Extra
            $table->boolean('status')->default(1); // 1 = active
            $table->enum('gender', ['male','female','other'])->nullable();
            $table->date('dob')->nullable();
            $table->string('image')->nullable();
            $table->boolean('agreement')->default(0);

            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
