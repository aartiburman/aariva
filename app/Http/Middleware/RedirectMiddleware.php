<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;

class RedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $path = $request->path();

        if ($path === '/') {
            return $next($request);
        }

        $redirect = Redirect::where('old_url', $path)
            ->where('is_active', true)
            ->first();

        if ($redirect) {
            return redirect($redirect->new_url, $redirect->status_code);
        }

        return $next($request);
    }
}
