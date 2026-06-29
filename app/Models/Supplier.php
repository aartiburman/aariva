<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'company_name', 'email', 'phone', 'address',
        'city', 'state', 'country', 'country_id', 'state_id', 'city_id',
        'gst_number', 'contact_person', 'notes', 'status',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_supplier')
            ->withPivot(['supplier_sku', 'supply_price', 'lead_time_days', 'is_preferred'])
            ->withTimestamps();
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function supplierCountry()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function supplierState()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function supplierCity()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
