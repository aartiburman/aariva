<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Auth;

class CheckMaintenanceMode
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
        // Skip for admin routes, auth routes, and maintenance control routes
        if ($request->is('admin/*') || 
            $request->is('login') || 
            $request->is('logout') || 
            $request->is('maintenance/*') ||
            $request->routeIs('admin.*') ||
            $request->routeIs('maintenance.*') ||
            $request->routeIs('login') ||
            $request->routeIs('logout') ||
            $request->routeIs('forgot.password') ||
            $request->routeIs('send.otp') ||
            $request->routeIs('verify.otp.form') ||
            $request->routeIs('otp.match') ||
            $request->routeIs('reset.password.form') ||
            $request->routeIs('reset.password')) {
            return $next($request);
        }

        // Check if maintenance mode is enabled
        $isMaintenance = false;
        try {
            // Check database setting
            $maintenanceSetting = GeneralSetting::where('key', 'maintenance_mode')->first();
            $dbEnabled = $maintenanceSetting && $maintenanceSetting->value == '1';
            
            // Check for physical flag file as backup/override
            $flagExists = file_exists(base_path('maintenance.flag'));
            
            $isMaintenance = $dbEnabled || $flagExists;
        } catch (\Exception $e) {
            // Fallback to flag file if database fails
            $isMaintenance = file_exists(base_path('maintenance.flag'));
        }
        
        if ($isMaintenance) {
            // 1. Admin (Role 1) always has access
            if (Auth::check() && Auth::user()->role == 1) {
                return $next($request);
            }

            // Get maintenance configuration
            $customUrlSetting = GeneralSetting::where('key', 'maintenance_custom_url')->first();
            $customUrl = $customUrlSetting ? $customUrlSetting->value : null;

            $rolesSetting = GeneralSetting::where('key', 'maintenance_roles')->first();
            $affectedRoles = $rolesSetting ? json_decode($rolesSetting->value, true) : ['2', '3'];
            if (!is_array($affectedRoles)) {
                $affectedRoles = ['2', '3'];
            }

            // 2. Check if current user/guest is affected
            $isAffected = false;
            if (Auth::check()) {
                if (in_array((string)Auth::user()->role, $affectedRoles)) {
                    $isAffected = true;
                }
            } else {
                // Guests are treated as Role 3 (Customers)
                if (in_array('3', $affectedRoles)) {
                    $isAffected = true;
                }
            }

            if ($isAffected) {
                // Determine user role for specific behavior
                $userRole = Auth::check() ? (string)Auth::user()->role : '3'; // Guests treated as Role 3

                // Handle API requests
                if ($request->is('api/*') || $request->expectsJson()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'System is currently under maintenance. Please try again later.',
                        'type' => 'maintenance'
                    ], 503);
                }

                // Role 3 (Customers) & Guests: Redirect if custom URL is set
                if ($userRole === '3' && $customUrl) {
                    $trimmedUrl = trim($customUrl, '/');
                    if (!$request->is($trimmedUrl) && !$request->is($trimmedUrl . '/*')) {
                        return redirect($customUrl);
                    }
                    // If they are already on the custom URL, let them see it
                    if ($request->is($trimmedUrl) || $request->is($trimmedUrl . '/*')) {
                        return $next($request);
                    }
                }

                // Default for Role 2 (Vendors) or Role 3 without custom URL: Show 503
                abort(503);
            }
        }

        return $next($request);
    }
}
