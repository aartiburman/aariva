<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Helpers\EmailHelper;
use Illuminate\Support\Facades\Hash;
use App\Helpers\NotificationHelper;
use App\Helpers\ImageHelper;
use GPBMetadata\Google\ApiLog;  
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\NotificationSetting;
use App\Models\GeneralSetting;


class LoginController extends Controller
{
    public function showLoginForm()
    {
        $notificationSetting = NotificationSetting::first();
        $generalSettings = GeneralSetting::pluck('value', 'key')->toArray();
        
        $darkLogo = isset($generalSettings['website_logo_dark']) ? ImageHelper::getWebsiteLogo($generalSettings['website_logo_dark']) : '';
        $lightLogo = isset($generalSettings['website_logo_light']) ? ImageHelper::getWebsiteLogo($generalSettings['website_logo_light']) : '';
        $siteName = $generalSettings['website_name'] ?? config('app.name');
        $siteFavicon = $generalSettings['favicon'] ?? null;
        return view('backend.login', compact('notificationSetting', 'darkLogo', 'lightLogo', 'siteName', 'siteFavicon'));
    }

    public function showRegistrationForm()
    {
        $notificationSetting = NotificationSetting::first();
        $generalSettings = GeneralSetting::pluck('value', 'key')->toArray();
        
        $darkLogo = isset($generalSettings['website_logo_dark']) ? ImageHelper::getWebsiteLogo($generalSettings['website_logo_dark']) : '';
        $lightLogo = isset($generalSettings['website_logo_light']) ? ImageHelper::getWebsiteLogo($generalSettings['website_logo_light']) : '';
        
        return view('backend.register', compact('notificationSetting', 'darkLogo', 'lightLogo'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'store_name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 2, // Vendor
            'status' => 0, // Pending activation
            'store_name' => $request->store_name,
            'uqid' => 'VND-' . strtoupper(substr(md5(time()), 0, 8)),
        ]);

        // Notify Admin
        $admin_email = \App\Models\EmailSetting::where('status', 1)->value('mail_from_address') ?? 'admin@ecom.com';
        EmailHelper::send(
            $admin_email,
            'New Seller Registration: ' . $user->store_name,
            'A new seller has registered. <br><br><b>Shop Name:</b> ' . $user->store_name . '<br><b>Email:</b> ' . $user->email
        );
        
        // Send Notification to Admin
        NotificationHelper::notifyAdmins([
            'title' => 'New Seller Registration',
            'message' => 'New seller ' . $user->store_name . ' has registered.',
            'type' => 'system',
            'url' => route('vendors.list'),
            'icon' => 'solar:user-bold-duotone',
            'priority' => 'medium'
        ]);

        // Notify Vendor
        $appUrl = config('app.url');
        EmailHelper::send(
            $user->email,
            'Welcome to ' . config('app.name') . '! You have joined as a vendor',
            '', // Message body handled by template
            'emails.registration',
            [
                'owner_name' => $user->name,
                'store_name' => $user->store_name,
                'login_url'  => $appUrl . '/login',
                'email'      => $user->email,
                'password'   => $request->password
            ]
        );

        // Send Notification to Vendor
        NotificationHelper::send($user, [
            'title' => 'Welcome to ' . config('app.name'),
            'message' => 'Your vendor account for ' . $user->store_name . ' has been successfully created. Please complete your profile.',
            'type' => 'system',
            'url' => route('vendor.dashboard'),
            'icon' => 'solar:confetti-bold-duotone',
            'priority' => 'high'
        ]);

        return redirect()->route('login')->with('success', 'Registration successful. Please login to complete your profile.');
    }

