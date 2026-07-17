<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'discount_percent',
        'start_date',
        'end_date',
        'status',
        'is_active',
        'offer_id',
        'budget_per_vendor',
        'max_vendors',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'campaign_vendors', 'campaign_id', 'vendor_id')
            ->withPivot(['budget_total', 'budget_spent', 'active', 'status'])
            ->withTimestamps();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'campaign_products', 'campaign_id', 'product_id')
            ->withPivot(['status'])
            ->withTimestamps();
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }
}
