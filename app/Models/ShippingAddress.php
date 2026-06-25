<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
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
        'city_id',
        'state_id',
        'country_id',
        'zip',
        'is_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
