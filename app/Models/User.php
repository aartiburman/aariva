<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\KYC_Document;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'uqid',
        'name',
        'store_name',
        'role',
        'email',
        'phone',
        'email_verified_at',
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'address',
        'country_id',
        'state_id',
        'city_id',
        'zip',
        'business_name',
        'pan_no',
        'vat_or_tax',
        'vendor_tax',
        'tax_id',
        'account_holder_name',
        'bank_name',
        'account_number',
        'status',
        'rejection_reason',
        'gender',
        'dob',
        'image',
        'agreement',
        'agreement_id',
        'wallet_balance',
        'payout_frequency',
        'reward_balance',
        'last_seen',
        'device_token',
        'device_type',
        'onesignal_player_id',
        'from_web',
        'referral_code',
        'referred_by',
        'social_id',
        'social_type',
        'category_ids',
        'vendor_description',
        'is_verified',
        'branch_location',
        'is_order_update_active',
        'is_promotional_email_active',   
        'is_newsletter_active',
        'delivery_days',
        'otp',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string', // ✅ cast role to string as it is an enum
            'category_ids' => 'array',
            'is_order_update_active' => 'boolean',
            'is_promotional_email_active' => 'boolean',   
            'is_newsletter_active' => 'boolean',
        ];
    }

    
    public function isAdmin(): bool
    {
        return (string)$this->role === '1';
    }

    public function isVendor(): bool
    {
        return (string)$this->role === '2';
    }

    public function isUser(): bool
    {
        return (string)$this->role === '3';
    }

 

    public function documents()
    {
        return $this->hasMany(VendorsDocument::class, 'vendor_id');
    }

    public function country() {
    return $this->belongsTo(Country::class);
}

public function state() {
    return $this->belongsTo(State::class);
}

public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    public function hasMinimumKyc(): bool
    {
        if (!$this->isVendor()) {
            return false;
        }

        $hasBusinessName = !empty($this->business_name);
        $hasMobile = !empty($this->phone);
        $hasAddress = !empty($this->address) && !empty($this->city_id) && !empty($this->state_id) && !empty($this->country_id);
        $hasBanking = !empty($this->bank_name) && !empty($this->account_number) && !empty($this->account_holder_name) && !empty($this->ifsc_code);
        
        return $hasBusinessName && $hasMobile && $hasAddress && $hasBanking;
    }

    /**
     * Check if all required active documents are uploaded and verified.
     */
    public function areRequiredDocumentsVerified(): bool
    {
        if (!$this->isVendor()) {
            return false;
        }

        $requiredDocsCount = KYC_Document::where('is_active', 1)->count();
        if ($requiredDocsCount === 0) {
            return true; // Or false? If no docs required, then they are "verified"
        }

        $approvedDocsCount = $this->documents()
            ->where('is_verify', '1')
            ->whereNotNull('document_id')
            ->whereIn('document_id', KYC_Document::where('is_active', 1)->pluck('id'))
            ->distinct('document_id')
            ->count('document_id');

        return $approvedDocsCount >= $requiredDocsCount;
    }

    /**
     * Check if all vendor documents are verified.
     */
    public function isDocumentsVerified(): bool
    {
        // If not a vendor, return false or true depending on logic (false is safer)
        if (!$this->isVendor()) {
            return false;
        }

        $totalDocuments = $this->documents()->count();
        $verifiedDocuments = $this->documents()->where('is_verify', '1')->count();

        // Must have at least one document and all of them must be verified
        return $totalDocuments > 0 && $totalDocuments === $verifiedDocuments;
    }
}
