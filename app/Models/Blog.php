<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'image',
        'status',
        'author_id',
        'views'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
