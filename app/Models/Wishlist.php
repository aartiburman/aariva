<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
  protected $table = 'wishlists'; // use exact table name

    // ✅ Mass assignable fields
    protected $fillable = [
         'user_id', 'ip_address', 'product_id', 'qty', 'color', 'size', 'image','price', 'status'
    ];

     public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
