<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    
    // public function handle(Request $request, Closure $next): Response
    // {
    //     return $next($request);
    // }

      public function handle($request, Closure $next)
    {
        $locale = session('locale', 'en');
        App::setLocale($locale);

        if (! session('country_code')) {
            $defaultCountry = \App\Models\Country::where('shortname', 'IN')->first();
            session([
                'country_code'   => 'IN',
                'currency_code'  => $defaultCountry->currency_code ?? 'INR',
                'currency_symbol' => $defaultCountry->currency ?? '₹',
            ]);
        }

        return $next($request);
    }
}
