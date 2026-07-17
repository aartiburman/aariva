<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildCategory extends Model
{
      // ✅ Table name must be STRING, not array
    protected $table = 'child_categories'; // use exact table name

    protected $fillable = [
        'category_id',
        'subcategory_id',
        'name',
        'slug',
        'is_active',
        'description',
        'meta_title',
        'meta_description',
    ];

    // ✅ Relationship with Category
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'subcategory_id');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucfirst($value);
    }
}
