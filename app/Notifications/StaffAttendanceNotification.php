<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StaffAttendanceNotification extends Notification
{
    use Queueable;

    protected $attendance;
    protected $type; // 'punch_in' or 'punch_out'

    public function __construct($attendance, $type)
    {
        $this->attendance = $attendance;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $action = $this->type === 'punch_in' ? 'Punched In' : 'Punched Out';
        $time = $this->type === 'punch_in' ? $this->attendance->punch_in_time : $this->attendance->punch_out_time;
        
        return [
            'title' => "Staff {$action}",
            'message' => "Staff {$this->attendance->user->first_name} {$action} at {$time}.",
            'type' => $this->type,
            'attendance_id' => $this->attendance->id,
        ];
    }
}
