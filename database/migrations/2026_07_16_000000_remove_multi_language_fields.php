<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function dropColumnsIfExist(string $tableName, array $columns): void
    {
        $existing = [];
        foreach ($columns as $col) {
            if (Schema::hasColumn($tableName, $col)) {
                $existing[] = $col;
            }
        }
        if (!empty($existing)) {
            Schema::table($tableName, function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }

    public function up(): void
    {
        $this->dropColumnsIfExist('categories', ['name_ar', 'name_ne', 'slug_ar', 'slug_ne', 'description_ar', 'description_ne', 'meta_title_ar', 'meta_title_ne', 'meta_description_ar', 'meta_description_ne']);
        $this->dropColumnsIfExist('sub_categories', ['name_ar', 'name_ne', 'slug_ar', 'slug_ne', 'description_ar', 'description_ne']);
        $this->dropColumnsIfExist('child_categories', ['name_ar', 'name_ne', 'slug_ar', 'slug_ne', 'description_ar', 'description_ne', 'meta_title_ar', 'meta_title_ne', 'meta_description_ar', 'meta_description_ne']);
        $this->dropColumnsIfExist('products', ['name_ar', 'name_ne', 'slug_ar', 'slug_ne', 'short_description_ar', 'short_description_ne', 'description_ar', 'description_ne']);
        $this->dropColumnsIfExist('banners', ['title_ar', 'title_ne']);
        $this->dropColumnsIfExist('brands', ['name_ar', 'name_ne', 'description_ar', 'description_ne']);
        $this->dropColumnsIfExist('countries', ['name_ar', 'name_ne']);
        $this->dropColumnsIfExist('states', ['name_ar', 'name_ne']);
        $this->dropColumnsIfExist('cities', ['name_ar', 'name_ne']);
        $this->dropColumnsIfExist('product_size_category', ['name_ar', 'name_ne']);
        $this->dropColumnsIfExist('product_variants', ['color_ar', 'color_ne', 'size_ar', 'size_ne', 'material_ar', 'material_ne']);
        $this->dropColumnsIfExist('terms_and_conditions', ['title_ar', 'title_ne', 'content_ar', 'content_ne']);
        $this->dropColumnsIfExist('privacy_policies', ['title_ar', 'title_ne', 'content_ar', 'content_ne']);
        $this->dropColumnsIfExist('shipping_addresses', ['address_ar', 'address_ne', 'city_ar', 'city_ne', 'state_ar', 'state_ne', 'country_ar', 'country_ne']);
        $this->dropColumnsIfExist('contact_details', ['title_ar', 'title_ne', 'address_ar', 'address_ne', 'city_ar', 'city_ne', 'state_ar', 'state_ne', 'country_ar', 'country_ne', 'opening_hours_ar', 'opening_hours_ne']);
        $this->dropColumnsIfExist('about_us', ['title_ar', 'title_ne', 'content_ar', 'content_ne']);
        $this->dropColumnsIfExist('faqs', ['question_ar', 'question_ne', 'answer_ar', 'answer_ne']);
        $this->dropColumnsIfExist('vendor_policies', ['title_ar', 'title_ne', 'content_ar', 'content_ne']);
        $this->dropColumnsIfExist('campaigns', ['name_ar', 'name_ne']);
        $this->dropColumnsIfExist('product_variant_labels', ['name_ar', 'name_ne']);
    }

    public function down(): void
    {
        // Rollback not supported - columns would need to be re-added manually
    }
};
