<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Services\TrustedDeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The "Trust this device?" prompt shown right after a successful 2FA code
 * verification — see TwoFactorChallengeController::store(). Only reachable
 * mid-login, once the code has actually been verified for this session.
 */
class TrustedDeviceController extends Controller
{
    public function __construct(protected TrustedDeviceService $trustedDevices)
    {
    }

    public function create(Request $request)
    {
        if (!Auth::check() || !$request->session()->get('2fa_verified')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-trust-device');
    }

    public function store(Request $request)
    {
        if (!Auth::check() || !$request->session()->get('2fa_verified')) {
            return redirect()->route('login');
        }

        $request->validate([
            'duration' => ['required', 'string', 'in:never,1_day,1_week,1_month,1_qtr'],
        ]);

        if ($request->input('duration') !== 'never') {
            $this->trustedDevices->trust($request, Auth::user(), $request->input('duration'));
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
