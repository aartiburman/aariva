<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'product_id',
        'variant_id',
        'qty',
        'color',
        'size',
        'price',
        'discount',
        'product_discount',
        'offer_code',
        'offer_discount',
        'campaign_id',
        'campaign_discount',
        'total_price',
        'image',
        'vendor_id',
    ];

    
     public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
