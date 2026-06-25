<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
        'logo',
        'status',
        'mode',
      
        'live_public_key',
        'live_secret_key',
        'test_public_key',
        'test_secret_key',
        'merchant_id',
        'app_id',
        'extra_params',
        'sandbox_base_url',
        'live_base_url',
        'success_url',
        'failure_url',
    ];

    protected $casts = [
        'status' => 'boolean',
        'extra_params' => 'array',
    ];
}
