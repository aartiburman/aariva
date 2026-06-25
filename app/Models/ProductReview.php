<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    use HasFactory;

    protected $table = 'product_reviews';

    protected $fillable = [
        'product_id',
        'order_item_id',
        'variant_id',
        'user_id',
        'rating',
        'review',
        'status'
    ];

    /**
     * Get the product associated with the review.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who wrote the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the variant associated with the review.
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function reactions()
    {
        return $this->hasMany(ReviewReaction::class, 'review_id');
    }

    public function likes()
    {
        return $this->hasMany(ReviewReaction::class, 'review_id')->where('reaction_type', 'like');
    }

    public function dislikes()
    {
        return $this->hasMany(ReviewReaction::class, 'review_id')->where('reaction_type', 'dislike');
    }
}
