<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\OrderItem;


class Order extends Model
{
     protected $fillable = [
        'order_reference_id',
        'transaction_id',
        'user_id',
        'sub_total',
        'product_discounts',
        'coupon_discounts',
        'offer_discounts',
        'campaign_discounts',
        'total_discount',
        'delivery_charges',
        'taxes',
        'total_cost',
        'reward_used',
        'wallet_used',
        'payment_mode',
        'payment_status',
        'status',
        'shipping_id',
        'currency_code',
        'order_date',
        'delivery_date',
        'coupon_id',
        'coupon_code',
        'refund_status',
        'refund_reason',
        'cancel_status',
        'cancel_user_id',
        'cancel_reason',
        'dispatched_date',
        'tandc',
        'vendor_id',
        'card_type',
        'card_id',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'dispatched_date' => 'date',
        'is_apply_offer' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class, 'shipping_id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}
