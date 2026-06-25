<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'used_count',
        'valid_from',
        'valid_until',
        'category_ids',
        'product_ids',
        'vendor_ids',
        'status',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'status' => 'boolean',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'coupon_category');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'coupon_product');
    }

    public function vendors()
    {
        return $this->belongsToMany(User::class, 'coupon_vendor', 'coupon_id', 'vendor_id');
    }
}
