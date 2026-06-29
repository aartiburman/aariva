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
        'low_stock_threshold',
        'warehouse_id',
        'package_weight',
        'package_length',
        'package_width',
        'package_height',
        'package_type',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'product_variant_id');
    }
}

