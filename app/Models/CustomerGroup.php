<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerGroup extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'status'];

    public function customers()
    {
        return $this->belongsToMany(User::class, 'customer_group_customer', 'customer_group_id', 'customer_id');
    }
}
