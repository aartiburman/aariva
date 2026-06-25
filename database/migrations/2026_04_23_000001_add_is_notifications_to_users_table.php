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
        Schema::table('users', function (Blueprint $table) {
          
            $table->boolean('is_order_update_active')->default(true)->after('is_newsletter_notifications');
            $table->boolean('is_promotional_email_active')->default(true)->after('is_order_update_active');
            $table->boolean('is_newsletter_active')->default(true)->after('is_promotional_email_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
             
                'is_order_update_active',
                'is_promotional_email_active',
                'is_newsletter_active'
            ]);
        });
    }
};
