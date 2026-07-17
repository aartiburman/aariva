<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSizeCategory extends Model
{
    protected $table = "product_size_category";
    protected $fillable = [
        'name',
        'status',
    ];
}
