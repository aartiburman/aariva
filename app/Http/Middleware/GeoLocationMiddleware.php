<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Stevebauman\Location\Facades\Location;

class GeoLocationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('locale')) {
            $ip = $request->ip();
            $location = Location::get($ip);

            if ($location) {
                $country = \App\Models\Country::where('shortname', $location->countryCode)->first();

                session([
                    'country'        => $location->countryName,
                    'country_code'   => $location->countryCode,
                    'city'           => $location->cityName,
                    'currency_code'  => $country->currency_code ?? 'INR',
                    'currency_symbol' => $country->currency ?? '₹',
                ]);
            }
        }

        return $next($request);
    }
}
