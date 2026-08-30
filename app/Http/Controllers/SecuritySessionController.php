<?php

namespace App\Http\Controllers;

use App\Models\LoginDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SecuritySessionController extends Controller
{
    public function loginHistory()
    {
        $history = LoginDetail::where('user_id', Auth::id())
            ->orderByDesc('date')
            ->limit(50)
            ->get(['ip', 'date', 'details']);

        return view('users.login-history', compact('history'));
    }

    public function logoutOtherSessions()
    {
        $currentSessionId = session()->getId();

        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return redirect()->back()->with('success', __('All other active sessions have been logged out.'));
    }
}
