<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPayout extends Model
{
    protected $table = 'vendor_payouts';

    protected $fillable = [
        'vendor_id',
        'order_id',
        'order_item_id',
        'order_amount',
        'commission_amount',
        'payout_amount',
        'pg_fee_amount',
        'payout_frequency',
        'payment_method',
        'status',
        'paid_at',
        'total_orders'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
