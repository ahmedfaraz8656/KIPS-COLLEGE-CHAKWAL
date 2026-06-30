<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Models\User;

class PasswordResetController extends Controller
{
    // ─── SEND RESET LINK (called from login.blade.php forgot-password modal) ──
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No account found with this email address.'], 404);
        }

        if (!$user->status) {
            return response()->json(['success' => false, 'message' => 'This account has been disabled. Please contact administration.'], 403);
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Reset link sent! Please check your email inbox (and spam folder).',
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Could not send reset link. Please try again.'], 500);
    }

    // ─── SET NEW PASSWORD PAGE ────────────────────────────────────
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'force_password_change' => false,
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['success' => true, 'message' => 'Password reset successful! Redirecting to login...']);
        }

        return response()->json(['success' => false, 'message' => 'This reset link has expired or is invalid. Please request a new one.'], 422);
    }
}
