<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
         'vendor_id', 'category_id', 'subcategory_id', 'child_category_id', 'brand_id', 'name', 'name_ar', 'name_ne', 'slug', 'slug_ar', 'slug_ne', 'short_description', 'short_description_ar', 'short_description_ne', 'description', 'description_ar', 'description_ne', 'meta_title', 'meta_description', 'thumbnail', 'status', 'rejection_reason', 'is_featured', 'offer_id', 'product_in', 'is_upload', 're_added',
         'vendor_warranty', 'vendor_payment', 'vendor_return', 'vendor_delivery'
    ];


    /* ---------------- VARIANTS ---------------- */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function firstVariant()
    {
        return $this->hasOne(ProductVariant::class, 'product_id')->orderBy('id');
    }

    public function lowestPriceVariant()
    {
        return $this->hasOne(ProductVariant::class, 'product_id')
            ->orderBy('price', 'asc');
    }

    /* ---------------- VENDOR ---------------- */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /* ---------------- CATEGORY ---------------- */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'subcategory_id');
    }

    public function childCategory()
    {
        return $this->belongsTo(ChildCategory::class, 'child_category_id');
    }

    /* ---------------- BRAND ---------------- */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function getNameAttribute($value)
    {
        $locale = app()->getLocale();
        if ($locale !== 'en') {
            return $this->{"name_{$locale}"} ?: $value;
        }
        return ucfirst($value);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucfirst($value);
    }

    /* ---------------- OFFER ---------------- */
    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }

    /* ---------------- REVIEWS ---------------- */
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(ProductReview::class)->where('status', 1);
    }

    public function scopeInCustomerCountry($query, $userId = null)
    {
        if (!$userId) {
            return $query;
        }

        $user = User::find($userId);
        if ($user && (string)$user->role === '3' && $user->country_id) {
            return $query->whereHas('vendor', function ($q) use ($user) {
                $q->where('country_id', $user->country_id)
                  ->orWhere('role', '1'); // Always show Admin (role 1) products
            });
        }

        return $query;
    }

    public function averageRating()
    {
        return $this->approvedReviews()->avg('rating');
    }

    /* ---------------- CAMPAIGN ---------------- */
    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_products', 'product_id', 'campaign_id')
            ->withPivot(['status'])
            ->withTimestamps();
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier')
            ->withPivot(['supplier_sku', 'supply_price', 'lead_time_days', 'is_preferred'])
            ->withTimestamps();
    }

    public function activeCampaign()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_products', 'product_id', 'campaign_id')
            ->where('campaigns.is_active', 1)
            ->where('campaigns.status', 1)
            ->where(function($q) {
                $now = now();
                $q->whereNull('campaigns.start_date')->orWhere('campaigns.start_date', '<=', $now);
            })
            ->where(function($q) {
                $now = now();
                $q->whereNull('campaigns.end_date')->orWhere('campaigns.end_date', '>=', $now);
            })
            ->wherePivot('status', 1);
    }
}
