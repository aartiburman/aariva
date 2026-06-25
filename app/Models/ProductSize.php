<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSize extends Model
{

    protected $table = "product_sizes";

   protected $fillable = [
        'size_cat_id',
        'name',
        'name_ar',
        'name_ne',
        'status',
    ];
}
