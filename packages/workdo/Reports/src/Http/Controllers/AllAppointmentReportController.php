<?php

namespace Workdo\Reports\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AllAppointmentReportController extends Controller
{

    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('customervsguestreport manage')) {

            $currentYear = date('Y');
            $period = $request->input('period', 'year');
            $Appointments = Appointment::where('business_id', getActiveBusiness())
                ->where('created_by', creatorId())
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->selectRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) as month, COUNT(*) as count')
                ->groupByRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y"))')
                ->orderBy('month')
                ->get()->toArray();

            $appointmentData = [];
            foreach ($Appointments as $appointment) {
                $appointmentData[$appointment['month']] = $appointment['count'];
            }

            $appointmentCounts = [];

            for ($i = 1; $i <= 12; $i++) {
                $appointmentCounts[] = isset($appointmentData[$i]) ? $appointmentData[$i] : 0;
            }

            $monthList = $this->yearMonth();

            // Appointment by Location    
            $appointmentsByLocation = Appointment::where('appointments.business_id', getActiveBusiness())
                ->where('appointments.created_by', creatorId())
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->leftJoin('locations', 'locations.id', '=', 'appointments.location_id')
                ->selectRaw('locations.name as location_name, COUNT(*) as count')
                ->groupBy('locations.id', 'locations.name')
                ->orderBy('locations.name')
                ->get()
                ->toArray();

            $locationLabels = array_column($appointmentsByLocation, 'location_name');
            $locationSeries = array_column($appointmentsByLocation, 'count');

            // Appointment by Staff    
            $appointmentsByStaff = Appointment::where('appointments.business_id', getActiveBusiness())
                ->where('appointments.created_by', creatorId())
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->leftJoin('staff', 'staff.user_id', '=', 'appointments.staff_id')
                ->selectRaw('staff.name as staff_name, COUNT(*) as count')
                ->groupBy('staff.user_id', 'staff.name')
                ->orderBy('staff.name')
                ->get()
                ->toArray();

            $staffLabels = array_column($appointmentsByStaff, 'staff_name');
            $staffSeries = array_column($appointmentsByStaff, 'count');

            return view('reports::reports.appointment_report', compact('monthList', 'appointmentCounts', 'locationLabels', 'locationSeries', 'staffLabels', 'staffSeries'));
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

    public function fetchAppointmentReportData(Request $request)
    {
        $period = $request->input('period');
        $date = $request->input('date');
        $businessId = getActiveBusiness();
        $creatorId = creatorId();

        if ($period == 'year') {
            $currentYear = date('Y');
            $appointmentReportData = Appointment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->selectRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) as month, COUNT(*) as count')
                ->groupByRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y"))')
                ->orderBy('month')
                ->get()->toArray();

            $monthList = $this->yearMonth();
            $appointmentData = [];
            foreach ($appointmentReportData as $appointment) {
                $appointmentData[$appointment['month']] = $appointment['count'];
            }

            $appointmentCounts = [];
            for ($i = 1; $i <= 12; $i++) {
                $appointmentCounts[] = isset($appointmentData[$i]) ? $appointmentData[$i] : 0;
            }
            return response()->json(['appointmentCounts' => $appointmentCounts, 'Listdata' => $monthList]);
        } elseif ($period == 'last-month') {
            $currentYear = now()->year;
            $lastMonth = now()->subMonth();
            $lastMonthYear = $lastMonth->year;
            $lastMonthNumber = $lastMonth->month;
            $lastday = $lastMonth->endOfMonth()->day;

            $appointmentReportData = Appointment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$lastMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$lastMonthNumber])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('date')
                ->get()
                ->toArray();

            $appointmentData = [];
            foreach ($appointmentReportData as $appointment) {
                $appointmentData[$appointment['date']] = $appointment['count'];
            }

            $appointmentCounts = [];
            $dateList = [];

            for ($i = 1; $i <= $lastday; $i++) {
                $date = sprintf('%04d-%02d-%02d', $lastMonthYear, $lastMonthNumber, $i);

                if (isset($appointmentData[$date])) {
                    $appointmentCounts[] = $appointmentData[$date];
                } else {
                    $appointmentCounts[] = 0;
                }

                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
            }
            return response()->json(['appointmentCounts' => $appointmentCounts, 'Listdata' => $dateList]);
        } elseif ($period == 'this-month') {
            $currentYear = now()->year;
            $currentMonth = now();
            $currentMonthYear = $currentMonth->year;
            $currentMonthNumber = $currentMonth->month;
            $lastday = $currentMonth->endOfMonth()->day;

            $appointmentReportData = Appointment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentMonthNumber])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('date')
                ->get()
                ->toArray();

            $appointmentData = [];
            foreach ($appointmentReportData as $appointment) {
                $appointmentData[$appointment['date']] = $appointment['count'];
            }

            $appointmentCounts = [];
            $dateList = [];

            for ($i = 1; $i <= $lastday; $i++) {
                $date = sprintf('%04d-%02d-%02d', $currentMonthYear, $currentMonthNumber, $i);

                if (isset($appointmentData[$date])) {
                    $appointmentCounts[] = $appointmentData[$date];
                } else {
                    $appointmentCounts[] = 0;
                }
                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
            }
            return response()->json(['appointmentCounts' => $appointmentCounts, 'Listdata' => $dateList]);
        } elseif ($period == 'seven-day') {
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime($endDate . ' -6 days'));

            $appointmentReportData = Appointment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereBetween(DB::raw('STR_TO_DATE(`date`, "%d-%m-%Y")'), [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('date')
                ->get()
                ->toArray();

            $appointmentData = [];
            foreach ($appointmentReportData as $appointment) {
                $appointmentData[$appointment['date']] = $appointment['count'];
            }

            $appointmentCounts = [];
            $dateList = [];

            $currentDate = strtotime($startDate);
            $endDateUnix = strtotime($endDate);

            while ($currentDate <= $endDateUnix) {
                $date = date('Y-m-d', $currentDate);
                $appointmentCounts[] = $appointmentData[$date] ?? 0;
                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
                $currentDate = strtotime('+1 day', $currentDate);
            }
            return response()->json(['appointmentCounts' => array_values($appointmentCounts), 'Listdata' => array_values($dateList)]);
        } elseif ($period == 'between-date') {
            $dates = explode(' to ', $date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));

            $appointmentReportData = Appointment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereBetween(DB::raw('STR_TO_DATE(`date`, "%d-%m-%Y")'), [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('date')
                ->get()
                ->toArray();

            $appointmentData = [];
            foreach ($appointmentReportData as $appointment) {
                $appointmentData[$appointment['date']] = $appointment['count'];
            }

            $appointmentCounts = [];
            $dateList = [];

            $currentDate = strtotime($startDate);
            $endDateUnix = strtotime($endDate);

            while ($currentDate <= $endDateUnix) {
                $date = date('Y-m-d', $currentDate);
                $appointmentCounts[] = $appointmentData[$date] ?? 0;
                $formattedDate = date('d-m-Y', $currentDate);
                $dateList[] = $formattedDate;
                $currentDate = strtotime('+1 day', $currentDate);
            }

            return response()->json(['appointmentCounts' => array_values($appointmentCounts), 'Listdata' => array_values($dateList)]);
        }
    }

    public function appointmentsByLocation(Request $request)
    {
        $period = $request->input('period');
        $date = $request->input('date');
        $businessId = getActiveBusiness();
        $creatorId = creatorId();

        if ($period == 'year') {
            $currentYear = date('Y');
            $appointmentsByLocation = Appointment::where('appointments.business_id', $businessId)
                ->where('appointments.created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->leftJoin('locations', 'locations.id', '=', 'appointments.location_id')
                ->selectRaw('locations.name as location_name, COUNT(*) as count')
                ->groupBy('locations.id', 'locations.name')
                ->orderBy('locations.name')
                ->get()
                ->toArray();

            $locationLabels = array_column($appointmentsByLocation, 'location_name');
            $locationSeries = array_column($appointmentsByLocation, 'count');

            return response()->json([
                'locationLabels' => $locationLabels,
                'locationSeries' => $locationSeries
            ]);
        } elseif ($period == 'last-month') {
            $lastMonth = now()->subMonth();
            $lastMonthYear = $lastMonth->year;
            $lastMonthNumber = $lastMonth->month;

            $appointmentsByLocation = Appointment::where('appointments.business_id', $businessId)
                ->where('appointments.created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$lastMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$lastMonthNumber])
                ->leftJoin('locations', 'locations.id', '=', 'appointments.location_id')
                ->selectRaw('locations.name as location_name, COUNT(*) as count')
                ->groupBy('locations.id', 'locations.name')
                ->orderBy('locations.name')
                ->get()
                ->toArray();

            $locationLabels = array_column($appointmentsByLocation, 'location_name');
            $locationSeries = array_column($appointmentsByLocation, 'count');

            return response()->json([
                'locationLabels' => $locationLabels,
                'locationSeries' => $locationSeries
            ]);
        } elseif ($period == 'this-month') {
            $currentMonth = now();
            $currentMonthYear = $currentMonth->year;
            $currentMonthNumber = $currentMonth->month;

            $appointmentsByLocation = Appointment::where('appointments.business_id', $businessId)
                ->where('appointments.created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$currentMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$currentMonthNumber])
                ->leftJoin('locations', 'locations.id', '=', 'appointments.location_id')
                ->selectRaw('locations.name as location_name, COUNT(*) as count')
                ->groupBy('locations.id', 'locations.name')
                ->orderBy('locations.name')
                ->get()
                ->toArray();

            $locationLabels = array_column($appointmentsByLocation, 'location_name');
            $locationSeries = array_column($appointmentsByLocation, 'count');

            return response()->json([
                'locationLabels' => $locationLabels,
                'locationSeries' => $locationSeries,
            ]);
        } elseif ($period == 'seven-day') {
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-6 days'));

            $appointmentsByLocation = Appointment::where('appointments.business_id', $businessId)
                ->where('appointments.created_by', $creatorId)
                ->whereBetween(DB::raw('STR_TO_DATE(appointments.date, "%d-%m-%Y")'), [$startDate, $endDate])
                ->leftJoin('locations', 'locations.id', '=', 'appointments.location_id')
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(appointments.date, "%d-%m-%Y"), "%Y-%m-%d") as appointment_date, locations.name as location_name, COUNT(*) as count')
                ->groupByRaw('appointment_date, locations.id, locations.name')
                ->orderBy('appointment_date', 'asc')
                ->orderBy('locations.name', 'asc')
                ->get()
                ->toArray();

            $locationLabels = array_column($appointmentsByLocation, 'location_name');
            $locationSeries = array_column($appointmentsByLocation, 'count');

            return response()->json([
                'locationLabels' => $locationLabels,
                'locationSeries' => $locationSeries,
            ]);
        } elseif ($period == 'between-date') {

            $dates = explode(' to ', $date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));

            $appointmentsByLocation = Appointment::where('appointments.business_id', $businessId)
                ->where('appointments.created_by', $creatorId)
                ->whereBetween(DB::raw('STR_TO_DATE(appointments.date, "%d-%m-%Y")'), [$startDate, $endDate])
                ->leftJoin('locations', 'locations.id', '=', 'appointments.location_id')
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(appointments.date, "%d-%m-%Y"), "%Y-%m-%d") as appointment_date, locations.name as location_name, COUNT(*) as count')
                ->groupByRaw('appointment_date, locations.id, locations.name')
                ->orderBy('appointment_date', 'asc')
                ->orderBy('locations.name', 'asc')
                ->get()
                ->toArray();

            $locationLabels = array_column($appointmentsByLocation, 'location_name');
            $locationSeries = array_column($appointmentsByLocation, 'count');

            return response()->json([
                'locationLabels' => $locationLabels,
                'locationSeries' => $locationSeries,
            ]);
        }
    }

    public function appointmentsByStaff(Request $request)
    {
        $period = $request->input('period');
        $date = $request->input('date');
        $businessId = getActiveBusiness();
        $creatorId = creatorId();

        if ($period == 'year') {
            $currentYear = date('Y');

            $appointmentsByStaff = Appointment::where('appointments.business_id', $businessId)
                ->where('appointments.created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->leftJoin('staff', 'staff.user_id', '=', 'appointments.staff_id')
                ->selectRaw('staff.name as staff_name, COUNT(*) as count')
                ->groupBy('staff.user_id', 'staff.name')
                ->orderBy('staff.name')
                ->get()
                ->toArray();

            $staffLabels = array_column($appointmentsByStaff, 'staff_name');
            $staffSeries = array_column($appointmentsByStaff, 'count');

            return response()->json([
                'staffLabels' => $staffLabels,
                'staffSeries' => $staffSeries
            ]);
        } elseif ($period == 'last-month') {
            $lastMonth = now()->subMonth();
            $lastMonthYear = $lastMonth->year;
            $lastMonthNumber = $lastMonth->month;

            $appointmentsByStaff = Appointment::where('appointments.business_id', $businessId)
                ->where('appointments.created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$lastMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$lastMonthNumber])
                ->leftJoin('staff', 'staff.user_id', '=', 'appointments.staff_id')
                ->selectRaw('staff.name as staff_name, COUNT(*) as count')
                ->groupBy('staff.user_id', 'staff.name')
                ->orderBy('staff.name')
                ->get()
                ->toArray();

            $staffLabels = array_column($appointmentsByStaff, 'staff_name');
            $staffSeries = array_column($appointmentsByStaff, 'count');

            return response()->json([
                'staffLabels' => $staffLabels,
                'staffSeries' => $staffSeries
            ]);
        } elseif ($period == 'this-month') {
            $currentMonth = now();
            $currentMonthYear = $currentMonth->year;
            $currentMonthNumber = $currentMonth->month;

            $appointmentsByStaff = Appointment::where('appointments.business_id', $businessId)
                ->where('appointments.created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$currentMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$currentMonthNumber])
                ->leftJoin('staff', 'staff.user_id', '=', 'appointments.staff_id')
                ->selectRaw('staff.name as staff_name, COUNT(*) as count')
                ->groupBy('staff.user_id', 'staff.name')
                ->orderBy('staff.name')
                ->get()
                ->toArray();

            $staffLabels = array_column($appointmentsByStaff, 'staff_name');
            $staffSeries = array_column($appointmentsByStaff, 'count');

            return response()->json([
                'staffLabels' => $staffLabels,
                'staffSeries' => $staffSeries
            ]);
        } elseif ($period == 'seven-day') {
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-6 days'));

            $appointmentsByStaff = Appointment::where('appointments.business_id', $businessId)
                ->where('appointments.created_by', $creatorId)
                ->whereBetween(DB::raw('STR_TO_DATE(appointments.date, "%d-%m-%Y")'), [$startDate, $endDate])
                ->leftJoin('staff', 'staff.user_id', '=', 'appointments.staff_id')
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(appointments.date, "%d-%m-%Y"), "%Y-%m-%d") as appointment_date, staff.name as staff_name, COUNT(*) as count')
                ->groupByRaw('appointment_date, staff.user_id, staff.name')
                ->orderBy('appointment_date', 'asc')
                ->orderBy('staff.name', 'asc')
                ->get()
                ->toArray();

            $staffLabels = array_column($appointmentsByStaff, 'staff_name');
            $staffSeries = array_column($appointmentsByStaff, 'count');

            return response()->json([
                'staffLabels' => $staffLabels,
                'staffSeries' => $staffSeries
            ]);
        } elseif ($period == 'between-date') {

            $dates = explode(' to ', $date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));

            $appointmentsByStaff = Appointment::where('appointments.business_id', $businessId)
                ->where('appointments.created_by', $creatorId)
                ->whereBetween(DB::raw('STR_TO_DATE(appointments.date, "%d-%m-%Y")'), [$startDate, $endDate])
                ->leftJoin('staff', 'staff.user_id', '=', 'appointments.staff_id')
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(appointments.date, "%d-%m-%Y"), "%Y-%m-%d") as appointment_date, staff.name as staff_name, COUNT(*) as count')
                ->groupByRaw('appointment_date, staff.user_id, staff.name')
                ->orderBy('appointment_date', 'asc')
                ->orderBy('staff.name', 'asc')
                ->get()
                ->toArray();

            $staffLabels = array_column($appointmentsByStaff, 'staff_name');
            $staffSeries = array_column($appointmentsByStaff, 'count');

            return response()->json([
                'staffLabels' => $staffLabels,
                'staffSeries' => $staffSeries
            ]);
        }
    }
}
