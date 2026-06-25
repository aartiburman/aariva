<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_policy_acceptances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('policy_id');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('policy_id')->references('id')->on('vendor_policies')->onDelete('cascade');
            $table->unique(['vendor_id', 'policy_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_policy_acceptances');
    }
};

