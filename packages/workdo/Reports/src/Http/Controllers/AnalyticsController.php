<?php

namespace Workdo\Reports\Http\Controllers;

use App\Models\Appointment;
use App\Models\CustomStatus;
use App\Models\Staff;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('analytics manage')) {
            $staffOption = $request->options;

            $businessId = getActiveBusiness();
            $creatorId = creatorId();

            $statuses = CustomStatus::where('created_by', $creatorId)
                ->where('business_id', $businessId)
                ->get();

            $pendingStatus = new CustomStatus();
            $pendingStatus->id = 0;
            $pendingStatus->title = 'Pending';
            $statuses->prepend($pendingStatus);

            if (!empty($staffOption)) {
                $staffs = Staff::select('name', 'user_id')
                    ->whereIn('user_id', $staffOption)
                    ->where('created_by', $creatorId)
                    ->where('business_id', $businessId)
                    ->get();
            } else {
                $staffs = Staff::select('name', 'user_id')
                    ->where('created_by', $creatorId)
                    ->where('business_id', $businessId)
                    ->get();
            }

            $staffStatusAppointments = Appointment::select(
                'staff_id',
                'appointment_status',
                DB::raw('count(*) as appointment_count'),
                DB::raw('SUM(appointment_payments.amount) as total_revenue')
            )
            ->join('appointment_payments', 'appointments.id', '=', 'appointment_payments.appointment_id')
            ->whereIn('staff_id', $staffs->pluck('user_id'))
            ->where('appointments.created_by', $creatorId)
            ->where('appointments.business_id', $businessId)
            ->groupBy('staff_id', 'appointment_status')
            ->get()
            ->groupBy('staff_id')
            ->map(function ($item) {
                return $item->keyBy('appointment_status');
            });
        

            $staffStatusAppointments->each(function ($staffAppointments) {
                if ($staffAppointments->has('Pending')) {
                    $staffAppointments[0] = $staffAppointments['Pending'];
                    unset($staffAppointments['Pending']);
                }
            });

            $totalAppointments = $staffStatusAppointments->flatten(1)->sum('appointment_count');
            $totalRevenue = $staffStatusAppointments->flatten(1)->sum('total_revenue');

            if (!empty($staffOption)) {
                $view = view('reports::reports.analytics_filter_report', compact('staffs', 'statuses', 'staffStatusAppointments', 'totalAppointments', 'totalRevenue'))->render();
                return response()->json(['html' => $view]);
            } else {
                return view('reports::reports.analytics_report', compact('staffs', 'statuses', 'staffStatusAppointments', 'totalAppointments', 'totalRevenue'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
