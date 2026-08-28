<?php

namespace Workdo\Reports\Http\Controllers;

use App\Models\Appointment;
use App\Models\CustomStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentStatusReportController extends Controller
{

    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('analytics manage')) {
            $businessId = getActiveBusiness();
            $creatorId = creatorId();

            $statuses = CustomStatus::where('created_by', $creatorId)
                ->where('business_id', $businessId)
                ->get();

            $pendingStatus = new CustomStatus();
            $pendingStatus->title = 'Pending';

            $statuses->prepend($pendingStatus, 'Pending');

            $period = $request->input('period', 'year');
            $statusCounts = [];
            $monthList = $this->yearMonth();

            foreach ($statuses as $status) {
                $statusCounts[$status->title] = Appointment::where('appointment_status', $status->title)
                    ->whereYear('created_at', now()->year)
                    ->selectRaw('MONTH(STR_TO_DATE(date, "%d-%m-%Y")) as month, count(*) as count')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->pluck('count', 'month')
                    ->toArray();
            }
            return view('reports::reports.status_report', compact('statuses', 'statusCounts', 'monthList', 'period'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function yearMonth()
    {
        return [
            __('January'),
            __('February'),
            __('March'),
            __('April'),
            __('May'),
            __('June'),
            __('July'),
            __('August'),
            __('September'),
            __('October'),
            __('November'),
            __('December')
        ];
    }

    public function fetchStatusReportData(Request $request)
    {
        $period = $request->input('period');
        $date = $request->input('date');
        $businessId = getActiveBusiness();
        $creatorId = creatorId();
        $currentYear = date('Y');

        $statuses = CustomStatus::where('created_by', $creatorId)
            ->where('business_id', $businessId)
            ->get();

        $pendingStatus = new CustomStatus();
        $pendingStatus->id = 0;
        $pendingStatus->title = 'Pending';
        $pendingStatus->status_color = '5bc0de';
        $statuses->prepend($pendingStatus);

        $statusData = [];
        $categories = [];

        if ($period == 'year') {
            $monthList = $this->yearMonth();
            $categories = $monthList;
            foreach ($statuses as $status) {
                $statusId = $status->id == 0 ? 'Pending' : $status->id;

                $appointments = Appointment::with('StatusData')
                    ->where('business_id', $businessId)
                    ->where('created_by', $creatorId)
                    ->where('appointment_status', $statusId)
                    ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                    ->selectRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) as month, COUNT(*) as count')
                    ->groupByRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y"))')
                    ->orderBy('month')
                    ->get()
                    ->toArray();

                $data = array_fill(1, 12, 0);
                foreach ($appointments as $appointment) {
                    $data[$appointment['month']] = $appointment['count'];
                }
                $statusData[$status->title] = array_values($data);
            }
        } elseif ($period == 'last-month') {
            $lastMonth = now()->subMonth();
            $lastMonthYear = $lastMonth->year;
            $lastMonthNumber = $lastMonth->month;
            $lastday = $lastMonth->endOfMonth()->day;
            $categories = array_map(function ($day) use ($lastMonthYear, $lastMonthNumber) {
                return sprintf('%02d-%02d-%04d', $day, $lastMonthNumber, $lastMonthYear);
            }, range(1, $lastday));

            foreach ($statuses as $status) {
                $statusId = $status->id == 0 ? 'Pending' : $status->id;
                $appointments = Appointment::with('StatusData')
                    ->where('business_id', $businessId)
                    ->where('created_by', $creatorId)
                    ->where('appointment_status', $statusId)
                    ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$lastMonthYear])
                    ->whereRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$lastMonthNumber])
                    ->selectRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                    ->groupByRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                    ->orderBy('date')
                    ->get()
                    ->toArray();

                $data = array_fill(1, $lastday, 0);
                foreach ($appointments as $appointment) {
                    $day = (int)date('d', strtotime($appointment['date']));
                    $data[$day] = $appointment['count'];
                }
                $statusData[$status->title] = array_values($data);
            }
        } elseif ($period == 'this-month') {
            $currentMonth = now();
            $currentMonthYear = $currentMonth->year;
            $currentMonthNumber = $currentMonth->month;
            $lastday = $currentMonth->endOfMonth()->day;
            $categories = array_map(function ($day) use ($currentMonthYear, $currentMonthNumber) {
                return sprintf('%02d-%02d-%04d', $day, $currentMonthNumber, $currentMonthYear);
            }, range(1, $lastday));

            foreach ($statuses as $status) {
                $statusId = $status->id == 0 ? 'Pending' : $status->id;
                $appointments = Appointment::with('StatusData')
                    ->where('business_id', $businessId)
                    ->where('created_by', $creatorId)
                    ->where('appointment_status', $statusId)
                    ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentMonthYear])
                    ->whereRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentMonthNumber])
                    ->selectRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                    ->groupByRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                    ->orderBy('date')
                    ->get()
                    ->toArray();

                $data = array_fill(1, $lastday, 0);
                foreach ($appointments as $appointment) {
                    $day = (int)date('d', strtotime($appointment['date']));
                    $data[$day] = $appointment['count'];
                }
                $statusData[$status->title] = array_values($data);
            }
        }
        if ($period == 'seven-day') {
            $endDate = Carbon::now()->endOfDay();
            $startDate = Carbon::now()->subDays(6)->startOfDay();

            $categories = [];
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $categories[] = $currentDate->format('d-m-Y');
                $currentDate->addDay();
            }

            foreach ($statuses as $status) {
                $statusId = $status->id == 0 ? 'Pending' : $status->id;
                $appointments = Appointment::with('StatusData')
                    ->where('business_id', $businessId)
                    ->where('created_by', $creatorId)
                    ->where('appointment_status', $statusId)
                    ->whereBetween(DB::raw("DATE_FORMAT(STR_TO_DATE(`date`, '%d-%m-%Y'), '%Y-%m-%d')"), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->selectRaw("DATE_FORMAT(STR_TO_DATE(`date`, '%d-%m-%Y'), '%d-%m-%Y') as date, COUNT(*) as count")
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->toArray();

                $data = array_fill(0, 7, 0);
                foreach ($appointments as $appointment) {
                    $index = Carbon::createFromFormat('d-m-Y', $appointment['date'])->diffInDays($startDate);
                    $data[$index] = $appointment['count'];
                }
                $statusData[$status->title] = $data;
            }
        } elseif ($period == 'between-date' && $date) {
            $dates = explode(' to ', $date);
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

            $categories = [];
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $categories[] = $currentDate->format('d-m-Y');
                $currentDate->addDay();
            }

            foreach ($statuses as $status) {
                $statusId = $status->id == 0 ? 'Pending' : $status->id;
                $appointments = Appointment::with('StatusData')
                    ->where('business_id', $businessId)
                    ->where('created_by', $creatorId)
                    ->where('appointment_status', $statusId)
                    ->whereBetween(DB::raw("DATE_FORMAT(STR_TO_DATE(`date`, '%d-%m-%Y'), '%Y-%m-%d')"), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->selectRaw("DATE_FORMAT(STR_TO_DATE(`date`, '%d-%m-%Y'), '%d-%m-%Y') as date, COUNT(*) as count")
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->toArray();

                $data = array_fill(0, count($categories), 0);
                foreach ($appointments as $appointment) {
                    $index = Carbon::createFromFormat('d-m-Y', $appointment['date'])->diffInDays($startDate);
                    $data[$index] = $appointment['count'];
                }
                $statusData[$status->title] = $data;
            }
        }

        return response()->json(['statuses' => $statuses, 'statusData' => $statusData, 'categories' => $categories]);
    }
}
