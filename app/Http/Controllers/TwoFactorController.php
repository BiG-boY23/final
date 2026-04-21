<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class TwoFactorController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // SETUP — Generate secret + QR code (Security Settings page)
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Show the Security Settings / 2FA setup page.
     */
    public function showSetup()
    {
        $user = Auth::user();
        $google2fa = app('pragmarx.google2fa');

        // Generate a NEW temp secret only if the user doesn't have one yet
        // (prevents regenerating on every page refresh)
        if (!session('2fa_setup_secret')) {
            session(['2fa_setup_secret' => $google2fa->generateSecretKey()]);
        }

        $tempSecret = session('2fa_setup_secret');

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'SmartGate'),
            $user->email ?? $user->username,
            $tempSecret
        );

        // Render QR as inline SVG using BaconQrCode
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer   = new \BaconQrCode\Writer($renderer);
        $qrSvg    = $writer->writeString($qrCodeUrl);

        return view('auth.2fa-setup', [
            'user'          => $user,
            'tempSecret'    => $tempSecret,
            'qrSvg'         => $qrSvg,
            'isEnabled'     => (bool) $user->google2fa_secret,
            'recoveryCodes' => $user->two_factor_recovery_codes
                                 ? json_decode($user->two_factor_recovery_codes, true)
                                 : [],
        ]);
    }

    /**
     * Activate 2FA: verify the code against the temp secret, then save.
     */
    public function activate(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $user       = Auth::user();
        $google2fa  = app('pragmarx.google2fa');
        $tempSecret = session('2fa_setup_secret');

        if (!$tempSecret) {
            return back()->withErrors(['code' => 'Setup session expired. Please refresh and try again.']);
        }

        $valid = $google2fa->verifyKey($tempSecret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid code. Make sure your phone clock is synced and try again.']);
        }

        // Generate 5 single-use recovery codes
        $recoveryCodes = collect(range(1, 5))->map(fn() =>
            strtoupper(Str::random(5) . '-' . Str::random(5))
        )->toArray();

        $user->google2fa_secret           = encrypt($tempSecret);
        $user->two_factor_recovery_codes  = json_encode($recoveryCodes);
        $user->two_factor_enabled         = true;
        $user->save();

        // Transition from Pending to Fully Authenticated
        session(['fully_authenticated' => true]);
        session()->forget('pending_2fa_user');
        session()->forget('2fa_setup_secret');

        // Set display sessions for UI
        $fullName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
        session(['user' => $fullName, 'role' => $user->role]);

        return redirect()->route($this->getRedirectRoute($user))
            ->with('2fa_just_activated', true)
            ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * Deactivate 2FA for the authenticated user.
     */
    public function deactivate(Request $request)
    {
        $request->validate(['password' => 'required']);

        $user = Auth::user();

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $user->google2fa_secret          = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_enabled        = false;
        $user->save();

        session()->forget('2fa_verified');

        return redirect()->route('2fa.setup')->with('success', '2FA has been disabled for your account.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // CHALLENGE — Verify TOTP on every fresh login
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Show the 6-digit code challenge page.
     */
    public function showChallenge()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // If 2FA not set up, nothing to challenge
        if (!$user->google2fa_secret) {
            return redirect()->route('dashboard');
        }

        // Already verified this session
        if (session('2fa_verified')) {
            return redirect()->route('dashboard');
        }

        return view('auth.2fa-challenge');
    }

    /**
     * Verify the submitted TOTP code (or recovery code).
     */
    public function verifyChallenge(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20',
        ]);

        $user      = Auth::user();
        $google2fa = app('pragmarx.google2fa');
        $input     = strtoupper(trim($request->code));

        // Try as TOTP first (digits only)
        if (ctype_digit(str_replace(' ', '', $input))) {
            $secret = decrypt($user->google2fa_secret);
            $valid  = $google2fa->verifyKey($secret, str_replace(' ', '', $input));

            if ($valid) {
                session(['fully_authenticated' => true]);
                session()->forget('pending_2fa_user');

                // Set display sessions
                $fullName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
                session(['user' => $fullName, 'role' => $user->role]);

                return redirect()->intended(route($this->getRedirectRoute($user)));
            }
        }

        // Try as recovery code
        $codes = $user->two_factor_recovery_codes
            ? json_decode($user->two_factor_recovery_codes, true)
            : [];

        if (in_array($input, $codes)) {
            // Consume the code (one-time use)
            $remaining = array_values(array_filter($codes, fn($c) => $c !== $input));
            $user->two_factor_recovery_codes = json_encode($remaining);
            $user->save();

            session(['fully_authenticated' => true]);
            session()->forget('pending_2fa_user');

            // Set display sessions
            $fullName = trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name);
            session(['user' => $fullName, 'role' => $user->role]);

            return redirect()->intended(route($this->getRedirectRoute($user)));
        }

        return back()->withErrors(['code' => 'Invalid code. Check your authenticator app or use a recovery code.']);
    }

    protected function getRedirectRoute($user)
    {
        return match ($user->role) {
            'admin'  => 'admin.dashboard',
            'office' => 'office.dashboard',
            'guard'  => 'guard.dashboard',
            default  => 'landing',
        };
    }
}
