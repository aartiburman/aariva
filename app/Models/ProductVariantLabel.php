<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantLabel extends Model
{
    protected $table = 'product_variant_labels';

    protected $fillable = [
        'name',
        'name_ar',
        'name_ne',
        'status',
    ];
}
