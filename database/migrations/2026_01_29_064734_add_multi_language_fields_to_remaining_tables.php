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
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'title_ar')) {
                $table->string('title_ar')->nullable()->after('title');
                $table->string('title_ne')->nullable()->after('title_ar');
            }
        });

        Schema::table('brands', function (Blueprint $table) {
            if (!Schema::hasColumn('brands', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
                $table->string('name_ne')->nullable()->after('name_ar');
                $table->text('description_ar')->nullable()->after('description');
                $table->text('description_ne')->nullable()->after('description_ar');
            }
        });

        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasColumn('countries', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
                $table->string('name_ne')->nullable()->after('name_ar');
            }
        });

        Schema::table('states', function (Blueprint $table) {
            if (!Schema::hasColumn('states', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
                $table->string('name_ne')->nullable()->after('name_ar');
            }
        });

        Schema::table('cities', function (Blueprint $table) {
            if (!Schema::hasColumn('cities', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
                $table->string('name_ne')->nullable()->after('name_ar');
            }
        });

        Schema::table('product_size_category', function (Blueprint $table) {
            if (!Schema::hasColumn('product_size_category', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
                $table->string('name_ne')->nullable()->after('name_ar');
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'color_ar')) {
                $table->string('color_ar')->nullable()->after('color');
                $table->string('color_ne')->nullable()->after('color_ar');
                $table->string('size_ar')->nullable()->after('size');
                $table->string('size_ne')->nullable()->after('size_ar');
                $table->string('material_ar')->nullable()->after('material');
                $table->string('material_ne')->nullable()->after('material_ar');
            }
        });

        Schema::table('terms_and_conditions', function (Blueprint $table) {
            if (!Schema::hasColumn('terms_and_conditions', 'title_ar')) {
                $table->string('title_ar')->nullable()->after('title');
                $table->string('title_ne')->nullable()->after('title_ar');
                $table->text('content_ar')->nullable()->after('content');
                $table->text('content_ne')->nullable()->after('content_ar');
            }
        });

        Schema::table('privacy_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('privacy_policies', 'title_ar')) {
                $table->string('title_ar')->nullable()->after('title');
                $table->string('title_ne')->nullable()->after('title_ar');
                $table->text('content_ar')->nullable()->after('content');
                $table->text('content_ne')->nullable()->after('content_ar');
            }
        });

        Schema::table('product_sizes', function (Blueprint $table) {
            if (!Schema::hasColumn('product_sizes', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
                $table->string('name_ne')->nullable()->after('name_ar');
            }
        });

        Schema::table('shipping_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('shipping_addresses', 'address_ar')) {
                $table->text('address_ar')->nullable()->after('address');
                $table->text('address_ne')->nullable()->after('address_ar');
            }
            if (!Schema::hasColumn('shipping_addresses', 'city_ar')) {
                $table->string('city_ar')->nullable()->after('city');
                $table->string('city_ne')->nullable()->after('city_ar');
            }
            if (!Schema::hasColumn('shipping_addresses', 'state_ar')) {
                $table->string('state_ar')->nullable()->after('state');
                $table->string('state_ne')->nullable()->after('state_ar');
            }
            if (!Schema::hasColumn('shipping_addresses', 'country_ar')) {
                $table->string('country_ar')->nullable()->after('country');
                $table->string('country_ne')->nullable()->after('country_ar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'title_ne']);
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_ne', 'description_ar', 'description_ne']);
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_ne']);
        });

        Schema::table('states', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_ne']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_ne']);
        });

        Schema::table('product_size_category', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_ne']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['color_ar', 'color_ne', 'size_ar', 'size_ne', 'material_ar', 'material_ne']);
        });

        Schema::table('terms_and_conditions', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'title_ne', 'content_ar', 'content_ne']);
        });

        Schema::table('privacy_policies', function (Blueprint $table) {
            $table->dropColumn(['title_ar', 'title_ne', 'content_ar', 'content_ne']);
        });

        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->dropColumn(['address_ar', 'address_ne', 'city_ar', 'city_ne', 'state_ar', 'state_ne', 'country_ar', 'country_ne']);
        });
    }
};
