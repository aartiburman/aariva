<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    // ✅ Table name must be STRING, not array
    protected $table = 'subcategories';

    // ✅ Mass assignable fields
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'image',
        'description',
        'meta_title',
        'meta_description',
        'is_active',
    ];

    // ✅ Relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class);
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
