<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    // Max failed attempts before lockout
    protected int $maxAttempts  = 5;
    protected int $decayMinutes = 15;

    // ─── SHOW LOGIN FORM ─────────────────────────────────────────
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // ─── HANDLE LOGIN ─────────────────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email address is required.',
            'email.email'       => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
        ]);

        // Check too many attempts
        if ($this->hasTooManyLoginAttempts($request)) {
            $seconds = $this->limiter()->availableIn($this->throttleKey($request));
            $minutes = ceil($seconds / 60);

            return response()->json([
                'success' => false,
                'message' => "Too many login attempts. Please try again in {$minutes} minute(s).",
                'locked'  => true,
                'retry_in' => $seconds,
            ], 429);
        }

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {

            $user = Auth::user();

            // Check if user is active
            if (!$user->isActive()) {
                Auth::logout();
                $this->incrementLoginAttempts($request);
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been disabled. Please contact administration.',
                ], 403);
            }

            // Update last login info
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Log audit
            Log::info("User Login: {$user->name} ({$user->primaryRole()}) from IP {$request->ip()}");

            $this->clearLoginAttempts($request);
            $request->session()->regenerate();

            return response()->json([
                'success'  => true,
                'message'  => 'Login successful! Redirecting...',
                'redirect' => route('dashboard'),
                'role'     => $user->primaryRole(),
            ]);
        }

        // Failed login
        $this->incrementLoginAttempts($request);
        $remaining = $this->maxAttempts - $this->limiter()->attempts($this->throttleKey($request));

        return response()->json([
            'success'   => false,
            'message'   => "Invalid email or password. {$remaining} attempt(s) remaining.",
            'remaining' => max(0, $remaining),
        ], 401);
    }

    // ─── LOGOUT ──────────────────────────────────────────────────
    public function logout(Request $request)
    {
        $user = Auth::user();
        Log::info("User Logout: {$user->name}");

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    // ─── THROTTLE HELPERS ────────────────────────────────────────
    protected function hasTooManyLoginAttempts(Request $request): bool
    {
        return $this->limiter()->tooManyAttempts(
            $this->throttleKey($request), $this->maxAttempts
        );
    }

    protected function incrementLoginAttempts(Request $request): void
    {
        $this->limiter()->hit(
            $this->throttleKey($request), $this->decayMinutes * 60
        );
    }

    protected function clearLoginAttempts(Request $request): void
    {
        $this->limiter()->clear($this->throttleKey($request));
    }

    protected function throttleKey(Request $request): string
    {
        return strtolower($request->input('email')) . '|' . $request->ip();
    }

    protected function limiter()
    {
        return app(\Illuminate\Cache\RateLimiter::class);
    }
}
