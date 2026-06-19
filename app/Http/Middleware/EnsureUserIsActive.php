<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access immediately if an Admin/MD has disabled this account
 * or its access-expiry date has passed — even mid-session.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            if (!$user->status) {
                Auth::logout();
                $request->session()->invalidate();
                return redirect()->route('login')
                    ->with('error', 'Your account has been disabled. Please contact administration.');
            }

            if ($user->access_expires_at && now()->greaterThan($user->access_expires_at)) {
                $user->update(['status' => false]);
                Auth::logout();
                $request->session()->invalidate();
                return redirect()->route('login')
                    ->with('error', 'Your account access has expired. Please contact administration.');
            }
        }

        return $next($request);
    }
}
