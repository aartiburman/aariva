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
        if (!Schema::hasColumn('users', 'rejection_reason')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable()->after('status');
            });
        }

        if (!Schema::hasColumn('products', 'rejection_reason')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });
    }
};
