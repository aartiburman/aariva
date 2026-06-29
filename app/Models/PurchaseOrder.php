<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'order_number', 'supplier_id', 'user_id', 'warehouse_id',
        'sub_total', 'discount', 'total', 'status', 'notes',
        'expected_at', 'received_at',
    ];

    protected $casts = [
        'expected_at' => 'datetime',
        'received_at' => 'datetime',
        'sub_total' => 'float',
        'discount' => 'float',
        'total' => 'float',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
