<?php

namespace Workdo\Reports\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentPayment;
use App\Models\Location;
use Google\Service\AndroidManagement\Date;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevenueReportController extends Controller
{

    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('revenuereport manage')) {

            $currentYear = date('Y');

            $appointmentPayments = AppointmentPayment::where('business_id', getActiveBusiness())
                ->where('created_by', creatorId())
                ->whereRaw('YEAR(payment_date) = ?', [$currentYear])
                ->selectRaw('MONTH(payment_date) as month, SUM(amount) as total_amount')
                ->groupByRaw('MONTH(payment_date)')
                ->orderBy('month')
                ->get();

            // Revenue by Location    
            $locationRevenue = Appointment::where('appointments.business_id', getActiveBusiness())
                ->leftJoin('locations', 'locations.id', '=', 'appointments.location_id')
                ->leftJoin('appointment_payments', 'appointment_payments.appointment_id', '=', 'appointments.id')
                ->where('appointments.created_by', creatorId())
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->selectRaw('locations.name as location_name, SUM(appointment_payments.amount) as total_revenue')
                ->groupBy('locations.id', 'locations.name')
                ->orderBy('total_revenue', 'desc')
                ->get();

            $locationSeries = $locationRevenue->pluck('total_revenue')->toArray();
            $locationLabels = $locationRevenue->pluck('location_name')->toArray();

            // Staff by Revenue
            $staffRevenue = Appointment::where('appointments.business_id', getActiveBusiness())
                ->leftJoin('staff', 'staff.user_id', '=', 'appointments.staff_id')
                ->leftJoin('appointment_payments', 'appointment_payments.appointment_id', '=', 'appointments.id')
                ->where('appointments.created_by', creatorId())
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->selectRaw('staff.name as staff_name, SUM(appointment_payments.amount) as total_revenue')
                ->groupBy('staff.user_id', 'staff.name')
                ->orderBy('total_revenue', 'desc')
                ->get();

            $staffSeries = $staffRevenue->pluck('total_revenue')->toArray();
            $staffLabels = $staffRevenue->pluck('staff_name')->toArray();

            $monthList = $this->yearMonth();

            $appointmentData = [];
            foreach ($appointmentPayments as $payment) {
                $appointmentData[$payment->month] = $payment->total_amount;
            }

            $revenueCounts = [];
            for ($i = 1; $i <= 12; $i++) {
                $revenueCounts[] = isset($appointmentData[$i]) ? $appointmentData[$i] : 0;
            }

            $company_settings = getCompanyAllSetting();

            return view('reports::reports.revenue_report', compact('revenueCounts', 'monthList', 'locationSeries', 'locationLabels', 'staffSeries', 'staffLabels','company_settings'));
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

    public function fetchRevenueReportData(Request $request)
    {
        $period = $request->input('period');
        $date = $request->input('date');
        $businessId = getActiveBusiness();
        $creatorId = creatorId();

        if ($period == 'year') {
            $currentYear = date('Y');
            $revenueReport = AppointmentPayment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(`payment_date`, "%Y-%m-%d")) = ?', [$currentYear])
                ->selectRaw('MONTH(STR_TO_DATE(`payment_date`, "%Y-%m-%d")) as month, SUM(amount) as total_amount')
                ->groupByRaw('MONTH(STR_TO_DATE(`payment_date`, "%Y-%m-%d"))')
                ->orderBy('month')
                ->get()->toArray();

            $monthList = $this->yearMonth();
            $revenueData = [];
            foreach ($revenueReport as $revenue) {
                $revenueData[$revenue['month']] = $revenue['total_amount'];
            }

            $revenueCounts = [];
            for ($i = 1; $i <= 12; $i++) {
                $revenueCounts[] = isset($revenueData[$i]) ? $revenueData[$i] : 0;
            }
            return response()->json(['revenueCounts' => $revenueCounts, 'Listdata' => $monthList]);
        } elseif ($period == 'last-month') {
            $currentYear = now()->year;
            $lastMonth = now()->subMonth();
            $lastMonthYear = $lastMonth->year;
            $lastMonthNumber = $lastMonth->month;
            $lastday = $lastMonth->endOfMonth()->day;

            $revenueReport = AppointmentPayment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereRaw('YEAR(payment_date) = ?', [$lastMonthYear])
                ->whereRaw('MONTH(payment_date) = ?', [$lastMonthNumber])
                ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m-%d") as month, SUM(amount) as total_amount')
                ->groupByRaw('DATE_FORMAT(payment_date, "%Y-%m-%d")')
                ->orderBy('month')
                ->get()
                ->toArray();

            $revenueData = [];
            foreach ($revenueReport as $revenue) {
                $revenueData[$revenue['month']] = $revenue['total_amount'];
            }

            $revenueCounts = [];
            $dateList = [];

            for ($i = 1; $i <= $lastday; $i++) {
                $date = sprintf('%04d-%02d-%02d', $lastMonthYear, $lastMonthNumber, $i);

                if (isset($revenueData[$date])) {
                    $revenueCounts[] = $revenueData[$date];
                } else {
                    $revenueCounts[] = 0;
                }

                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
            }
            return response()->json(['revenueCounts' => $revenueCounts, 'Listdata' => $dateList]);
        } elseif ($period == 'this-month') {
            $currentYear = now()->year;
            $currentMonth = now();
            $currentMonthYear = $currentMonth->year;
            $currentMonthNumber = $currentMonth->month;
            $lastday = $currentMonth->endOfMonth()->day;

            $revenueReport = AppointmentPayment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereRaw('YEAR(payment_date) = ?', [$currentMonthYear])
                ->whereRaw('MONTH(payment_date) = ?', [$currentMonthNumber])
                ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m-%d") as month, SUM(amount) as total_amount')
                ->groupByRaw('DATE_FORMAT(payment_date, "%Y-%m-%d")')
                ->orderBy('month')
                ->get()
                ->toArray();


            $revenueData = [];
            foreach ($revenueReport as $revenue) {
                $revenueData[$revenue['month']] = $revenue['total_amount'];
            }

            $revenueCounts = [];
            $dateList = [];

            for ($i = 1; $i <= $lastday; $i++) {
                $date = sprintf('%04d-%02d-%02d', $currentMonthYear, $currentMonthNumber, $i);

                if (isset($revenueData[$date])) {
                    $revenueCounts[] = $revenueData[$date];
                } else {
                    $revenueCounts[] = 0;
                }
                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
            }
            return response()->json(['revenueCounts' => $revenueCounts, 'Listdata' => $dateList]);
        } elseif ($period == 'seven-day') {
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-6 days'));

            $revenueReport = AppointmentPayment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereBetween(DB::raw('STR_TO_DATE(payment_date, "%Y-%m-%d")'), [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(payment_date, "%Y-%m-%d"), "%Y-%m-%d") as date, SUM(amount) as total_amount')
                ->groupByRaw('DATE_FORMAT(STR_TO_DATE(payment_date, "%Y-%m-%d"), "%Y-%m-%d")')
                ->orderBy('date')
                ->get()
                ->toArray();


            $revenueData = [];
            foreach ($revenueReport as $revenue) {
                $revenueData[$revenue['date']] = $revenue['total_amount'];
            }

            $revenueCounts = [];
            $dateList = [];
            $currentDate = strtotime($startDate);
            $endDateUnix = strtotime($endDate);

            while ($currentDate <= $endDateUnix) {
                $date = date('Y-m-d', $currentDate);
                $revenueCounts[] = $revenueData[$date] ?? 0;
                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
                $currentDate = strtotime('+1 day', $currentDate);
            }

            return response()->json(['revenueCounts' => $revenueCounts, 'Listdata' => $dateList]);
        } elseif ($period == 'between-date') {
            $dates = explode(' to ', $date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));

            $revenueReport = AppointmentPayment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereBetween(DB::raw('STR_TO_DATE(payment_date, "%Y-%m-%d")'), [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(payment_date, "%Y-%m-%d"), "%Y-%m-%d") as date, SUM(amount) as total_amount')
                ->groupByRaw('DATE_FORMAT(STR_TO_DATE(payment_date, "%Y-%m-%d"), "%Y-%m-%d")')
                ->orderBy('date')
                ->get()
                ->toArray();


            $revenueData = [];
            foreach ($revenueReport as $revenue) {
                $revenueData[$revenue['date']] = $revenue['total_amount'];
            }

            $revenueCounts = [];
            $dateList = [];
            $currentDate = strtotime($startDate);
            $endDateUnix = strtotime($endDate);

            while ($currentDate <= $endDateUnix) {
                $date = date('Y-m-d', $currentDate);
                $revenueCounts[] = $revenueData[$date] ?? 0;
                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
                $currentDate = strtotime('+1 day', $currentDate);
            }

            return response()->json(['revenueCounts' => $revenueCounts, 'Listdata' => $dateList]);
        }
    }

    public function revenueByLocation(Request $request)
    {
        $period = $request->input('period');
        $date = $request->input('date');
        $businessId = getActiveBusiness();
        $creatorId = creatorId();

        if ($period == 'year') {
            $currentYear = date('Y');

            $locationRevenue = Appointment::where('appointments.business_id', $businessId)
                ->leftJoin('locations', 'locations.id', '=', 'appointments.location_id')
                ->leftJoin('appointment_payments', 'appointment_payments.appointment_id', '=', 'appointments.id')
                ->where('appointments.created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$currentYear])
                ->selectRaw('locations.name as location_name, SUM(appointment_payments.amount) as total_revenue')
                ->groupBy('locations.id', 'locations.name')
                ->orderBy('locations.name')
                ->get()
                ->toArray();

            $locationLabels = array_column($locationRevenue, 'location_name');
            $locationSeries = array_column($locationRevenue, 'total_revenue');

            return response()->json([
                'locationLabels' => $locationLabels,
                'locationSeries' => $locationSeries
            ]);
        } elseif ($period == 'last-month') {
            $lastMonth = now()->subMonth();
            $lastMonthYear = $lastMonth->year;
            $lastMonthNumber = $lastMonth->month;

            $locationRevenue = Appointment::where('appointments.business_id', $businessId)
                ->leftJoin('locations', 'locations.id', '=', 'appointments.location_id')
                ->leftJoin('appointment_payments', 'appointment_payments.appointment_id', '=', 'appointments.id')
                ->where('appointments.created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$lastMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$lastMonthNumber])
                ->selectRaw('locations.name as location_name, SUM(appointment_payments.amount) as total_revenue')
                ->groupBy('locations.id', 'locations.name')
                ->orderBy('total_revenue', 'desc')
                ->get();

            $locationLabels = $locationRevenue->pluck('location_name')->toArray();
            $locationSeries = $locationRevenue->pluck('total_revenue')->toArray();

            return response()->json([
                'locationLabels' => $locationLabels,
                'locationSeries' => $locationSeries
            ]);
        } elseif ($period == 'this-month') {
            $currentMonth = now();
            $currentMonthYear = $currentMonth->year;
            $currentMonthNumber = $currentMonth->month;

            $locationRevenue = Appointment::where('appointments.business_id', $businessId)
                ->where('appointments.created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$currentMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$currentMonthNumber])
                ->leftJoin('locations', 'locations.id', '=', 'appointments.location_id')
                ->leftJoin('appointment_payments', 'appointment_payments.appointment_id', '=', 'appointments.id')
                ->selectRaw('locations.name as location_name, SUM(appointment_payments.amount) as total_revenue')
                ->groupBy('locations.id', 'locations.name')
                ->orderBy('total_revenue', 'desc')
                ->get();

            $locationLabels = $locationRevenue->pluck('location_name')->toArray();
            $locationSeries = $locationRevenue->pluck('total_revenue')->toArray();

            return response()->json([
                'locationLabels' => $locationLabels,
                'locationSeries' => $locationSeries,
            ]);
        } elseif ($period == 'seven-day') {
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-6 days'));

            $locationRevenue = Appointment::where('appointments.business_id', $businessId)
                ->leftJoin('locations', 'locations.id', '=', 'appointments.location_id')
                ->leftJoin('appointment_payments', 'appointment_payments.appointment_id', '=', 'appointments.id')
                ->where('appointments.created_by', $creatorId)
                ->whereBetween(DB::raw('STR_TO_DATE(appointments.date, "%d-%m-%Y")'), [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(appointments.date, "%d-%m-%Y"), "%Y-%m-%d") as appointment_date, locations.name as location_name, SUM(appointment_payments.amount) as total_revenue')
                ->groupByRaw('appointment_date, locations.id, locations.name')
                ->orderBy('appointment_date', 'asc')
                ->orderBy('locations.name', 'asc')
                ->get();

            $locationLabels = $locationRevenue->pluck('location_name')->toArray();
            $locationSeries = $locationRevenue->pluck('total_revenue')->toArray();

            return response()->json([
                'locationLabels' => $locationLabels,
                'locationSeries' => $locationSeries,
            ]);
        } elseif ($period == 'between-date') {

            $dates = explode(' to ', $date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));

            $locationRevenue = Appointment::where('appointments.business_id', $businessId)
                ->leftJoin('locations', 'locations.id', '=', 'appointments.location_id')
                ->leftJoin('appointment_payments', 'appointment_payments.appointment_id', '=', 'appointments.id')
                ->where('appointments.created_by', $creatorId)
                ->whereBetween(DB::raw('STR_TO_DATE(appointments.date, "%d-%m-%Y")'), [$startDate, $endDate])
                ->selectRaw('locations.name as location_name, SUM(appointment_payments.amount) as total_revenue')
                ->groupByRaw('locations.id, locations.name')
                ->orderBy('location_name', 'asc')
                ->get();

            $locationLabels = $locationRevenue->pluck('location_name')->toArray();
            $locationSeries = $locationRevenue->pluck('total_revenue')->toArray();

            return response()->json([
                'locationLabels' => $locationLabels,
                'locationSeries' => $locationSeries,
            ]);
        }
    }

    public function revenueByStaff(Request $request)
    {
        $period = $request->input('period');
        $date = $request->input('date');
        $businessId = getActiveBusiness();
        $creatorId = creatorId();

        if ($period == 'year') {
            $currentYear = date('Y');
            $staffRevenue = Appointment::where('appointments.business_id', $businessId)
                ->leftJoin('staff', 'staff.user_id', '=', 'appointments.staff_id')
                ->leftJoin('appointment_payments', 'appointment_payments.appointment_id', '=', 'appointments.id')
                ->where('appointments.created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->selectRaw('staff.name as staff_name, SUM(appointment_payments.amount) as total_revenue')
                ->groupBy('staff.user_id', 'staff.name')
                ->orderBy('total_revenue', 'desc')
                ->get()->toArray();

            $staffLabels = array_column($staffRevenue, 'staff_name');
            $staffSeries = array_column($staffRevenue, 'total_revenue');

            return response()->json([
                'staffLabels' => $staffLabels,
                'staffSeries' => $staffSeries
            ]);
        } elseif ($period == 'last-month') {
            $lastMonth = now()->subMonth();
            $lastMonthYear = $lastMonth->year;
            $lastMonthNumber = $lastMonth->month;

            $staffRevenue = Appointment::where('appointments.business_id', $businessId)
                ->leftJoin('staff', 'staff.user_id', '=', 'appointments.staff_id')
                ->leftJoin('appointment_payments', 'appointment_payments.appointment_id', '=', 'appointments.id')
                ->where('appointments.created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$lastMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$lastMonthNumber])
                ->selectRaw('staff.name as staff_name, SUM(appointment_payments.amount) as total_revenue')
                ->groupBy('staff.id', 'staff.name')
                ->orderBy('total_revenue', 'desc')
                ->get()
                ->toArray();

            $staffLabels = array_column($staffRevenue, 'staff_name');
            $staffSeries = array_column($staffRevenue, 'total_revenue');

            return response()->json([
                'staffLabels' => $staffLabels,
                'staffSeries' => $staffSeries
            ]);
        } elseif ($period == 'this-month') {

            $currentMonth = now();
            $currentMonthYear = $currentMonth->year;
            $currentMonthNumber = $currentMonth->month;

            $staffRevenue = Appointment::where('appointments.business_id', $businessId)
                ->where('appointments.created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$currentMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(appointments.date, "%d-%m-%Y")) = ?', [$currentMonthNumber])
                ->leftJoin('staff', 'staff.user_id', '=', 'appointments.staff_id')
                ->leftJoin('appointment_payments', 'appointment_payments.appointment_id', '=', 'appointments.id')
                ->selectRaw('staff.name as staff_name, SUM(appointment_payments.amount) as total_revenue')
                ->groupBy('staff.id', 'staff.name')
                ->orderBy('total_revenue', 'desc')
                ->get()
                ->toArray();

            $staffLabels = array_column($staffRevenue, 'staff_name');
            $staffSeries = array_column($staffRevenue, 'total_revenue');

            return response()->json([
                'staffLabels' => $staffLabels,
                'staffSeries' => $staffSeries
            ]);
        } elseif ($period == 'seven-day') {
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-6 days'));

            $staffRevenue = Appointment::where('appointments.business_id', $businessId)
                ->leftJoin('staff', 'staff.user_id', '=', 'appointments.staff_id')
                ->leftJoin('appointment_payments', 'appointment_payments.appointment_id', '=', 'appointments.id')
                ->where('appointments.created_by', $creatorId)
                ->whereBetween(DB::raw('STR_TO_DATE(appointments.date, "%d-%m-%Y")'), [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(appointments.date, "%d-%m-%Y"), "%Y-%m-%d") as appointment_date, staff.name as staff_name, SUM(appointment_payments.amount) as total_revenue')
                ->groupByRaw('appointment_date, staff.id, staff.name')
                ->orderBy('appointment_date', 'asc')
                ->orderBy('staff.name', 'asc')
                ->get()
                ->toArray();

            $staffLabels = array_column($staffRevenue, 'staff_name');
            $staffSeries = array_column($staffRevenue, 'total_revenue');

            return response()->json([
                'staffLabels' => $staffLabels,
                'staffSeries' => $staffSeries
            ]);
        } elseif ($period == 'between-date') {

            $dates = explode(' to ', $date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));

            $staffRevenue = Appointment::where('appointments.business_id', $businessId)
                ->leftJoin('staff', 'staff.user_id', '=', 'appointments.staff_id')
                ->leftJoin('appointment_payments', 'appointment_payments.appointment_id', '=', 'appointments.id')
                ->where('appointments.created_by', $creatorId)
                ->whereBetween(DB::raw('STR_TO_DATE(appointments.date, "%d-%m-%Y")'), [$startDate, $endDate])
                ->selectRaw('staff.name as staff_name, SUM(appointment_payments.amount) as total_revenue')
                ->groupByRaw('staff.id, staff.name')
                ->orderBy('staff_name', 'asc')
                ->get()
                ->toArray();

            $staffLabels = array_column($staffRevenue, 'staff_name');
            $staffSeries = array_column($staffRevenue, 'total_revenue');

            return response()->json([
                'staffLabels' => $staffLabels,
                'staffSeries' => $staffSeries
            ]);
        }
    }
}
