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
        Schema::table('email_settings', function (Blueprint $table) {
            $table->boolean('use_alternate_smtp')->default(false);
            $table->string('alt_mail_driver')->default('smtp')->nullable();
            $table->string('alt_mail_host')->nullable();
            $table->string('alt_mail_port')->nullable();
            $table->string('alt_mail_username')->nullable();
            $table->string('alt_mail_password')->nullable();
            $table->string('alt_mail_encryption')->nullable();
            $table->string('alt_mail_from_address')->nullable();
            $table->string('alt_mail_from_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_settings', function (Blueprint $table) {
            $table->dropColumn([
                'use_alternate_smtp',
                'alt_mail_driver',
                'alt_mail_host',
                'alt_mail_port',
                'alt_mail_username',
                'alt_mail_password',
                'alt_mail_encryption',
                'alt_mail_from_address',
                'alt_mail_from_name',
            ]);
        });
    }
};
