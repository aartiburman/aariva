<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    // ✅ Table name must be STRING, not array
    protected $table = 'sub_categories'; // use exact table name

    // ✅ Mass assignable fields
    protected $fillable = [
        'category_id',
        'name',
        'name_ar',
        'name_ne',
        'slug',
        'slug_ar',
        'slug_ne',
        'image',
        'description',
        'description_ar',
        'description_ne',
        'is_active',
    ];

    // ✅ Relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getNameAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale !== 'en') {
            return $this->{"name_{$locale}"} ?: $value;
        }
        return ucfirst($value);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucfirst($value);
    }

    public function childCategories()
    {
        return $this->hasMany(
            ChildCategory::class,
            'subcategory_id',
            'id'
        )->where('is_active', 1);
    }
}
