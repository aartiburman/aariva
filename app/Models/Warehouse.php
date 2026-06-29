<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'name', 'slug', 'location', 'phone', 'email', 'manager_name', 'status',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
