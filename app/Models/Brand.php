<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'name_ne',
        'slug',
        'logo',
        'description',
        'description_ar',
        'description_ne',
        'meta_title',
        'meta_description',
        'status',
        'category_id',
        'subcategory_id',
        'childcategory_id',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
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
