<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ImageHelper;
use App\Models\KYC_Document;
use App\Models\UserCard;
use App\Models\Product;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\VendorsDocument;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Helpers\EmailHelper;
use App\Helpers\ReferralHelper;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;
use App\Helpers\SparrowSmsHelper;
use Illuminate\Support\Facades\Log;
use App\Models\ProductReview;




class UserController extends Controller
{
   public function signup(Request $request)
    {
        try {
            $inputs = $request->all();
            
            $rules = [
                'name'           => 'required|string|max:255',
                'email_or_phone' => 'required',
                'password'       => 'required|min:6',
            ];

            $validator = Validator::make($inputs, $rules);
            
            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return response(['status' => false, 'message' => $errors[0]], 200);
            }

            // 1. Initialize User Object
            $user = new User;
            $user->uqid = 'USER-' . strtoupper(uniqid());
            $user->role = '3';
            $user->name = $inputs['name'];

            // 2. Handle Email or Phone Logic
            $email_or_phone = $inputs['email_or_phone'];
            if (filter_var($email_or_phone, FILTER_VALIDATE_EMAIL)) {
                if (User::where('email', $email_or_phone)->exists()) {
                    return response(['status' => false, 'message' => __('messages.email_already_registered')], 200);
                }
                $user->email = $email_or_phone;
            } else {
                if (User::where('phone', $email_or_phone)->exists()) {
                    return response(['status' => false, 'message' => __('messages.mobile_already_registered')], 200);
                }
                $user->phone = $email_or_phone;
            }

            $user->password = Hash::make($inputs['password']);
            
            if (!empty($inputs['device_type'])) {
                $user->device_type = $inputs['device_type'];
            }
            if (!empty($inputs['device_token'])) {
                $user->device_token = $inputs['device_token'];
            }

            // 3. Save User
            $user->save();

            // 3.1 Send OTP for verification
            $otp = random_int(100000, 999999);
            $user->otp = $otp;
            $user->save();

            $message = 'Your verification OTP is ' . $otp;
            $isEmail = filter_var($email_or_phone, FILTER_VALIDATE_EMAIL);

            if ($isEmail) {
                EmailHelper::send($user->email, 'Account Verification OTP', $message);
            } else {
                $phone = $user->phone;
                // Format phone for Sparrow SMS (ensure +977 prefix)
                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (strlen($phone) == 10) {
                    $phone = '977' . $phone;
                }
                if (substr($phone, 0, 3) !== '977' && strlen($phone) == 10) {
                     $phone = '977' . $phone;
                }
                // Many SMS gateways prefer without the + or with it, 
                // but the previous code used +977. Let's follow that.
                if (substr($phone, 0, 1) !== '+') {
                    $phone = '+' . $phone;
                }
                SparrowSmsHelper::send($phone, $message);
            }

            // 4. Generate Referral Code (Self)
            if (empty($user->referral_code)) {
                $user->referral_code = 'REF' . $user->id;
                $user->save();
            }

            // 5. Handle Being Referred by Someone Else
            if (!empty($inputs['referral_code'])) {
                $referrer = User::where('referral_code', $inputs['referral_code'])->first();
                if ($referrer && $referrer->id !== $user->id) {
                    // Note: Ensure ReferralHelper class exists and is imported
                    if (class_exists('ReferralHelper')) {
                        $canUse = ReferralHelper::canUseReferralCode($user->email ?? null, $user->phone ?? null);
                        if ($canUse) {
                            $user->referred_by = $referrer->id;
                            $user->save();
                        }
                    }
                }
            }

            // 6. Sync Guest Data (Cart/Wishlist)
            if ($request->filled('ip_address')) {
                $this->syncGuestData($user, $request->ip_address);
            }

            // 7. Add notification status fields to response
            $user->is_order_update_active = (bool)$user->is_order_update_active;
            $user->is_promotional_email_active = (bool)$user->is_promotional_email_active;
            $user->is_newsletter_active = (bool)$user->is_newsletter_active;

            return response([
                'status' => true, 
                'message' => __('messages.registration_success'), 
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            // Log the error for your own records
            Log::error("Signup Error: " . $e->getMessage());

            // Return the actual error message to the frontend for debugging
            return response([
                'status' => false, 
                'message' => 'Exception Caught: ' . $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

        public function userlogin(Request $request)
        {
            // 🔹 Validate input
            $validator = Validator::make($request->all(), [
                'email_or_phone' => 'required',
                'password'       => 'required',
                'device_token'   => 'nullable|string',
                'device_type'    => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $emailOrPhone = $request->email_or_phone;
            $password     = $request->password;

            // 🔹 Find user by email OR phone
            $user = filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL)
                ? User::where('email', $emailOrPhone)->first()
                : User::where('phone', $emailOrPhone)->first();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.user_not_found'),
                ], 404);
            }

            // 🔹 Check password
            if (!Hash::check($password, $user->password)) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.invalid_credentials'),
                ], 401);
            }

            // 🔹 Store device token and type using NotificationHelper
            if ($request->filled('device_token')) {
                NotificationHelper::updateDeviceToken($user, $request->device_token, $request->device_type ?? 'api');
            }



            // 🔹 Update guest cart and wishlist to user_id
            if ($request->filled('ip_address')) {
                $this->syncGuestData($user, $request->ip_address);
            }

            // 🔹 Create auth token
            $token = $user->createToken('auth_token')->plainTextToken;

            // 🔹 Convert numeric role → string (FOR RESPONSE ONLY)
            $role = match ((int)$user->role) {
                2 => 'vendor',
                1 => 'admin',
                3 => 'user',
            };

            // 🔹 Profile image
            $user->image = ImageHelper::getUserImage($user->image);

            // 🔹 Add role string to response object
            $user->role_name = $role; // ✅ SAFE

            // 🔹 Add notification status
            $user->is_order_update_active = (bool)$user->is_order_update_active;
            $user->is_promotional_email_active = (bool)$user->is_promotional_email_active;
            $user->is_newsletter_active = (bool)$user->is_newsletter_active;

            // 🔹 FCM details
            $notificationSetting = \App\Models\NotificationSetting::first();
            $fcm_config = null;
            if ($notificationSetting && $notificationSetting->status) {
                $fcm_config = [
                    'api_key' => $notificationSetting->firebase_api_key ?? '',
                    'auth_domain' => $notificationSetting->firebase_auth_domain ?? '',
                    'project_id' => $notificationSetting->firebase_project_id ?? '',
                    'storage_bucket' => $notificationSetting->firebase_storage_bucket ?? '',
                    'messaging_sender_id' => $notificationSetting->firebase_messaging_sender_id ?? '',
                    'app_id' => $notificationSetting->firebase_app_id ?? '',
                    'vapid_key' => $notificationSetting->fcm_vapid_key ?? '',
                    'measurementId' => $notificationSetting->measurementId ?? '',
                ];
            }

            return response()->json([
                'status'  => true,
                'message' => __('messages.login_success'),
                'token'   => $token,
                'data'    => $user,
                'fcm_config' => $fcm_config
            ], 200);
        }

