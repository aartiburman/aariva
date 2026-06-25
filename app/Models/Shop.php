<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'latitude',
        'longitude',
        'created_by',
        'license_start_date',
        'license_duration_days',
        'license_expiry_date',
        'license_status',
    ];

    protected $casts = [
        'license_start_date' => 'date',
        'license_expiry_date' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($shop) {
            if ($shop->license_start_date && $shop->license_duration_days) {
                $shop->license_expiry_date = Carbon::parse($shop->license_start_date)->addDays((int) $shop->license_duration_days);
            }
            $shop->updateStatus();
        });
    }

    /**
     * Update the license status based on expiry date.
     */
    public function updateStatus()
    {
        if (!$this->license_expiry_date) {
            $this->license_status = 'active';
            return;
        }

        $today = Carbon::today();
        $expiry = Carbon::parse($this->license_expiry_date);
        $diff = $today->diffInDays($expiry, false);

        if ($diff < 0) {
            $this->license_status = 'expired';
        } elseif ($diff <= 7) {
            $this->license_status = 'expiring';
        } else {
            $this->license_status = 'active';
        }
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
