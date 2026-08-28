<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Business;
use App\Models\TwoFactorAuditLog;
use App\Models\User;
use App\Models\UserTwoFactorRecoveryCode;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function __construct()
    {
        if (!Auth::check() || Auth::user()->type !== 'super admin') {
            abort(403);
        }
    }

    public function index()
    {
        $users = User::where('type', '!=', 'super admin')
            ->with([])
            ->get()
            ->map(function (User $user) {
                $business = Business::find($user->business_id);

                $status = 'Disabled';
                if ($user->two_factor_required && !$user->two_factor_enabled) {
                    $status = 'Required by Admin';
                } elseif ($user->two_factor_enabled) {
                    $status = 'Enabled';
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'business' => $business->name ?? '-',
                    'role' => $user->type,
                    'status' => $status,
                ];
            });

        return view('super-admin.two-factor.index', compact('users'));
    }

    public function require(Request $request, User $user)
    {
        $user->two_factor_required = true;
        $user->save();

        TwoFactorAuditLog::record(TwoFactorAuditLog::ADMIN_REQUIRED, $user->id, Auth::id(), $user->business_id);

        return back()->with('success', __('Two-factor authentication is now required for this user.'));
    }

    public function disable(Request $request, User $user)
    {
        $user->two_factor_secret = null;
        $user->two_factor_enabled = false;
        $user->two_factor_required = false;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_enabled_at = null;
        $user->save();

        UserTwoFactorRecoveryCode::where('user_id', $user->id)->delete();

        TwoFactorAuditLog::record(TwoFactorAuditLog::ADMIN_DISABLED, $user->id, Auth::id(), $user->business_id);

        return back()->with('success', __('Two-factor authentication has been disabled for this user.'));
    }

    public function reset(Request $request, User $user)
    {
        $user->two_factor_secret = null;
        $user->two_factor_enabled = false;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_enabled_at = null;
        $user->save();

        UserTwoFactorRecoveryCode::where('user_id', $user->id)->delete();

        TwoFactorAuditLog::record(TwoFactorAuditLog::ADMIN_RESET, $user->id, Auth::id(), $user->business_id);

        return back()->with('success', __('Two-factor authentication has been reset for this user. They will be asked to set it up again.'));
    }
}
