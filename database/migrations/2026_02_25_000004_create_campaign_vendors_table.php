<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaign_vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('vendor_id');
            $table->decimal('budget_total', 12, 2)->default(0);
            $table->decimal('budget_spent', 12, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['campaign_id', 'vendor_id']);
            $table->foreign('campaign_id')->references('id')->on('campaigns')->cascadeOnDelete();
            $table->foreign('vendor_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_vendors');
    }
};

