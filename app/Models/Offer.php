<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $table = 'offers';

    protected $fillable = [
        'code',
        'type',
        'value',
        'status',
        'valid_from',
        'valid_until',
        'max_uses',
        'used_count',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

  
}
