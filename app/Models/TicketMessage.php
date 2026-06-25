<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'attachment',
    ];

    public function getAttachmentAttribute($value)
    {
        return $value ? \App\Helpers\ImageHelper::getTicketAttachment($value) : null;
    }

    /**
     * Get the raw filename from database
     */
    public function getRawAttachment()
    {
        return $this->getRawOriginal('attachment');
    }

    /**
     * Check if the attachment is an image
     */
    public function isImage()
    {
        $file = $this->getRawAttachment();
        if (!$file) return false;
        
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Check if the attachment is a PDF
     */
    public function isPdf()
    {
        $file = $this->getRawAttachment();
        if (!$file) return false;
        
        return strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf';
    }

    /**
     * Get the display name of the attachment
     */
    public function getAttachmentName()
    {
        $file = $this->getRawAttachment();
        return $file ? basename($file) : null;
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
