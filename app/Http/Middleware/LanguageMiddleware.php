<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle($request, Closure $next)
    {
        $locale = $request->get('lang') ?: session('locale', config('app.locale'));
        
        if (in_array($locale, ['en', 'ar', 'zh', 'ja', 'hi', 'hing', 'de', 'fr', 'ko', 'pt', 'es', 'ru', 'it', 'tr', 'th', 'vi'])) {
            app()->setLocale($locale);
        } else {
            app()->setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
