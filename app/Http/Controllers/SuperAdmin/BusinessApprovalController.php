<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BusinessApprovalController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isAbleTo('business approval manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $businesses = Business::where('registration_status', 'pending')
            ->with('industry')
            ->orderByDesc('created_at')
            ->get();

        return view('super-admin.business-approvals.index', compact('businesses'));
    }

    public function approve(Business $business)
    {
        if (!Auth::user()->isAbleTo('business approval manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $business->registration_status = 'approved';
        $business->save();

        $owner = User::where('id', $business->created_by)->first();
        if ($owner) {
            $plan = Plan::where('is_free_plan', 1)->first();
            if ($plan) {
                $owner->assignPlan($plan->id, 'Month', $plan->modules, 0, $owner->id);
            }
        }

        return redirect()->back()->with('success', __('Business approved successfully.'));
    }

    public function reject(Business $business)
    {
        if (!Auth::user()->isAbleTo('business approval manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $business->registration_status = 'rejected';
        $business->save();

        return redirect()->back()->with('success', __('Business registration rejected.'));
    }
}
