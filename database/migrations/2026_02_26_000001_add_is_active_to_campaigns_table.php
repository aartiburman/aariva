<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
        });

        // Backfill is_active based on status and date window
        try {
            DB::statement("
                UPDATE campaigns
                SET is_active = CASE
                    WHEN status = 1 AND (start_date IS NULL OR start_date <= NOW())
                         AND (end_date IS NULL OR end_date >= NOW()) THEN 1
                    ELSE 0
                END
            ");
        } catch (\Throwable $e) {
            // skip if DB driver limitations
        }
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
