<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('campaign_vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('campaign_vendors', 'status')) {
                $table->string('status')->default('pending')->after('active'); // pending, approved, rejected
            }
        });

        // Backfill status based on active flag
        DB::table('campaign_vendors')->where('active', 1)->update(['status' => 'approved']);
        DB::table('campaign_vendors')->where('active', 0)->update(['status' => 'pending']);
    }

    public function down(): voidadd 
    {
        Schema::table('campaign_vendors', function (Blueprint $table) {
            if (Schema::hasColumn('campaign_vendors', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
