<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorAuditLog;
use App\Models\UserTwoFactorRecoveryCode;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TwoFactorAuthenticationController extends Controller
{
    public function __construct(protected TwoFactorAuthenticationService $twoFactor)
    {
    }

    public function status()
    {
        $user = Auth::user();

        return response()->json([
            'two_factor_enabled' => (bool) $user->two_factor_enabled,
            'two_factor_required' => (bool) $user->two_factor_required,
            'mandatory' => $this->isMandatoryForUser($user),
        ]);
    }

    public function setup(Request $request)
    {
        $user = Auth::user();

        $secret = $this->twoFactor->generateSecret();
        $request->session()->put('pending_2fa_secret', $secret);

        $otpauthUri = $this->twoFactor->otpauthUri($user, $secret);

        TwoFactorAuditLog::record(TwoFactorAuditLog::SETUP_STARTED, $user->id);

        return view('auth.two-factor-setup', [
            'secret' => $secret,
            'qrCodeSvg' => $this->twoFactor->qrCodeSvg($otpauthUri),
            'forced' => (bool) $user->two_factor_required,
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = Auth::user();
        $secret = $request->session()->get('pending_2fa_secret');

        if (!$secret) {
            throw ValidationException::withMessages([
                'code' => __('Your setup session has expired. Please start again.'),
            ]);
        }

        if (!$this->twoFactor->verify($secret, $request->input('code'))) {
            TwoFactorAuditLog::record(TwoFactorAuditLog::VERIFY_FAILED, $user->id);

            throw ValidationException::withMessages([
                'code' => __('The code you entered is invalid.'),
            ]);
        }

        $user->two_factor_secret = $secret;
        $user->two_factor_enabled = true;
        $user->two_factor_confirmed_at = now();
        $user->two_factor_enabled_at = now();
        $user->save();

        $request->session()->forget('pending_2fa_secret');
        $request->session()->put('2fa_verified', true);

        $recoveryCodes = $this->twoFactor->generateRecoveryCodes($user);

        TwoFactorAuditLog::record(TwoFactorAuditLog::ENABLED, $user->id);

        return view('auth.two-factor-recovery-codes', [
            'codes' => $recoveryCodes,
        ]);
    }

    public function disable(Request $request)
    {
        $user = Auth::user();

        if ($this->isMandatoryForUser($user)) {
            abort(403, __('Two-factor authentication is required by your company administrator and cannot be disabled.'));
        }

        $request->validate([
            'password' => ['required', 'current_password'],
            'code' => ['nullable', 'digits:6'],
        ]);

        if ($user->two_factor_enabled) {
            $validCode = $request->filled('code') && $this->twoFactor->verify($user->two_factor_secret, $request->input('code'));
            $validRecovery = $request->filled('code') && $this->twoFactor->verifyAndConsumeRecoveryCode($user, $request->input('code'));

            if (!$validCode && !$validRecovery) {
                throw ValidationException::withMessages([
                    'code' => __('Please enter your current authenticator code to confirm.'),
                ]);
            }
        }

        $user->two_factor_secret = null;
        $user->two_factor_enabled = false;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_enabled_at = null;
        $user->save();

        UserTwoFactorRecoveryCode::where('user_id', $user->id)->delete();

        $request->session()->forget(['pending_2fa_secret', '2fa_verified']);

        TwoFactorAuditLog::record(TwoFactorAuditLog::DISABLED, $user->id);

        return redirect()->route('profile')->with('success', __('Two-factor authentication has been disabled.'));
    }

    public function regenerateRecoveryCodes(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => ['required', 'current_password'],
            'code' => ['required', 'digits:6'],
        ]);

        if (!$this->twoFactor->verify($user->two_factor_secret, $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => __('The code you entered is invalid.'),
            ]);
        }

        $codes = $this->twoFactor->generateRecoveryCodes($user);

        TwoFactorAuditLog::record(TwoFactorAuditLog::RECOVERY_CODES_REGENERATED, $user->id);

        return view('auth.two-factor-recovery-codes', [
            'codes' => $codes,
        ]);
    }

    protected function isMandatoryForUser($user): bool
    {
        if ($user->two_factor_required) {
            return true;
        }

        if ($user->type !== 'super admin') {
            $business = \App\Models\Business::find($user->business_id);
            if ($business && $business->require_2fa) {
                return true;
            }
        }

        return false;
    }
}
