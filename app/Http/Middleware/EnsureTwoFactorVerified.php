<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !$user->requiresTwoFactor()) {
            return $next($request);
        }

        if ($request->session()->get('2fa_verified')) {
            return $next($request);
        }

        if (!$user->two_factor_enabled) {
            return redirect()->route('2fa.setup');
        }

        return redirect()->route('2fa.challenge');
    }
}
