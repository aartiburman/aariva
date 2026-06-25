<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StaffStatusUpdated extends Notification
{
    use Queueable;

    protected $status;

    public function __construct($status)
    {
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $statusText = $this->status == 1 ? 'Activated' : 'Deactivated';
        return [
            'title' => 'Account Status Updated',
            'message' => "Your account has been {$statusText} by Admin.",
            'type' => 'status_update',
        ];
    }
}
