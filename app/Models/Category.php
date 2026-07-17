<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
   protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'image',
        'is_active',
        'description',
        'meta_title',
        'meta_description',
    ];

 

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class) ;
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
