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
        'title_ar',
        'title_ne',
        'email',
        'phone',
        'whatsapp',
        'address',
        'address_ar',
        'address_ne',
        'city',
        'city_ar',
        'city_ne',
        'state',
        'state_ar',
        'state_ne',
        'country',
        'country_ar',
        'country_ne',
        'postal_code',
        'map_url',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'opening_hours',
        'opening_hours_ar',
        'opening_hours_ne',
        'status',
        'order_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'order_by' => 'integer',
    ];
}
