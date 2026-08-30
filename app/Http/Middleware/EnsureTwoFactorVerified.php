<?php

namespace App\Http\Middleware;

use App\Services\TrustedDeviceService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTwoFactorVerified
{
    public function __construct(protected TrustedDeviceService $trustedDevices)
    {
    }

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

        if ($this->trustedDevices->isTrusted($request, $user)) {
            $request->session()->put('2fa_verified', true);

            return $next($request);
        }

        return redirect()->route('2fa.challenge');
    }
}
