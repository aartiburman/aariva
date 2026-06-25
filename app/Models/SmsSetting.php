<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    protected $fillable = [
        'sms_gateway',
        'api_key',
        'api_secret',
        'from_number',
        'status'
    ];
}