    public function google_login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'uid'   => 'required',
            'name'  => 'nullable',
            'device_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Create new user if not exists
            $name = $request->name ?? explode('@', $request->email)[0];
            $user = User::create([
                'name'        => $name,
                'email'       => $request->email,
                'password'    => Hash::make($request->uid . rand(1000, 9999)), // Random password
                'role'        => "2", // Default to Vendor
                'status'      => 0, // Pending activation
                'store_name'  => $name . "'s Store",
                'uqid'        => 'VND-' . strtoupper(substr(md5(time()), 0, 8)),
                'social_id'   => $request->uid,
                'social_type' => 'google'
            ]);
        } else {
            // Update existing user with social info if not present
            if (!$user->social_id) {
                $user->social_id = $request->uid;
                $user->social_type = 'google';
                $user->save();
            }
        }

        Auth::login($user);

        // Regenerate session for security
        $request->session()->regenerate();

        // Enforce single active session per user
        $sessionId = $request->session()->getId();
        $user->current_session_id = $sessionId;
        
        // 🔹 Store device token if provided
        if ($request->filled('device_token')) {
            $user->device_token = $request->device_token;
        }
        
        $user->save();

        $redirect = route('login');
        if ((string)$user->role === '1') {
            $redirect = route('admin.dashboard');
        } elseif ((string)$user->role === '2') {
            $redirect = route('vendor.dashboard');
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Login successful',
            'redirect' => $redirect
        ]);
    }


    public function facebook_login(Request $request)
    {
        Log::info('Facebook Login Attempt:', [
            'email' => $request->email,
            'uid' => $request->uid,
            'name' => $request->name,
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip()
        ]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'uid'   => 'required|string|min:10',
            'name'  => 'nullable|string|max:255',
            'device_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::error('Facebook Login Validation Failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided: ' . $validator->errors()->first()
            ], 422);
        }

        try {
            // Check if user already exists
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                // Create new user if not exists
                $name = $request->name ?? explode('@', $request->email)[0];
                $name = trim($name); // Clean up the name

                $user = User::create([
                    'name'        => $name,
                    'email'       => $request->email,
                    'password'    => Hash::make($request->uid . rand(1000, 9999)), // Random password
                    'role'        => "2", // Default to Vendor
                    'status'      => 1, // Active by default for social logins
                    'store_name'  => $name . "'s Store",
                    'uqid'        => 'VND-' . strtoupper(substr(md5(time()), 0, 8)),
                    'social_id'   => $request->uid,
                    'social_type' => 'facebook'
                ]);
                Log::info('New Facebook User Created:', [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name
                ]);
            } else {
                // Update existing user with social info if not present
                if (!$user->social_id) {
                    $user->social_id = $request->uid;
                    $user->social_type = 'facebook';
                    $user->save();
                    Log::info('Existing User Linked to Facebook:', ['id' => $user->id]);
                }
            }

            // Check if user is active
            if ((int)$user->status !== 1) {
                Log::warning('Facebook Login Attempt for Inactive User:', ['id' => $user->id, 'status' => $user->status]);
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is not active. Please contact administrator.'
                ], 403);
            }

            Auth::login($user);

            // Regenerate session for security
            $request->session()->regenerate();

            // Enforce single active session per user
            $sessionId = $request->session()->getId();
            $user->current_session_id = $sessionId;
            $user->last_login_at = now();
            
            // 🔹 Store device token if provided
            if ($request->filled('device_token')) {
                $user->device_token = $request->device_token;
            }
            
            $user->save();

            $redirect = route('login');
            if ((string)$user->role === '1') {
                $redirect = route('admin.dashboard');
            } elseif ((string)$user->role === '2') {
                $redirect = route('vendor.dashboard');
            }

            Log::info('Facebook Login Successful:', [
                'user_id' => $user->id,
                'email' => $user->email,
                'redirect' => $redirect
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'Login successful',
                'redirect' => $redirect
            ]);
        } catch (\Exception $e) {
            Log::error('Facebook Login Backend Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An internal error occurred during login. Please try again.'
            ], 500);
        }
    }
    

    public function do_login(Request $request)
    {
        // Validate request
        $request->validate([
            'username' => ['required', 'email', 'max:255'],
            'password' => ['required'],
            'device_token' => ['nullable', 'string'],
            'device_type' => ['nullable', 'string', 'max:20'],
        ]);

        // Check if user exists
        $user = User::where('email', $request->username)->first();

        if (!$user) {
            if ($request->ajax()) {
                return response()->json(['status' => false, 'message' => 'No account found with this email address.'], 404);
            }
            return back()
                ->withInput()
                ->with('error', 'No account found with this email address.');
        }

        // Check password
       if (!Auth::attempt([
            'email' => $request->username,
            'password' => $request->password
        ])) {
            if ($request->ajax()) {
                return response()->json(['status' => false, 'message' => 'Invalid credentials'], 401);
            }
            return back()->with('error', 'Invalid credentials');
        }

        $request->session()->regenerate();
        $user = Auth::user();
        
        // 🔹 Store device token and device type using NotificationHelper
        if ($request->filled('device_token')) {
            NotificationHelper::updateDeviceToken($user, $request->device_token, $request->device_type ?? 'web');
        } else {
            $user->device_type = 'web';
            $user->save();
        }
        
        // Update last login
        $user->last_login_at = now();
        $user->save();
        
        // Attempt login
        Auth::login($user);

        // Regenerate session for security
        $request->session()->regenerate();

        // Enforce single active session per user
        $sessionId = $request->session()->getId();
        $user->current_session_id = $sessionId;
        $user->save();

        // Role-based redirect
        if ((string)$user->role === '1') {
            if ($request->ajax()) {
                return response()->json(['status' => true, 'redirect' => url('admin-dashboard')]);
            }
            return redirect('admin-dashboard')->with('success', 'Login successful');
        }

        if ((string)$user->role === '2') {
            // Check if vendor is blocked
            if ($user->status == 3) {
                Auth::logout();
                if ($request->ajax()) {
                    return response()->json(['status' => false, 'message' => 'Your account has been blocked. Please contact the administrator.'], 403);
                }
                return redirect()->route('login')
                    ->withInput()
                    ->with('error', 'Your account has been blocked. Please contact the administrator.');
            }
            
            // Check if vendor is rejected
            if ($user->status == 2) {
                Auth::logout();
                if ($request->ajax()) {
                    return response()->json(['status' => false, 'message' => 'Your account application has been rejected. Please contact the administrator.'], 403);
                }
                return redirect()->route('login')
                    ->withInput()
                    ->with('error', 'Your account application has been rejected. Please contact the administrator.');
            }

            // Check if vendor is verified or pending
            if ($user->status == 1 || $user->status == 0) {
                if ($request->ajax()) {
                    return response()->json(['status' => true, 'redirect' => url('vendor-dashboard')]);
                }
                return redirect('vendor-dashboard')->with('success', 'Login successful');
            } else {
                Auth::logout();
                if ($request->ajax()) {
                    return response()->json(['status' => false, 'message' => 'Your account is pending verification. Please contact the administrator.'], 403);
                }
                return redirect()->route('login')
                    ->withInput()
                    ->with('error', 'Your account is pending verification. Please contact the administrator.');
            }
        }

        Auth::logout();
        if ($request->ajax()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized access.'], 403);
        }
        return back()->with('error', 'Unauthorized access.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $currentSessionId = $request->session()->getId();

        if ($user && $user->current_session_id === $currentSessionId) {
            $user->current_session_id = null;
            $user->save();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');

       
       
    }
}
