<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'budget_per_vendor')) {
                $table->decimal('budget_per_vendor', 12, 2)->nullable()->after('coupon_id');
            }
            if (!Schema::hasColumn('campaigns', 'max_vendors')) {
                $table->unsignedInteger('max_vendors')->nullable()->after('budget_per_vendor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'budget_per_vendor')) {
                $table->dropColumn('budget_per_vendor');
            }
            if (Schema::hasColumn('campaigns', 'max_vendors')) {
                $table->dropColumn('max_vendors');
            }
        });
    }
};
