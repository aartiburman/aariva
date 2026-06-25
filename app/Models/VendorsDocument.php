<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorsDocument extends Model
{
    
     protected $table = 'vendors_document'; // use exact table name

    // ✅ Mass assignable fields
    protected $fillable = [
        'vendor_id', 'document_id', 'document_number', 'document', 'is_verify', 'rejection_reason'
    ];

    public function documentType()
    {
        return $this->belongsTo(KYC_Document::class, 'document_id');
    }
}
