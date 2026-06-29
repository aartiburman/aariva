<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'package_weight')) {
                $table->decimal('package_weight', 10, 2)->nullable()->after('material');
            }
            if (!Schema::hasColumn('product_variants', 'package_length')) {
                $table->decimal('package_length', 10, 2)->nullable()->after('package_weight');
            }
            if (!Schema::hasColumn('product_variants', 'package_width')) {
                $table->decimal('package_width', 10, 2)->nullable()->after('package_length');
            }
            if (!Schema::hasColumn('product_variants', 'package_height')) {
                $table->decimal('package_height', 10, 2)->nullable()->after('package_width');
            }
            if (!Schema::hasColumn('product_variants', 'package_type')) {
                $table->string('package_type', 100)->nullable()->after('package_height');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['package_weight', 'package_length', 'package_width', 'package_height', 'package_type']);
        });
    }
};
