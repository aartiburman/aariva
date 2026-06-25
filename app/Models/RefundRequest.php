<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundRequest extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'user_id',
        'vendor_id',
        'refund_reason',
        'description',
        'images',
        'amount',
        'vendor_status',
        'vendor_message',
        'admin_status',
        'admin_message'
    ];
    
    protected $casts = [
        'images' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
    
}
