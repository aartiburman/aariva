<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'agent_id',
        'amount',
        'selfie_url',
        'remarks',
        'status',
        'requested_duration_days',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
