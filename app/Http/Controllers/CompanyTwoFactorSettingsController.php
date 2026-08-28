<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\TwoFactorAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyTwoFactorSettingsController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->type !== 'company') {
            abort(403);
        }

        $business = Business::find($user->business_id ?: $user->active_business);

        if (!$business) {
            abort(404);
        }

        $allow2fa = $request->boolean('allow_2fa');
        $require2fa = $request->boolean('require_2fa');

        $wasRequired = (bool) $business->require_2fa;

        $business->allow_2fa = $allow2fa;
        $business->require_2fa = $allow2fa && $require2fa;
        $business->save();

        if ($business->require_2fa && !$wasRequired) {
            TwoFactorAuditLog::record(TwoFactorAuditLog::COMPANY_REQUIRE_ENABLED, $user->id, $user->id, $business->id);
        } elseif (!$business->require_2fa && $wasRequired) {
            TwoFactorAuditLog::record(TwoFactorAuditLog::COMPANY_REQUIRE_DISABLED, $user->id, $user->id, $business->id);
        }

        return back()->with('success', __('Security settings updated.'));
    }
}
