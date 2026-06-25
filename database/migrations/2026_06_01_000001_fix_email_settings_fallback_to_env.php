<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $setting = DB::table('email_settings')->first();

        if ($setting) {
            DB::table('email_settings')->where('id', $setting->id)->update([
                'mail_host' => env('MAIL_HOST'),
                'mail_port' => env('MAIL_PORT'),
                'mail_username' => env('MAIL_USERNAME'),
                'mail_password' => env('MAIL_PASSWORD'),
                'mail_encryption' => env('MAIL_ENCRYPTION'),
                'mail_from_address' => env('MAIL_FROM_ADDRESS'),
                'mail_from_name' => env('MAIL_FROM_NAME'),
                'use_alternate_smtp' => null,
                'alt_mail_host' => null,
                'alt_mail_port' => null,
                'alt_mail_username' => null,
                'alt_mail_password' => null,
                'alt_mail_encryption' => null,
                'alt_mail_from_address' => null,
                'alt_mail_from_name' => null,
            ]);
        } else {
            DB::table('email_settings')->insert([
                'mail_driver' => 'smtp',
                'mail_host' => env('MAIL_HOST'),
                'mail_port' => env('MAIL_PORT'),
                'mail_username' => env('MAIL_USERNAME'),
                'mail_password' => env('MAIL_PASSWORD'),
                'mail_encryption' => env('MAIL_ENCRYPTION'),
                'mail_from_address' => env('MAIL_FROM_ADDRESS'),
                'mail_from_name' => env('MAIL_FROM_NAME'),
                'status' => 1,
                'use_alternate_smtp' => null,
            ]);
        }
    }

    public function down(): void
    {
        // No rollback needed
    }
};
