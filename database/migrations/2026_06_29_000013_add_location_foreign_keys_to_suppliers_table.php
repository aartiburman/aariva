<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'country_id')) {
                $table->unsignedInteger('country_id')->nullable()->after('country');
            }
            if (!Schema::hasColumn('suppliers', 'state_id')) {
                $table->unsignedInteger('state_id')->nullable()->after('country_id');
            }
            if (!Schema::hasColumn('suppliers', 'city_id')) {
                $table->unsignedInteger('city_id')->nullable()->after('state_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['country_id', 'state_id', 'city_id']);
        });
    }
};
