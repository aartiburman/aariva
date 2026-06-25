<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StoreVisitNotification extends Notification
{
    use Queueable;

    protected $visit;

    public function __construct($visit)
    {
        $this->visit = $visit;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'New Store Visit',
            'message' => "Staff {$this->visit->user->first_name} visited a store at {$this->visit->visit_time}.",
            'type' => 'store_visit',
            'visit_id' => $this->visit->id,
        ];
    }
}
