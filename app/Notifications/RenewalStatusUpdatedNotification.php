<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ShopRenewal;

class RenewalStatusUpdatedNotification extends Notification
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
        $status = ucfirst($this->renewal->status);
        $message = 'Your renewal request for shop ' . $this->renewal->shop->name . ' has been ' . $status . '.';
        
        return (new MailMessage)
                    ->line($message)
                    ->line('Amount: ' . $this->renewal->amount)
                    ->action('View Shop', url('/agent/shops/' . $this->renewal->shop_id))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'renewal_updated',
            'renewal_id' => $this->renewal->id,
            'shop_id' => $this->renewal->shop_id,
            'shop_name' => $this->renewal->shop->name,
            'status' => $this->renewal->status,
            'message' => 'Renewal request for ' . $this->renewal->shop->name . ' ' . $this->renewal->status . '.',
        ];
    }
}
