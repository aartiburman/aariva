<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Config;

class TimezoneMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $timezone = GeneralSetting::where('key', 'timezone')->value('value');
        
        if ($timezone) {
            Config::set('app.timezone', $timezone);
            date_default_timezone_set($timezone);
        }

        return $next($request);
    }
}
