<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'current_password' => ['required_with:new_password', 'nullable'],
            'new_password'     => ['nullable', 'confirmed', Password::min(8)],
            'profile_picture'  => ['nullable', 'image', 'max:2048'], // 2MB max
            'dark_mode'        => ['nullable', 'boolean'],
            'two_factor_enabled' => ['nullable', 'boolean'],
            'language'         => ['required', 'string', 'in:en,tl'],
        ]);

        // 1. Profile Picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $path = $request->file('profile_picture')->store('profiles', 'public');
            $user->profile_picture = $path;
        }

        // 2. Security check for password change
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The current password you provided is incorrect.'
                ], 422);
            }
            $user->password = Hash::make($request->new_password);
        }

        // 3. User Identity updates
        $user->first_name = strip_tags($request->first_name);
        $user->last_name  = strip_tags($request->last_name);
        $user->name       = trim($user->first_name . ' ' . $user->last_name);
        $user->email      = $request->email;
        
        // 4. Preferences & Security settings
        $user->dark_mode          = $request->has('dark_mode') ? (bool)$request->dark_mode : $user->dark_mode;
        $user->two_factor_enabled = $request->has('two_factor_enabled') ? (bool)$request->two_factor_enabled : $user->two_factor_enabled;
        $user->language           = $request->language ?? $user->language;
        
        $user->save();

        // 5. Sync Session Data (Used in Dashboard Sidebar/Header)
        session([
            'user' => $user->name,
            'role' => $user->role
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile Updated! Your changes have been successfully saved.',
            'user'    => [
                'full_name' => $user->name,
                'email'     => $user->email,
                'avatar'    => $user->profile_picture ? Storage::url($user->profile_picture) : null
            ]
        ]);
    }

    /**
     * Get login history for the current user
     */
    public function getLoginHistory()
    {
        $logs = Auth::user()->loginLogs()->orderBy('login_at', 'desc')->take(10)->get();
        return response()->json($logs);
    }

    /**
     * Get new pending registrations for notifications.
     */
    public function getPendingRegistrations(Request $request)
    {
        // Only return for admin or office
        if (!in_array(Auth::user()->role, ['admin', 'office'])) {
            return response()->json(['total_pending' => 0, 'new_registrations' => [], 'current_time' => now()->toIso8601String()]);
        }

        $lastCheck = $request->query('last_check');
        $query = \App\Models\VehicleRegistration::where('status', 'pending');
        
        $totalPending = (clone $query)->count();
        
        $newRegistrations = [];
        if ($lastCheck) {
            try {
                $lastCheckTime = \Carbon\Carbon::parse($lastCheck);
                $newRegistrations = (clone $query)->where('created_at', '>', $lastCheckTime)
                                                  ->orderBy('created_at', 'desc')
                                                  ->get(['id', 'full_name', 'vehicle_type', 'created_at']);
            } catch (\Exception $e) {
                // Ignore parse errors
            }
        }

        return response()->json([
            'total_pending' => $totalPending,
            'new_registrations' => $newRegistrations,
            'current_time' => now()->toIso8601String()
        ]);
    }
}
