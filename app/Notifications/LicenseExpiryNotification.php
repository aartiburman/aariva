<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Shop;

class LicenseExpiryNotification extends Notification
{
    use Queueable;

    protected $shop;

    /**
     * Create a new notification instance.
     */
    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
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
        $status = ucfirst($this->shop->license_status);
        return (new MailMessage)
                    ->line('The license for shop ' . $this->shop->name . ' is ' . $status . '.')
                    ->line('Expiry Date: ' . $this->shop->license_expiry_date->format('Y-m-d'))
                    ->action('View Shop', url('/admin/shops/' . $this->shop->id))
                    ->line('Please take necessary actions for renewal.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'license_expiry',
            'shop_id' => $this->shop->id,
            'shop_name' => $this->shop->name,
            'license_status' => $this->shop->license_status,
            'expiry_date' => $this->shop->license_expiry_date->format('Y-m-d'),
            'message' => 'Shop ' . $this->shop->name . ' license is ' . $this->shop->license_status . '.',
        ];
    }
}
