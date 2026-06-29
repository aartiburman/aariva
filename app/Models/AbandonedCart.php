<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedCart extends Model
{
    protected $fillable = [
        'user_id', 'ip_address', 'cart_data', 'total', 'status',
        'notified_at', 'recovered_at',
    ];

    protected $casts = [
        'cart_data' => 'array',
        'total' => 'float',
        'notified_at' => 'datetime',
        'recovered_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
