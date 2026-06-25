<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\RefundRequest;

class OrderItem extends Model
{
     protected $fillable = [
        'vendor_id', 
        'order_id', 
        'product_id', 
        'variant_id', 
        'campaign_id', 
        'price', 
        'discount', 
        'offer_discount',
        'campaign_discount', 
        'quantity', 
        'actual_price', 
        'total_actual_price', 
        'status',
        'payment_status',
        'payment_mode',
        'currency',
        'vendor_tax',
        'tax_amount',
        'logistics_provider',
        'tracking_id',
        'logistics_status',
        'delivery_charges',
        'card_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function supportTicket()
    {
        return $this->hasOne(Ticket::class, 'order_item_id');
    }

    public function refundRequest()
    {
        return $this->hasOne(RefundRequest::class, 'order_item_id');
    }

    public function review()
    {
        return $this->hasOne(ProductReview::class, 'order_item_id');
    }
}
