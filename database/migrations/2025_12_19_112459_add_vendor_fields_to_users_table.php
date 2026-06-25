<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // Role
          
            // Vendor Identity
            $table->string('store_name')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');

            // Address
            $table->text('address')->nullable()->before('created_at');
            $table->string('city')->nullable()->before('created_at');
            $table->string('state')->nullable()->before('created_at');
            $table->string('zip')->nullable()->before('created_at');

            // Business Details
            $table->string('business_name')->nullable()->before('created_at');
            $table->string('tax_id')->nullable()->before('created_at');

            // Bank Details
            $table->string('bank_name')->nullable()->before('created_at');
            $table->string('account_number')->nullable()->before('created_at');
            $table->string('ifsc_code')->nullable()->before('created_at');

            // Vendor Status
            $table->tinyInteger('status')
                  ->default(0)
                  ->comment('0=Pending, 1=Active, 2=Rejected')->before('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'owner_name',
                'store_name',
                'phone',
                'address',
                'city',
                'state',
                'zip',
                'business_name',
                'tax_id',
                'bank_name',
                'account_number',
                'ifsc_code',
                'status',
            ]);
        });
    }
};