        public function social_login(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'name'         => 'required',
                'email'        => 'required|email',
                'social_id'    => 'required',
                'social_type'  => 'required|in:google,facebook',
                'device_token' => 'nullable',
                'device_type'  => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $user = User::where('social_id', $request->social_id)
                ->where('social_type', $request->social_type)
                ->first();

            if (!$user) {
                // Check if user exists with same email
                $user = User::where('email', $request->email)->first();

                if ($user) {
                    // Link social account to existing email
                    $user->social_id = $request->social_id;
                    $user->social_type = $request->social_type;
                    $user->save();
                } else {
                    // Create new user
                    $user = User::create([
                        'uqid'        => 'USER-' . strtoupper(uniqid()),
                        'role'        => '3', // customer
                        'name'        => $request->name,
                        'email'       => $request->email,
                        'password'    => Hash::make(Str::random(16)),
                        'social_id'   => $request->social_id,
                        'social_type' => $request->social_type,
                        'device_token'=> $request->device_token,
                        'device_type' => $request->device_type,
                        'status'      => 1,
                    
                    ]);

                    // Generate referral code
                    $user->referral_code = 'REF' . $user->id;
                    $user->save();
                }
            }

            // Update device token if provided
            if ($request->filled('device_token')) {
                $user->device_token = $request->device_token;
                if ($request->filled('device_type')) {
                    $user->device_type = $request->device_type;
                }
                $user->save();
            }

            // Update guest cart and wishlist
            if ($request->filled('ip_address')) {
                $this->syncGuestData($user, $request->ip_address);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            // Profile image
            $user->image = ImageHelper::getUserImage($user->image);
            $user->role_name = 'user';

            // 🔹 Add notification status
            $user->is_order_update_active = (bool)$user->is_order_update_active;
            $user->is_promotional_email_active = (bool)$user->is_promotional_email_active;
            $user->is_newsletter_active = (bool)$user->is_newsletter_active;

            // 🔹 FCM details
            $notificationSetting = \App\Models\NotificationSetting::first();
            $fcm_config = null;
            if ($notificationSetting && $notificationSetting->status) {
                $fcm_config = [
                    'api_key' => $notificationSetting->firebase_api_key ?? '',
                    'auth_domain' => $notificationSetting->firebase_auth_domain ?? '',
                    'project_id' => $notificationSetting->firebase_project_id ?? '',
                    'storage_bucket' => $notificationSetting->firebase_storage_bucket ?? '',
                    'messaging_sender_id' => $notificationSetting->firebase_messaging_sender_id ?? '',
                    'app_id' => $notificationSetting->firebase_app_id ?? '',
                    'vapid_key' => $notificationSetting->fcm_vapid_key ?? '',
                    'measurementId' => $notificationSetting->measurementId ?? '',
                ];
            }

            return response()->json([
                'status'  => true,
                'message' => __('messages.login_success'),
                'token'   => $token,
                'data'    => $user,
                'fcm_config' => $fcm_config
            ], 200);
        }

        public function facebook_login(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'name'         => 'required',
                'email'        => 'required|email',
                'social_id'    => 'required', // Facebook User ID
                'device_token' => 'nullable',
                'device_type'  => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $user = User::where('social_id', $request->social_id)
                ->where('social_type', 'facebook')
                ->first();

            if (!$user) {
                // Check if user exists with same email
                $user = User::where('email', $request->email)->first();

                if ($user) {
                    // Link social account to existing email
                    $user->social_id = $request->social_id;
                    $user->social_type = 'facebook';
                    $user->save();
                } else {
                    // Create new user
                    $user = User::create([
                        'uqid'        => 'USER-' . strtoupper(uniqid()),
                        'role'        => '3', // customer
                        'name'        => $request->name,
                        'email'       => $request->email,
                        'password'    => Hash::make(Str::random(16)),
                        'social_id'   => $request->social_id,
                        'social_type' => 'facebook',
                        'device_token'=> $request->device_token,
                        'device_type' => $request->device_type,
                        'status'      => 1,
                    ]);

                    // Generate referral code
                    $user->referral_code = 'REF' . $user->id;
                    $user->save();
                }
            }

            // Update device token if provided
            if ($request->filled('device_token')) {
                $user->device_token = $request->device_token;
                if ($request->filled('device_type')) {
                    $user->device_type = $request->device_type;
                }
                $user->save();
            }

            // Update guest cart and wishlist
            if ($request->filled('ip_address')) {
                $this->syncGuestData($user, $request->ip_address);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            // Profile image
            $user->image = ImageHelper::getUserImage($user->image);
            $user->role_name = 'user';

            // 🔹 Add notification status
            $user->is_order_update_active = (bool)$user->is_order_update_active;
            $user->is_promotional_email_active = (bool)$user->is_promotional_email_active;
            $user->is_newsletter_active = (bool)$user->is_newsletter_active;

            // 🔹 FCM details
            $notificationSetting = \App\Models\NotificationSetting::first();
            $fcm_config = null;
            if ($notificationSetting && $notificationSetting->status) {
                $fcm_config = [
                    'api_key' => $notificationSetting->firebase_api_key ?? '',
                    'auth_domain' => $notificationSetting->firebase_auth_domain ?? '',
                    'project_id' => $notificationSetting->firebase_project_id ?? '',
                    'storage_bucket' => $notificationSetting->firebase_storage_bucket ?? '',
                    'messaging_sender_id' => $notificationSetting->firebase_messaging_sender_id ?? '',
                    'app_id' => $notificationSetting->firebase_app_id ?? '',
                    'vapid_key' => $notificationSetting->fcm_vapid_key ?? '',
                    'measurementId' => $notificationSetting->measurementId ?? '',
                ];
            }

            return response()->json([
                'status'  => true,
                'message' => __('messages.login_success'),
                'token'   => $token,
                'data'    => $user,
                'fcm_config' => $fcm_config
            ], 200);
        }


        public function logout(Request $request)
        {
            // $user = $request->user();
            $inputs = $request->all();
            $user = $inputs['user_id'] ?? null;
            if ($user) {
                User::where('id', $user)->update(['device_token' => null]);
            }
            return response()->json([
                'status' => true,
                'message' => 'logout_success'
            ]);
        }

        public function forgot_password(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'email_or_phone' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
            }
            $identifier = $request->email_or_phone;
            $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
            
            $user = $isEmail
                ? User::where('email', $identifier)->first()
                : User::where('phone', $identifier)->first();

            if (!$user) {
                return response()->json(['status' => false, 'message' => __('messages.user_not_found')], 404);
            }

            $otp = random_int(100000, 999999);
            $user->otp = $otp;
            $user->save();

            $message = 'Your OTP is ' . $otp;

            if ($isEmail) {
                $to = $user->email;
                $subject = 'Password Reset OTP';
                
                try {
                    $result = EmailHelper::sendWithReason($to, $subject, $message);
                    
                    if (!($result['success'] ?? false)) {
                        // Log the specific error but don't crash the API
                        Log::error('Forgot Password Email Failed: ' . ($result['error'] ?? 'Unknown error'));
                        
                        // Fallback: If email fails, we still returned success if OTP was saved, 
                        // but since OTP is the only way to reset, we should inform the user or try another way.
                        // However, per user request, we just want to "fix" the 554 error crashing things.
                        return response()->json([
                            'status' => false,
                            'message' => 'Email service is currently unavailable. Please try again later or contact support.',
                            'debug_error' => $result['error'] ?? null // Optional: remove in production
                        ], 503);
                    }
                } catch (\Exception $e) {
                    Log::error('Forgot Password Exception: ' . $e->getMessage());
                    return response()->json([
                        'status' => false,
                        'message' => 'An unexpected error occurred while sending the email.',
                    ], 500);
                }
            } else {
                $to = $user->phone;
                // Format phone for Sparrow SMS (ensure +977 prefix)
                $to = preg_replace('/[^0-9]/', '', $to);
                if (strlen($to) == 10) {
                    $to = '977' . $to;
                }
                if (substr($to, 0, 1) !== '+') {
                    $to = '+' . $to;
                }

            
                $smsResult = SparrowSmsHelper::send($to, $message);
                
                if (!($smsResult['status'] ?? false)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to send OTP SMS',
                        'error' => $smsResult['response'] ?? 'Unknown error'
                    ], 500);
                }
            }

