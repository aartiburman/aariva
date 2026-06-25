<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPolicyAcceptance extends Model
{
    protected $fillable = [
        'vendor_id',
        'policy_id',
        'accepted_at',
    ];

    protected $dates = [
        'accepted_at',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(VendorPolicy::class, 'policy_id');
    }
}

