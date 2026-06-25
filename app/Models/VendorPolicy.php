<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorPolicy extends Model
{
    protected $fillable = [
        'title',
        'title_ar',
        'title_ne',
        'content',
        'content_ar',
        'content_ne',
        'status',
        'version'
    ];

    public function getTitleAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale !== 'en') {
            return $this->{"title_{$locale}"} ?? $value;
        }
        return $value;
    }

    public function getContentAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale !== 'en') {
            return $this->{"content_{$locale}"} ?? $value;
        }
        return $value;
    }

    protected $casts = [
        'status' => 'boolean',
    ];

    public function acceptances(): HasMany
    {
        return $this->hasMany(VendorPolicyAcceptance::class, 'policy_id');
    }
}

