<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ShopRenewal;

class RenewalSubmittedNotification extends Notification
{
    use Queueable;

    protected $renewal;

    /**
     * Create a new notification instance.
     */
    public function __construct(ShopRenewal $renewal)
    {
        $this->renewal = $renewal;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('A new renewal request has been submitted.')
                    ->line('Shop: ' . $this->renewal->shop->name)
                    ->line('Agent: ' . $this->renewal->agent->first_name . ' ' . $this->renewal->agent->last_name)
                    ->line('Amount: ' . $this->renewal->amount)
                    ->action('View Renewal', url('/admin/renewals/' . $this->renewal->id))
                    ->line('Please review and approve/reject.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'renewal_submitted',
            'renewal_id' => $this->renewal->id,
            'shop_id' => $this->renewal->shop_id,
            'shop_name' => $this->renewal->shop->name,
            'agent_id' => $this->renewal->agent_id,
            'amount' => $this->renewal->amount,
            'message' => 'New renewal request submitted for ' . $this->renewal->shop->name . '.',
        ];
    }
}
