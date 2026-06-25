<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCard extends Model
{
    protected $table = 'users_card';

    protected $fillable = [
        'user_id',
        'card_holder_name',
        'card_number',
        'expiry_month',
        'expiry_year',
        'card_type',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
