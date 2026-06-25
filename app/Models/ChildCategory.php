<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildCategory extends Model
{
      // ✅ Table name must be STRING, not array
    protected $table = 'child_categories'; // use exact table name

    // ✅ Mass assignable fields
    protected $fillable = [
        'category_id',
        'subcategory_id',
        'name',
        'name_ar',
        'name_ne',
        'slug',
        'slug_ar',
        'slug_ne',
        'is_active',
        'description',
        'description_ar',
        'description_ne',
        'meta_title',
        'meta_title_ar',
        'meta_title_ne',
        'meta_description',
        'meta_description_ar',
        'meta_description_ne',
    ];

    // ✅ Relationship with Category
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'subcategory_id');
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
}
