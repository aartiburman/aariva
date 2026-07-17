<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorPolicy extends Model
{
    protected $fillable = [
        'title',
        'content',
        'status',
        'version'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function acceptances(): HasMany
    {
        return $this->hasMany(VendorPolicyAcceptance::class, 'policy_id');
    }
}

