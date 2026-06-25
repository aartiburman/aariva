<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopVisit extends Model
{
    use HasFactory;

    protected $primaryKey = 'visit_id';

    protected $fillable = [
        'user_id',
        'shop_id',
        'visit_date',
        'visit_time',
        'photo_path',
        'latitude',
        'longitude',
        'address',
        'remark',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
