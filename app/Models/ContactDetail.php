<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactDetail extends Model
{
    protected $fillable = [
        'country_id',
        'state_id',
        'city_id',
        'title',
        'email',
        'phone',
        'whatsapp',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'map_url',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'opening_hours',
        'status',
        'order_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'order_by' => 'integer',
    ];
}
