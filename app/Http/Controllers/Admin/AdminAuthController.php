<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Helpers\ImageHelper;
use App\Helpers\EmailHelper;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;



class AdminAuthController extends Controller
{


    public function admin_change_password(Request $request)
    {
        return view('backend/admin/setting/admin-change-password');
    }


    public function admin_profile(Request $request)
    {
        $admin_id = Auth::user()->id;;
        $admin_info = User::where('id', $admin_id)->first();
        $admin_info->image = ImageHelper::getProfileImage($admin_info->image);
        return view('backend/admin/setting/admin-profile', compact('admin_info'));
    }


    public function forgot_password(Request $request)
    {
        $generalSettings = GeneralSetting::pluck('value', 'key')->toArray();
        
        $darkLogo = isset($generalSettings['website_logo_dark']) ? ImageHelper::getWebsiteLogo($generalSettings['website_logo_dark']) : '';
        $lightLogo = isset($generalSettings['website_logo_light']) ? ImageHelper::getWebsiteLogo($generalSettings['website_logo_light']) : '';
        // echo '<pre>';print_r($generalSettings);die;
        return view('backend/admin/auth/forgot-password', compact('darkLogo', 'lightLogo'));
    }


    public function send_otp(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $request->username)->first();
            if (!$user) {
                return redirect()->back()->with('error', 'User not found.');
            }

            $otp = rand(100000, 999999);
            
            $user->otp = $otp;
            $user->save();

            $subject = 'Verification Code - ' . config('app.name');
            
            Log::info('Sending OTP email to: ' . $user->email, ['otp' => $otp]);
            
            $email_sent = EmailHelper::send(
                $user->email, 
                $subject, 
                '',
                'emails.otp', 
                [
                    'otp' => $otp,
                    'user_name' => $user->name ?? 'User'
                ]
            );

            Log::info('Email send result: ' . ($email_sent ? 'success' : 'failed'));

            if (!$email_sent) {
                return redirect()->back()->with('error', 'Failed to send OTP. Please try again later.');
            }

            session()->put('reset_email', $request->username);
            session()->save();

            Log::info('Redirecting to verify.otp.form with email: ' . $request->username);

            return redirect()->route('verify.otp.form')->with('success', 'A 6-digit OTP has been sent to ' . $request->username);
        } catch (\Exception $e) {
            Log::error('send_otp error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function verify_otp_form(Request $request)
    {
        $generalSettings = GeneralSetting::pluck('value', 'key')->toArray();
        
        $darkLogo = isset($generalSettings['site_logo_dark']) ? ImageHelper::getWebsiteLogo($generalSettings['site_logo_dark']) : '';
        $lightLogo = isset($generalSettings['site_logo_light']) ? ImageHelper::getWebsiteLogo($generalSettings['site_logo_light']) : '';
        
        return view('backend/admin/auth/verify-otp-form', compact('darkLogo', 'lightLogo'));
    }

    public function otp_match(Request $request)
    {
        $request->validate([
            // 'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:6',
        ]);

        $email = $request->session()->get('reset_email');


        $user = User::where('email', $email)
            ->where('otp', $request->otp)
            ->first();

        if (!$user) {
            return back()->withErrors([
                'otp' => 'OTP does not match.'
            ]);
        }

        // OTP matched successfully
        return redirect()->route('reset.password.form')
            ->with('email', $request->email)
            ->with('success', 'OTP verified successfully');
    }

    public function reset_password_form(Request $request)
    {
        $generalSettings = GeneralSetting::pluck('value', 'key')->toArray();
        
        $darkLogo = isset($generalSettings['site_logo_dark']) ? ImageHelper::getWebsiteLogo($generalSettings['site_logo_dark']) : '';
        $lightLogo = isset($generalSettings['site_logo_light']) ? ImageHelper::getWebsiteLogo($generalSettings['site_logo_light']) : '';
        
        return view('backend/admin/auth/reset-password-form', compact('darkLogo', 'lightLogo'));
    }

    public function reset_password(Request $request)
    {
        // Validate inputs
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        // Get email from session
        $email = session('reset_email');

        if (!$email) {
            return redirect()->route('forgot.password')
                ->with('error', 'Session expired. Please request OTP again.');
        }

        // Find user with matching email & OTP
        $user = User::where('email', $email)
            ->first();


        // Update password & clear OTP
        $user->update([
            'password' => Hash::make($request->password),
            'otp' => null,
        ]);

        // Send confirmation email
        $subject = 'Password Changed Successfully - ' . config('app.name');
        EmailHelper::send(
            $user->email,
            $subject,
            '', // Message body handled by template
            'emails.password-reset-success',
            [
                'user_name' => $user->name ?? 'User'
            ]
        );

        // Clear session
        session()->forget('reset_email');

        return redirect()->route('login')
            ->with('success', 'Password reset successfully. Please login.');
    }
    public function admin_profile_update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::user()->id,
            'phone' => 'nullable|numeric|digits_between:1,12',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'address' => 'required|string',
        ]);

        try {
            $admin = User::findOrFail(Auth::user()->id);
            $admin->name = $request->name;
            $admin->email = $request->email;
            $admin->phone = $request->phone;
            $admin->address = $request->address;

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $fileName = ImageHelper::compressImage($request->file('image'), '/uploads/images/profile/');

                $destination = public_path('/uploads/images/profile/');
                // Delete old image if exists
                if ($admin->image && file_exists($destination . DIRECTORY_SEPARATOR . $admin->image)) {
                    @unlink($destination . DIRECTORY_SEPARATOR . $admin->image);
                }

                $admin->image = $fileName;
            }

            $admin->save();

            return redirect('admin-profile')->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            return redirect('admin-profile')->with('error', 'Profile update failed: ' . $e->getMessage());
        }
    }

    public function admin_update_password(Request $request)
    {
        $id = Auth::user()->id;;
        $admin = User::findOrFail($id);

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if (Hash::check($request->current_password, $admin->password)) {
            $admin->password = Hash::make($request->new_password);
            $admin->save();

            // Send confirmation email
            $subject = 'Security Notification: Password Changed - ' . config('app.name');
            EmailHelper::send(
                $admin->email,
                $subject,
                '', // Message body handled by template
                'emails.password-reset-success',
                [
                    'user_name' => $admin->name ?? 'User'
                ]
            );

            return back()->with('success', 'Password updated successfully.');
        } else {
            return back()->with('error', 'Current password does not match.');
        }
    }
    
}
