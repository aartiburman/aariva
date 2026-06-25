<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'receiver_id',
        'order_id',
        'order_item_id',
        'subject',
        'status',
        'priority',
        'last_reply_at',
        'escalated_at',
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
        'escalated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }
}
