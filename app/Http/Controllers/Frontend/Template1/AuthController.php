<?php

namespace App\Http\Controllers\Frontend\Template1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('frontend.auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $credentials['role'] = '3';

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('frontend.home')]);
            }
            return redirect()->intended(route('frontend.home'));
        }

        if ($request->ajax()) {
            return response()->json(['errors' => ['email' => ['Invalid email or password']]], 422);
        }
        return redirect()->back()->with('error', 'Invalid email or password')->withInput();
    }

    public function showRegisterForm()
    {
        return view('frontend.auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'phone'                 => 'required|string|max:20',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => '3',
            'status'   => 1,
        ]);

        Auth::login($user);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'redirect' => route('frontend.home')]);
        }
        return redirect()->route('frontend.home');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('frontend.login');
    }
}
