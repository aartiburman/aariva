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
        'views',
        'meta_title',
        'meta_description'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
