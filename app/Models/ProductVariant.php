<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'color',
        'color_ar',
        'color_ne',
        'product_variant',
        'size_cat_id',
        'size',
        'size_ar',
        'size_ne',
        'stock',
        'price',
        'discount_type',
        'discount_value',
        'final_price',
        'image',
        'material',
        'material_ar',
        'material_ne',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

