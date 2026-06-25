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
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
                $table->string('name_ne')->nullable()->after('name_ar');
                $table->string('slug_ar')->nullable()->after('slug');
                $table->string('slug_ne')->nullable()->after('slug_ar');
                $table->text('description_ar')->nullable()->after('description');
                $table->text('description_ne')->nullable()->after('description_ar');
                $table->string('meta_title_ar')->nullable()->after('meta_title');
                $table->string('meta_title_ne')->nullable()->after('meta_title_ar');
                $table->text('meta_description_ar')->nullable()->after('meta_description');
                $table->text('meta_description_ne')->nullable()->after('meta_description_ar');
            }
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('sub_categories', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
                $table->string('name_ne')->nullable()->after('name_ar');
                $table->string('slug_ar')->nullable()->after('slug');
                $table->string('slug_ne')->nullable()->after('slug_ar');
                $table->text('description_ar')->nullable()->after('description');
                $table->text('description_ne')->nullable()->after('description_ar');
            }
        });

        Schema::table('child_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('child_categories', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
                $table->string('name_ne')->nullable()->after('name_ar');
                $table->string('slug_ar')->nullable()->after('slug');
                $table->string('slug_ne')->nullable()->after('slug_ar');
                $table->text('description_ar')->nullable()->after('description');
                $table->text('description_ne')->nullable()->after('description_ar');
                $table->string('meta_title_ar')->nullable()->after('meta_title');
                $table->string('meta_title_ne')->nullable()->after('meta_title_ar');
                $table->text('meta_description_ar')->nullable()->after('meta_description');
                $table->text('meta_description_ne')->nullable()->after('meta_description_ar');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
                $table->string('name_ne')->nullable()->after('name_ar');
                $table->string('slug_ar')->nullable()->after('slug');
                $table->string('slug_ne')->nullable()->after('slug_ar');
                $table->text('short_description_ar')->nullable()->after('short_description');
                $table->text('short_description_ne')->nullable()->after('short_description_ar');
                $table->text('description_ar')->nullable()->after('description');
                $table->text('description_ne')->nullable()->after('description_ar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_ne', 'slug_ar', 'slug_ne', 'description_ar', 'description_ne', 'meta_title_ar', 'meta_title_ne', 'meta_description_ar', 'meta_description_ne']);
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_ne', 'slug_ar', 'slug_ne', 'description_ar', 'description_ne']);
        });

        Schema::table('child_categories', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_ne', 'slug_ar', 'slug_ne', 'description_ar', 'description_ne', 'meta_title_ar', 'meta_title_ne', 'meta_description_ar', 'meta_description_ne']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_ne', 'slug_ar', 'slug_ne', 'short_description_ar', 'short_description_ne', 'description_ar', 'description_ne']);
        });
    }
};
