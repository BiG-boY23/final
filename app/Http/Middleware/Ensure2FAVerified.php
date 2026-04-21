<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Ensure2FAVerified
{
    /**
     * 2FA applies to these administrative roles.
     */
    private const PROTECTED_ROLES = ['admin', 'office', 'meso'];

    /**
     * These route names are ALWAYS allowed even if 2FA is not verified yet.
     */
    private const WHITELISTED_ROUTES = [
        '2fa.challenge',
        '2fa.verify',
        'logout',
        'login',
        'landing'
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Whitelist: Always allow these security routes to prevent redirect loops
        $routeName = $request->route() ? $request->route()->getName() : null;
        if ($routeName && in_array($routeName, self::WHITELISTED_ROUTES)) {
            return $next($request);
        }
        if ($request->is('2fa/*') || $request->is('login') || $request->is('logout') || $request->is('verify-2fa*')) {
            return $next($request);
        }

        // 2. SUCCESS STATE: If already fully verified, pass through
        if (session('fully_authenticated')) {
            return $next($request);
        }

        // 3. THE WALL: If 2FA is pending, redirect back to verification, NOT login
        if (session('pending_2fa_user')) {
            $user = Auth::user();
            // If they haven't set it up, they go to setup
            if ($user && !$user->google2fa_secret) {
                return redirect()->route('2fa.setup');
            }
            // Otherwise, they go to verification
            return redirect()->route('2fa.challenge');
        }

        // 4. ROLE CHECK: If role doesn't need 2FA (e.g. Guard), they are fully authenticated automatically
        if (Auth::check() && !in_array(Auth::user()->role, self::PROTECTED_ROLES)) {
            session(['fully_authenticated' => true]);
            return $next($request);
        }

        // 5. NO SESSION: If not logged in and no pending user, redirect to login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 6. DEFAULT: If logged in but no flags, treat as pending for safety
        $user = Auth::user();
        if (in_array($user->role, self::PROTECTED_ROLES)) {
            session(['pending_2fa_user' => $user->id]);
            return redirect()->route('2fa.challenge');
        }

        return $next($request);
    }
}
