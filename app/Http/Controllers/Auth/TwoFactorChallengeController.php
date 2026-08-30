<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TwoFactorAuditLog;
use App\Providers\RouteServiceProvider;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorChallengeController extends Controller
{
    public function __construct(protected TwoFactorAuthenticationService $twoFactor)
    {
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        if (!$user || $request->session()->get('2fa_verified')) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        if (!$user->two_factor_enabled) {
            return redirect()->route('2fa.setup');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $input = $request->input('code');

        $verified = false;

        if (preg_match('/^\d{6}$/', $input)) {
            $verified = $this->twoFactor->verify($user->two_factor_secret, $input);
        }

        if (!$verified) {
            $verified = $this->twoFactor->verifyAndConsumeRecoveryCode($user, $input);

            if ($verified) {
                TwoFactorAuditLog::record(TwoFactorAuditLog::RECOVERY_CODE_USED, $user->id);
            }
        }

        if (!$verified) {
            TwoFactorAuditLog::record(TwoFactorAuditLog::VERIFY_FAILED, $user->id);

            throw ValidationException::withMessages([
                'code' => __('The code you entered is invalid or expired.'),
            ]);
        }

        $request->session()->put('2fa_verified', true);
        $request->session()->regenerate();

        TwoFactorAuditLog::record(TwoFactorAuditLog::VERIFY_SUCCESS, $user->id);

        // redirect()->intended() hasn't been called yet, so the original
        // destination is still sitting in session('url.intended') — the
        // trust-device screen reads it via the same call once its own
        // question is answered.
        return redirect()->route('2fa.trust-device');
    }
}
