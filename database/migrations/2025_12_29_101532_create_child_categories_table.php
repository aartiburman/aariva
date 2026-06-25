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
       Schema::create('child_categories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('subcategory_id')->nullable();

            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_ne')->nullable();

            $table->string('slug')->unique();
            $table->string('slug_ar')->nullable();
            $table->string('slug_ne')->nullable();

            $table->boolean('is_active')->default(1);

            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_ne')->nullable();

            $table->string('meta_title')->nullable();
            $table->string('meta_title_ar')->nullable();
            $table->string('meta_title_ne')->nullable();

            $table->text('meta_description')->nullable();
            $table->text('meta_description_ar')->nullable();
            $table->text('meta_description_ne')->nullable();

            $table->timestamps();

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');

            $table->foreign('subcategory_id')
                ->references('id')
                ->on('sub_categories')
                ->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_categories');
    }
};
