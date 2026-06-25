<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',    
    ];

  
}
