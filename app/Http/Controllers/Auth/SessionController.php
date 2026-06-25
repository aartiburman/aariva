<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function heartbeat(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'valid' => false,
                'redirect' => route('login')
            ], 200);
        }

        $user = Auth::user();
        $current = $request->session()->getId();
        $matches = empty($user->current_session_id) || $user->current_session_id === $current;

        if (!$matches) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return response()->json([
                'valid' => false,
                'redirect' => route('login'),
                'message' => 'Detached : Your account was logged in from another device.'
            ], 200);
        }

        return response()->json([
            'valid' => true
        ], 200);
    }
}
