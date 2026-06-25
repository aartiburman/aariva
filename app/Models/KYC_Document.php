<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KYC_Document extends Model
{
        protected $table = "kyc_documents";
    protected $fillable = [
      'name',
      'is_active',
    ];
}