            return response()->json(['status' => true, 'message' => 'OTP sent successfully']);
        }

        public function verify_otp(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'email_or_phone' => 'required',
                'otp' => 'required|digits:6',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
            }
            $identifier = $request->email_or_phone;
            $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
                ? User::where('email', $identifier)->first()
                : User::where('phone', $identifier)->first();
            if (!$user) {
                return response()->json(['status' => false, 'message' => __('messages.user_not_found')], 404);
            }
            if ((string)($user->otp ?? '') !== (string)$request->otp) {
                return response()->json(['status' => false, 'message' => 'Invalid OTP'], 400);
            }
            return response()->json(['status' => true, 'message' => 'OTP verified']);
        }

    public function reset_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_or_phone' => 'required',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $identifier = $request->email_or_phone;
        $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? User::where('email', $identifier)->first()
            : User::where('phone', $identifier)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => __('messages.user_not_found')], 404);
        }
        
        $user->password = Hash::make($request->password);
        $user->otp = null;
        $user->save();
        return response()->json(['status' => true, 'message' => 'Password reset successfully']);
    }

    public function change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'current_password' => 'required',
            'new_password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $user = User::find($request->user_id);
        if (!$user || !Hash::check($request->current_password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Current password is incorrect'], 400);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        return response()->json(['status' => true, 'message' => 'Password updated successfully']);
    }

    public function updateNotificationStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
          
            'is_order_update_active' => 'nullable|boolean',
            'is_promotional_email_active' => 'nullable|boolean',
            'is_newsletter_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::find($request->user_id);
        
        
        if ($request->has('is_order_update_active')) {
            $user->is_order_update_active = $request->is_order_update_active;
        }
        if ($request->has('is_promotional_email_active')) {
            $user->is_promotional_email_active = $request->is_promotional_email_active;
        }
        if ($request->has('is_newsletter_active')) {
            $user->is_newsletter_active = $request->is_newsletter_active;
        }
        
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Notification status updated successfully',
            'data' => [
               
                'is_order_update_active' => (bool)$user->is_order_update_active,
                'is_promotional_email_active' => (bool)$user->is_promotional_email_active,
                'is_newsletter_active' => (bool)$user->is_newsletter_active,
            ]
        ], 200);
    }

    public function getNotificationStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::find($request->user_id);

        return response()->json([
            'status' => true,
            'message' => 'Notification status fetched successfully',
            'data' => [
            
                'is_order_update_active' => (bool)$user->is_order_update_active,
                'is_promotional_email_active' => (bool)$user->is_promotional_email_active,
                'is_newsletter_active' => (bool)$user->is_newsletter_active,
            ]
        ], 200);
    }




 public function seller_registration(Request $request)
{
    DB::beginTransaction();

    try {

        // ================= VALIDATION =================
        $request->validate([
            'owner_name'           => 'required|string|max:255',
            'store_name'           => 'required|string|max:255',
            'business_name'        => 'required|string|max:255',
            'email'                => 'required|email|unique:users,email',
            'phone'                => 'required|string|max:20',
            'password'             => 'required|confirmed|min:6',
            'address'              => 'required|string',
            'country_id'           => 'required|integer',
            'state_id'             => 'required|integer',
            'city_id'              => 'nullable|integer',
            'zip'                  => 'required|string|max:20',
            'logo'                 => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'document_id'          => 'required|string', // "1,2,3"
            'document'             => 'required',
            'document.*'           => 'file|mimes:jpg,jpeg,png,pdf|max:4096',
            'pan_no'               => 'nullable|string|max:100',
            'vendor_tax'           => 'nullable|string',
            'bank_name'            => 'nullable|string|max:255',
            'account_number'       => 'nullable|string|max:100',
            'account_holder_name'  => 'nullable|string|max:255',
            'category_ids'         => 'required',
            'agreement'           => 'required',
        ]);

        $inputs = $request->all();

        // ================= UPLOAD LOGO =================
        $logoName = null;
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time().'_logo.'.$logo->getClientOriginalExtension();
            $logo->move(public_path('/uploads/vendors/'), $logoName);
        }

        // ================= GENERATE UQID =================
        $uqid = 'Seller-' . strtoupper(uniqid());

        // ================= HANDLE CATEGORY IDS =================

            $categoryIds = $request->category_ids;

            if (is_string($categoryIds)) {
                $categoryIds = explode(',', $categoryIds);
            }
          
        // ================= CREATE SELLER =================
        $vendor = User::create([
            'name'            => $inputs['owner_name'],
            'uqid'            => $uqid,
            'store_name'      => $inputs['store_name'],
            'email'           => $inputs['email'],
            'phone'           => $inputs['phone'],
            'password'        => Hash::make($inputs['password']),
            'address'         => $inputs['address'],
            'city_id'         => $inputs['city_id'] ?? null,
            'state_id'        => $inputs['state_id'],
            'country_id'      => $inputs['country_id'],
            'zip'             => $inputs['zip'],
            'business_name'   => $inputs['business_name'],
            'pan_no'          => $inputs['pan_no'] ?? null,
            'vendor_tax'          => $inputs['vat_or_tax'] ?? null,
            'bank_name'       => $inputs['bank_name'] ?? null,
            'account_holder_name'  => $inputs['account_holder_name'] ?? null,
            'account_number'  => $inputs['account_number'] ?? null,
            'branch_name'     => $inputs['branch_name'] ?? null,
            'role'            => '2',
            'image'           => $logoName,
            'from_web'        => '1',
            'status'          => 0,
            'agreement'       => 1,
            'agreement_id'    => $inputs['agreement_id'],   
            'category_ids'    => $categoryIds, 
        ]);



        // ================= SAVE DOCUMENTS =================
        if ($request->hasFile('document')) {

            $documentIds = $request->document_id;
            if (is_string($documentIds)) {
                // Keep empty values to preserve index positioning for exactly 5 docs
                $documentIds = explode(',', $documentIds);
            }

            $documentNumbers = $request->document_number;
            if (is_string($documentNumbers)) {
                $documentNumbers = explode(',', $documentNumbers);
            }
            
            $documents = $request->file('document');
            if (!is_array($documents)) {
                $documents = [$documents];
            }
            foreach ($documents as $index => $file) {
                if ($file->isValid()) {
                    $fileName = time() . '_' . $index . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('/uploads/vendors/documents'), $fileName);

                    VendorsDocument::create([
                        'vendor_id'       => $vendor->id,
                        'document_id'     => isset($documentIds[$index]) ? trim($documentIds[$index]) : null,
                        'document_number' => isset($documentNumbers[$index]) ? trim($documentNumbers[$index]) : null,
                        'document'        => $fileName,
                    ]);
                }
            }
        }

        DB::commit();

        // ================= NOTIFY ADMIN & SELLER =================
        try {
            $admin_email = \App\Models\EmailSetting::where('status', 1)->value('mail_from_address') ?? 'admin@ecom.com';
            
            // Notify Admin using common template
            $admin_result = EmailHelper::send($admin_email, 'New Seller Registration: ' . $inputs['store_name'], 'A new seller has registered via API.<br><br><b>Shop Name:</b> ' . $inputs['store_name'] . '<br><b>Email:</b> ' . $inputs['email'], 'emails.common', [
                'action_url' => url('/vendors-list'),
                'action_text' => 'Review New Vendor'
            ]);

            if (!$admin_result) {
                Log::warning('Admin notification email failed for store: ' . $inputs['store_name']);
            }

            // ✅ Notify Seller with Credentials and Login URL using registration template
            $loginUrl = url('/login');
            $seller_email = $inputs['email'];
            $password = $inputs['password'];

            $seller_result = EmailHelper::send($seller_email, 'Welcome to ' . config('app.name') . '! You have joined as a vendor', '', 'emails.registration', [
                'owner_name' => $inputs['owner_name'],
                'store_name' => $inputs['store_name'],
                'login_url'  => $loginUrl,
                'email'      => $seller_email,
                'password'   => $password
            ]);

            if (!$seller_result) {
                Log::error('Seller registration email failed for email: ' . $seller_email);
            }

            // ================= SEND SYSTEM NOTIFICATIONS =================

            // Notify Admin
            NotificationHelper::notifyAdmins([
                'title' => 'New Seller Registration (Website)',
                'message' => 'New seller ' . $vendor->store_name . ' has registered via Website.',
                'type' => 'system',
                'url' => url('/vendors-list'),
                'icon' => 'solar:user-bold-duotone',
                'priority' => 'medium'
            ]);

            // Notify Seller
            NotificationHelper::send($vendor, [
                'title' => 'Welcome to ' . config('app.name'),
                'message' => 'Your vendor account for ' . $vendor->store_name . ' has been successfully created. Please complete your profile.',
                'type' => 'system',
                'url' => route('vendor.dashboard'),
                'icon' => 'solar:confetti-bold-duotone',
                'priority' => 'high'
            ]);
        } catch (\Exception $e) {
            // Log email error but don't fail registration
            Log::error('Registration Email Exception: ' . $e->getMessage());
        }

        return response()->json([
            'status'  => true,
            'message' => 'Seller registered successfully',
            'data'    => $vendor
        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status'  => false,
            'message' => $e->getMessage()
        ], 500);
    }
}



    public function update_kyc_documents(Request $request)
    {
        // ✅ Validate required inputs
        $validator = Validator::make($request->all(), [
            'vendor_id'   => 'required|integer',
            'document_id' => 'nullable',
            'document'    => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.validation_error'),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $vendorId   = $request->vendor_id;

        // ✅ Delete selected documents (by ID or filename)
        $toDelete = $request->input('delete_documents', []);

        if (!empty($toDelete)) {
            foreach ($toDelete as $del) {

                $doc = is_numeric($del)
                    ? VendorsDocument::where('id', $del)->where('vendor_id', $vendorId)->first()
                    : VendorsDocument::where('vendor_id', $vendorId)
                    ->where('document', $del)
                    ->first();

                if ($doc) {
                    // delete physical file
                    if (!preg_match('/^https?:\/\//', $doc->document)) {
                        $path = public_path('vendors/documents/' . basename($doc->document));
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                    }
                    $doc->delete();
                }
            }
        }

        // ✅ Upload new documents (Handle Arrays or Comma-Separated)
        if ($request->hasFile('document')) {
            $files = $request->file('document');
            if (!is_array($files)) {
                $files = [$files];
            }

            $documentIds = $request->input('document_id', []);
            if (is_string($documentIds)) {
                $documentIds = array_values(array_filter(explode(',', $documentIds)));
            }

            // ✅ Validate counts match
            if (count($files) !== count($documentIds)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Document count (' . count($files) . ') does not match Document ID count (' . count($documentIds) . ').',
                ], 200);
            }

            foreach ($files as $key => $file) {
                if ($file->isValid()) {
                    $fileName = time() . '_' . rand(100, 999) . '_' .
                        preg_replace('/[^A-Za-z0-9_.-]/', '_', $file->getClientOriginalName());

                    $file->move(public_path('vendors/documents'), $fileName);

                    VendorsDocument::create([
                        'vendor_id'   => $vendorId,
                        'document_id' => $documentIds[$key] ?? null,
                        'document'    => $fileName,
                    ]);
                }
            }
        }

        return response()->json([
            'status'  => true,
            'message' => __('messages.documents_updated_success'),
        ]);
    }




    public function get_my_documents(Request $request)
    {
        // ✅ Validate request
        $request->validate([
            'vendor_id' => 'required|exists:users,id'
        ]);

        $vendorDocuments = VendorsDocument::join(
            'kyc_documents',
            'vendors_document.document_id',
            '=',
            'kyc_documents.id'
        )
            ->where('vendors_document.vendor_id', $request->vendor_id)
            ->select(
                'vendors_document.id',
                'vendors_document.vendor_id',
                'vendors_document.document_id',
                'vendors_document.document',
                'vendors_document.is_verify',
                'kyc_documents.name as document_name'
            )
            ->get()
            ->map(function ($item) {
                $item->document = ImageHelper::getVendorDocImage($item->document);
                return $item;
            });

        return response()->json([
            'status'  => true,
            'message' => 'vendor_documents_success',
            'data'    => $vendorDocuments
        ], 200);
    }




  public function my_profile(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id'
    ]);

    $user = User::with(['country', 'state', 'city'])
        ->find($request->user_id);

    if (!$user) {
        return response()->json([
            'status'  => false,
            'message' => 'User not found'
        ], 404);
    }

    // 🖼 Profile image full URL
    $profileImage = ImageHelper::getUserImage($user->image);
    return response()->json([
        'status'  => true,
        'message' => __('messages.profile_fetched_successfully'),
        'data'    => [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'dob'        => $user->dob, // keep as stored or format if needed
            'image'      => $profileImage, // ✅ added
            'country'    => $user->country->name ?? null,
            'state'      => $user->state->name ?? null,
            'city'       => $user->city->name ?? null,
            'gender'       => $user->gender ?? null,

            // optional IDs (useful for edit profile)
            'country_id' => $user->country_id,
            'address' => $user->address,
            'zip_code' => $user->zip,
            'state_id'   => $user->state_id,
            'city_id'    => $user->city_id,
            // Referral code for sharing
            'referral_code' => $user->referral_code ?? ('REF' . $user->id),
            // Notification status
            'is_order_update_active' => (bool)$user->is_order_update_active,
            'is_promotional_email_active' => (bool)$user->is_promotional_email_active,
            'is_newsletter_active' => (bool)$user->is_newsletter_active,
        ]
    ], 200);
}

    /**
     * Get referral code and share details for customers.
     * Use for: Profile/Referral screen, copy code, share link, WhatsApp, etc.
     */
    public function get_referral_details(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $code = $user->referral_code ?? ('REF' . $user->id);
        $referredReward = ReferralHelper::getReferredReward();
        $baseUrl = config('app.url', url('/'));
        $shareLink = rtrim($baseUrl, '/') . '/signup?ref=' . $code;
        $shareMessage = "Use my referral code {$code} and get INR {$referredReward} on your first order (min INR 1000)! Sign up: {$shareLink}";

        return response()->json([
            'status' => true,
            'message' => 'Referral details for share and copy',
            'data' => [
                'referral_code' => $code,
                'copy_text' => $code,
                'share_link' => $shareLink,
                'share_message' => $shareMessage,
                'reward_info' => 'You get INR ' . ReferralHelper::getReferrerReward() . ' when they complete first order (min INR ' . ReferralHelper::getMinCartValue() . '). They get INR ' . ReferralHelper::getReferredReward() . '.',
            ],
        ], 200);
    }



    public function update_profile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'name'    => 'nullable|string|max:255',
                'email'   => 'nullable|email|unique:users,email,' . $request->user_id,
                'phone'   => 'nullable|digits:10|unique:users,phone,' . $request->user_id,
                'address' => 'nullable|string|max:500',
                'dob'     => 'nullable|date',
                'city_id' => 'nullable|integer',
                'state_id' => 'nullable|integer',
                'country_id' => 'nullable|integer',
                'gender'  => 'nullable|in:male,female,other',
                'zip'     => 'nullable|numeric',
                'vendor_description' => 'nullable|string',
                'image'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // 🔹 Find User
            $user = User::find($request->user_id);

            // 🔹 Update Fields
            $user->name  = $request->name ?? $user->name;
            $user->email = $request->email ?? $user->email;
            $user->phone = $request->phone ?? $user->phone;
            $user->address = $request->address ?? $user->address;
            $user->dob = $request->dob ?? $user->dob;
            $user->city_id = $request->city_id ?? $user->city_id;
            $user->state_id = $request->state_id ?? $user->state_id;
            $user->country_id = $request->country_id ?? $user->country_id;
            $user->gender = $request->gender ?? $user->gender;
            $user->zip = $request->zip ?? $user->zip;
            $user->vendor_description = $request->vendor_description ?? $user->vendor_description;

            // 🔹 Image Upload
            if ($request->hasFile('image')) {
                $user->image = ImageHelper::uploadImage($request->image, 'uploads/users/');
            }

            $user->save();

            return response()->json([
                'status' => true,
                'message' => __('messages.update_profile_success'),
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update_vendor_settings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'            => 'required|exists:users,id',
            'vendor_description' => 'nullable|string',
            'delivery_days'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = User::find($request->user_id);
        if (!$user || (string)$user->role !== '2') {
            return response()->json(['status' => false, 'message' => 'Unauthorized or Vendor not found'], 403);
        }

        $user->update([
            'vendor_description' => $request->vendor_description ?? $user->vendor_description,
            'delivery_days'      => $request->delivery_days ?? $user->delivery_days,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Vendor settings updated successfully',
            'data'    => $user
        ]);
    }


    public function BannerList(Request $request)
    {
        $inputs = $request->all();
        $banners = Banner::where('status', 1)->get();
        foreach ($banners as $banner) {
            $image = $banner->image;
            $decoded = json_decode($image, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $banner->image = array_map(fn($img) => ImageHelper::getBannerImage($img), $decoded);
            } else {
                $banner->image = $image ? [ImageHelper::getBannerImage($image)] : [];
            }
        }
        return response()->json([
            'status' => true,
            'message' => __('messages.banner_list_success'),
            'data' => $banners
        ]);
    }

    public function get_kyc_document(Request $request)
    {

        $vendor_doc_type = KYC_Document::where('is_active', 1)->get();
        return response()->json([
            'status' => true,
            'message' => __('messages.profile_fetched_successfully'),
            'data' => $vendor_doc_type
        ]);
    }

    private function syncGuestData($user, $ip_address)
    {
        if (empty($ip_address)) return;

        // 1. Sync Cart
        $guestCarts = Cart::where('ip_address', $ip_address)->get();
        foreach ($guestCarts as $guestItem) {
            $userItem = Cart::where('user_id', $user->id)
                ->where('product_id', $guestItem->product_id)
                ->where('variant_id', $guestItem->variant_id)
                ->first();

            if ($userItem) {
                // If exists, update quantity and delete guest item
                $userItem->qty += $guestItem->qty;
                $userItem->save();
                $guestItem->delete();
            } else {
                // Otherwise, assign to user
                $guestItem->update(['user_id' => $user->id, 'ip_address' => null]);
            }
        }

        // 2. Sync Wishlist
        $guestWishlists = Wishlist::where('ip_address', $ip_address)->get();
        foreach ($guestWishlists as $guestItem) {
            $exists = Wishlist::where('user_id', $user->id)
                ->where('product_id', $guestItem->product_id)
                ->exists();

            if ($exists) {
                // Duplicate in wishlist, delete guest entry
                $guestItem->delete();
            } else {
                // Assign to user
                $guestItem->update(['user_id' => $user->id, 'ip_address' => null]);
            }
        }
    }

    
    public function testSms(Request $request)
    {
        $phone = $request->phone ?? '9685185488';
        $message = $request->message ?? 'Your order has been placed successfully.';

        $sms = \App\Helpers\SparrowSmsHelper::send($phone, $message);

        return response()->json($sms);
    }

    public function my_reviews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $reviews = ProductReview::with(['product:id,name,thumbnail', 'variant:id,image'])
            ->where('user_id', $request->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($reviews->isNotEmpty()) {
            $data = $reviews->map(function ($review) {
                $productImage = $review->product->thumbnail ?? null;
                
                // If variant exists and has an image, use it instead of product thumbnail
                if ($review->variant && !empty($review->variant->image)) {
                    $productImage = $review->variant->image;
                }

                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'review' => $review->review,
                    'status' => $review->status,
                    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                    'product' => [
                        'id' => $review->product->id ?? null,
                        'name' => $review->product->name ?? null,
                        'image' => $productImage ? ImageHelper::getProductImage($productImage) : null,
                    ]
                ];
            });
            $message = __('messages.reviews_fetched_successfully');
        } else {
            $data = [];
            $message = __('messages.no_reviews_found');
        }

        return response()->json([
             'status' => true,
             'message' => $message,
             'data' => $data
         ], 200);
     }

    public function update_review(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'review_id' => 'required|exists:product_reviews,id',
            'user_id'   => 'required|exists:users,id',
            'rating'    => 'required|integer|between:1,5',
            'review'    => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $review = ProductReview::where('id', $request->review_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$review) {
            return response()->json([
                'status' => false,
                'message' => 'Review not found or unauthorized'
            ], 404);
        }

        $review->update([
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        return response()->json([
            'status' => true,
            'message' => __('messages.product_review_updated_successfully'),
            'data' => $review
        ], 200);
    }

    public function delete_review(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'review_id' => 'required|exists:product_reviews,id',
            'user_id'   => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $review = ProductReview::where('id', $request->review_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$review) {
            return response()->json([
                'status' => false,
                'message' => 'Review not found or unauthorized'
            ], 404);
        }

        $review->delete();

        return response()->json([
            'status' => true,
            'message' => __('messages.product_review_deleted_successfully')
        ], 200);
    }

    public function get_my_card(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $cards = UserCard::where('user_id', $request->user_id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $cards->map(function ($card) {
            return [
                'id'               => $card->id,
                'card_holder_name' => $card->card_holder_name,
                'card_number'      => $card->card_number,
                'expiry_month'     => $card->expiry_month,
                'expiry_year'      => $card->expiry_year,
                'card_type'        => $card->card_type,
                'is_default'       => $card->is_default,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Cards fetched successfully',
            'data'    => $data,
        ], 200);
    }

    public function add_card(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'          => 'required|exists:users,id',
            'card_holder_name' => 'required|string|max:255',
            'card_number'      => 'required|string|max:20',
            'expiry_month'     => 'required|numeric|min:1|max:12',
            'expiry_year'      => 'required|numeric|min:' . date('y') . '|max:99',
            'card_type'        => 'nullable|string|max:50',
            'is_default'       => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        if ($request->is_default) {
            UserCard::where('user_id', $request->user_id)->update(['is_default' => false]);
        }

        $card = UserCard::create([
            'user_id'          => $request->user_id,
            'card_holder_name' => $request->card_holder_name,
            'card_number'      => $request->card_number,
            'expiry_month'     => $request->expiry_month,
            'expiry_year'      => $request->expiry_year,
            'card_type'        => $request->card_type,
            'is_default'       => $request->is_default ?? false,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Card added successfully',
            'data'    => $card
        ], 201);
    }

    public function update_card(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_id'          => 'required|exists:users_card,id',
            'card_holder_name' => 'nullable|string|max:255',
            'card_number'      => 'nullable|string|max:20',
            'expiry_month'     => 'nullable|numeric|min:1|max:12',
            'expiry_year'      => 'nullable|numeric|min:' . date('y') . '|max:99',
            'card_type'        => 'nullable|string|max:50',
            'is_default'       => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $card = UserCard::find($request->card_id);

        if ($request->is_default) {
            UserCard::where('user_id', $card->user_id)->update(['is_default' => false]);
        }

        $card->update($request->only([
            'card_holder_name',
            'card_number',
            'expiry_month',
            'expiry_year',
            'card_type',
            'is_default'
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Card updated successfully',
            'data'    => $card
        ], 200);
    }

    public function delete_card(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_id' => 'required|exists:users_card,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $card = UserCard::find($request->card_id);
        $card->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Card deleted successfully'
        ], 200);
    }

    
      public function edit_card(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_id' => 'required|exists:users_card,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $card = UserCard::select('id as card_id','user_id','card_holder_name','card_number','expiry_month','expiry_year','card_type','is_default')->where('id', $request->card_id)->first();
       

        return response()->json([
            'status'  => true,
            'message' => 'Card detail fetched successfully!',
            'data'    => $card
        ], 200);
    }
 }
