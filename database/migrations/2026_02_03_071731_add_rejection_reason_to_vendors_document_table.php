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
        Schema::table('vendors_document', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors_document', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('is_verify');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors_document', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });
    }
};
