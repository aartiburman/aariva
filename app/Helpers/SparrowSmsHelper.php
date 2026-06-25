<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use App\Models\SmsSetting;

class SparrowSmsHelper
{
    public static function send($to, $message)
    {
        try {
            $smsSetting = SmsSetting::first();
            
            // If sms setting exists and gateway is sparrow, use db config, else fallback to env
            $token = ($smsSetting && $smsSetting->sms_gateway === 'sparrow' && !empty($smsSetting->api_key)) 
                ? $smsSetting->api_key 
                : env('SPARROW_SMS_TOKEN');
                
            $from = ($smsSetting && $smsSetting->sms_gateway === 'sparrow' && !empty($smsSetting->from_number)) 
                ? $smsSetting->from_number 
                : env('SPARROW_SMS_FROM');
                
            $url = ($smsSetting && $smsSetting->sms_gateway === 'sparrow' && !empty($smsSetting->api_secret) && filter_var($smsSetting->api_secret, FILTER_VALIDATE_URL))
                ? $smsSetting->api_secret
                : env('SPARROW_SMS_URL', 'https://api.sparrowsms.com/v2/sms/');

            $response = Http::asForm()->post($url, [
                'token'   => $token,
                'from'    => $from,
                'to'      => $to,
                'text'    => $message,
            ]);

            return [
                'status' => $response->successful(),
                'response' => $response->json(),
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'response' => $e->getMessage(),
            ];
        }
    }
}