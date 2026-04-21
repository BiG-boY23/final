<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validate Input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. Brute Force Protection (Rate Limiting)
        $throttleKey = Str::lower($request->input('username')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'username' => "Too many login attempts. Please try again in {$seconds} seconds."
            ])->withInput($request->except('password'));
        }

        // 3. Attempt Login
        if (Auth::attempt($request->only('username', 'password'), $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            
            $user = Auth::user();
            $fullName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
            
            // Record login audit trail
            \App\Models\LoginLog::create([
                'user_id'    => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // 4. Handle 2FA Sequence
            if ($user->google2fa_secret) {
                Session::put('pending_2fa_user', $user->id);
                return redirect()->route('2fa.challenge');
            }

            // FORCED ENROLLMENT
            if (in_array($user->role, ['admin', 'office', 'meso'])) {
                Session::put('pending_2fa_user', $user->id);
                return redirect()->route('2fa.setup')->with('info', 'Security Policy: Please set up Two-Factor Authentication to continue.');
            }

            // Normal Redirect (Guards/Vendors)
            Session::put('fully_authenticated', true);
            Session::put('user', $fullName);
            Session::put('role', $user->role);
            
            return redirect()->intended($this->getRedirectPath($user))
                ->with('success', "Welcome back, $fullName!");
        }

        // 5. Fail - Increment Throttle
        RateLimiter::hit($throttleKey);

        return back()->withErrors(['username' => 'Invalid credentials. Please check your username and password.'])
            ->withInput($request->except('password'));
    }

    /**
     * Determine redirect path based on user role.
     */
    protected function getRedirectPath($user)
    {
        return match ($user->role) {
            'admin'  => route('admin.dashboard'),
            'office' => route('office.dashboard'),
            'guard'  => route('guard.dashboard'),
            default  => route('landing'),
        };
    }

    public function logout()
    {
        Auth::logout();
        Session::flush();
        return redirect()->route('landing')->with('success', 'Logged out successfully. Have a nice day!');
    }
}
