<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
   protected $fillable = [
        'name',
        'name_ar',
        'name_ne',
        'slug',
        'slug_ar',
        'slug_ne',
        'parent_id',
        'image',
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

 

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class) ;
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
        return $this->hasMany(ChildCategory::class, 'category_id');
    }
}
