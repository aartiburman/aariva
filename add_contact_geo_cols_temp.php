<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    Schema::table('contact_details', function (Blueprint $table) {
        if (!Schema::hasColumn('contact_details', 'country_id')) {
            $table->unsignedBigInteger('country_id')->nullable()->after('id');
        }
        if (!Schema::hasColumn('contact_details', 'state_id')) {
            $table->unsignedBigInteger('state_id')->nullable()->after('country_id');
        }
        if (!Schema::hasColumn('contact_details', 'city_id')) {
            $table->unsignedBigInteger('city_id')->nullable()->after('state_id');
        }
    });
    echo "Columns added\n";
} catch (\Throwable $e) {
    echo "Error: ".$e->getMessage()."\n";
}

