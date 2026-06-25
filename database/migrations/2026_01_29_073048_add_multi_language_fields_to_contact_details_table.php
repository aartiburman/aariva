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
        Schema::table('contact_details', function (Blueprint $table) {
            if (!Schema::hasColumn('contact_details', 'title_ar')) {
                $table->string('title_ar')->nullable()->after('title');
                $table->string('title_ne')->nullable()->after('title_ar');
            }
            if (!Schema::hasColumn('contact_details', 'address_ar')) {
                $table->text('address_ar')->nullable()->after('address');
                $table->text('address_ne')->nullable()->after('address_ar');
            }
            if (!Schema::hasColumn('contact_details', 'city_ar')) {
                $table->string('city_ar')->nullable()->after('city');
                $table->string('city_ne')->nullable()->after('city_ar');
            }
            if (!Schema::hasColumn('contact_details', 'state_ar')) {
                $table->string('state_ar')->nullable()->after('state');
                $table->string('state_ne')->nullable()->after('state_ar');
            }
            if (!Schema::hasColumn('contact_details', 'country_ar')) {
                $table->string('country_ar')->nullable()->after('country');
                $table->string('country_ne')->nullable()->after('country_ar');
            }
            if (!Schema::hasColumn('contact_details', 'opening_hours_ar')) {
                $table->text('opening_hours_ar')->nullable()->after('opening_hours');
                $table->text('opening_hours_ne')->nullable()->after('opening_hours_ar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_details', function (Blueprint $table) {
            $table->dropColumn([
                'title_ar', 'title_ne',
                'address_ar', 'address_ne',
                'city_ar', 'city_ne',
                'state_ar', 'state_ne',
                'country_ar', 'country_ne',
                'opening_hours_ar', 'opening_hours_ne'
            ]);
        });
    }
};
