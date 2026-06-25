<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorCampaignOptIn extends Model
{
    protected $table = 'vendor_campaign_optins';

    protected $fillable = [
        'vendor_id',
        'opted_in',
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}

