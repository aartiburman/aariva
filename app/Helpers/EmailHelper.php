<?php

namespace App\Helpers;

use App\Models\EmailSetting;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailHelper
{
    /**
     * Get the configured mailer instance based on database settings
     */
    public static function getMailer()
    {
        $setting = EmailSetting::where('status', 1)->first();

        if ($setting) {
            if ($setting->use_alternate_smtp) {
                $config = [
                    'transport' => $setting->alt_mail_driver ?? 'smtp',
                    'host' => $setting->alt_mail_host ?? '',
                    'port' => $setting->alt_mail_port ?? '',
                    'encryption' => $setting->alt_mail_encryption ?? '',
                    'username' => $setting->alt_mail_username ?? '',
                    'password' => $setting->alt_mail_password ?? '',
                    'timeout' => null,
                    'auth_mode' => null,
                ];

                Config::set('mail.mailers.dynamic_smtp', $config);
                Config::set('mail.from.address', $setting->alt_mail_from_address ?? '');
                Config::set('mail.from.name', $setting->alt_mail_from_name ?? '');
            } else {
                $config = [
                    'transport' => $setting->mail_driver ?? 'smtp',
                    'host' => $setting->mail_host ?? '',
                    'port' => $setting->mail_port ?? '',
                    'encryption' => $setting->mail_encryption ?? '',
                    'username' => $setting->mail_username ?? '',
                    'password' => $setting->mail_password ?? '',
                    'timeout' => null,
                    'auth_mode' => null,
                ];

                Config::set('mail.mailers.dynamic_smtp', $config);
                Config::set('mail.from.address', $setting->mail_from_address ?? '');
                Config::set('mail.from.name', $setting->mail_from_name ?? '');
            }

            return Mail::mailer('dynamic_smtp');
        }

        Config::set('mail.mailers.dynamic_smtp', [
            'transport' => 'smtp',
            'host' => '',
            'port' => '',
            'encryption' => '',
            'username' => '',
            'password' => '',
            'timeout' => null,
            'auth_mode' => null,
        ]);
        Config::set('mail.from.address', '');
        Config::set('mail.from.name', '');

        return Mail::mailer('dynamic_smtp');
    }

    /**
     * Send an email using Laravel's Mail facade
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $message Email body (HTML)
     * @param string $template Blade template name
     * @param array $data Additional data for the template
     * @return bool True if sent successfully, false otherwise
     */
    public static function send($to, $subject, $message, $template = 'emails.common', $data = [])
    {
        $result = self::sendWithReason($to, $subject, $message, $template, $data);
        
        if (!$result['success']) {
            Log::error('EmailHelper Laravel Mail Error: ' . ($result['error'] ?? 'Unknown error'));
        }

        return $result['success'];
    }

    /**
     * Send email and return detailed result with reason on failure
     */
    public static function sendWithReason($to, $subject, $message, $template = 'emails.common', $data = []): array
    {
        try {
            if (empty($to)) {
                return ['success' => false, 'error' => 'Recipient email address is empty.'];
            }

            $logoDark = GeneralSetting::where('key', 'website_logo_dark')->first();
            $logoLight = GeneralSetting::where('key', 'website_logo_light')->first();
            
            $logo_url = '';
            if ($logoLight && $logoLight->value) {
                $logo_url = ImageHelper::getWebsiteLogo($logoLight->value, true);
            } elseif ($logoDark && $logoDark->value) {
                $logo_url = ImageHelper::getWebsiteLogo($logoDark->value, true);
            }

            $emailData = array_merge([
                'subject' => $subject,
                'message_body' => $message,
                'logo_url' => $logo_url,
                'website_name' => GeneralSetting::where('key', 'website_name')->value('value') ?? config('app.name')
            ], $data);

            $mailer = self::getMailer();
            $fromAddress = Config::get('mail.from.address');
            $fromName = Config::get('mail.from.name');

            $mailer->send($template, $emailData, function ($mail) use ($to, $subject, $fromAddress, $fromName) {
                $mail->to($to)
                    ->subject($subject)
                    ->from($fromAddress, $fromName);
            });

            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
