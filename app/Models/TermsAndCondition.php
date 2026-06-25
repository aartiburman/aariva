<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermsAndCondition extends Model
{
    protected $fillable = ['title', 'title_ar', 'title_ne', 'content', 'content_ar', 'content_ne', 'status'];
}
