<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewReaction extends Model
{
    use HasFactory;

    protected $table = 'review_reactions';

    protected $fillable = [
        'review_id',
        'user_id',
        'reaction_type',
    ];

    public function review()
    {
        return $this->belongsTo(ProductReview::class, 'review_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
