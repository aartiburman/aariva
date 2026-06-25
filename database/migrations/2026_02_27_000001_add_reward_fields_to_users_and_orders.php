<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'reward_balance')) {
                $table->decimal('reward_balance', 15, 2)->default(0)->after('wallet_balance');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'reward_used')) {
                $table->decimal('reward_used', 12, 2)->default(0)->after('total_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'reward_balance')) {
                $table->dropColumn('reward_balance');
            }
        });
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'reward_used')) {
                $table->dropColumn('reward_used');
            }
        });
    }
};
