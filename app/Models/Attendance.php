<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'date',
        'punch_in_time',
        'punch_out_time',
        'punch_in_location',
        'punch_out_location',
        'punch_in_address',
        'punch_out_address',
        'total_hours',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
