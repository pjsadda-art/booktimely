<?php

namespace Workdo\Reports\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use DateTime;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceAppointmentReportController extends Controller
{

    public function index()
    {
        if (Auth::user()->isAbleTo('serviceappointmentreport manage')) {
            $services = Service::where('created_by', creatorId())
                ->where('business_id', getActiveBusiness())
                ->pluck('name', 'id');

            $currentYear = date('Y');

            $serviceAppointments = Appointment::where('business_id', getActiveBusiness())
                ->where('created_by', creatorId())
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->selectRaw('service_id, MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) as month, COUNT(*) as count')
                ->groupByRaw('service_id, MONTH(STR_TO_DATE(`date`, "%d-%m-%Y"))')
                ->orderBy('service_id')
                ->orderBy('month')
                ->get()
                ->toArray();

            $serviceAppointmentData = [];

            foreach ($serviceAppointments as $appointment) {
                $serviceId = $appointment['service_id'];
                $month = $appointment['month'];
                $count = $appointment['count'];

                if (!isset($serviceAppointmentData[$serviceId])) {
                    $serviceAppointmentData[$serviceId] = array_fill(1, 12, 0);
                }
                $serviceAppointmentData[$serviceId][$month] = $count;
            }

            $serviceAppointmentCounts = [];
            foreach ($services as $serviceId => $serviceName) {
                $appointmentData = isset($serviceAppointmentData[$serviceId]) ? $serviceAppointmentData[$serviceId] : [];
                $appointmentCounts = [];

                for ($i = 1; $i <= 12; $i++) {
                    $appointmentCounts[] = isset($appointmentData[$i]) ? $appointmentData[$i] : 0;
                }

                $serviceAppointmentCounts[] = [
                    'name' => $serviceName,
                    'data' => $appointmentCounts
                ];
            }

            // Get month names
            $monthList = $this->yearMonth();

            // Pass the data to the view
            return view('reports::reports.service_appointment_report', compact('monthList', 'serviceAppointmentCounts'));
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


    public function fetchServiceReportData(Request $request)
    {
        $period = $request->input('period');
        $date = $request->input('date');
        $businessId = getActiveBusiness();
        $creatorId = creatorId();

        $services = Service::where('created_by', $creatorId)
            ->where('business_id', $businessId)
            ->pluck('name', 'id');

        if ($period == 'year') {
            $currentYear = date('Y');
            $serviceAppointmentReport = Appointment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->selectRaw('service_id, MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) as month, COUNT(*) as count')
                ->groupByRaw('service_id, MONTH(STR_TO_DATE(`date`, "%d-%m-%Y"))')
                ->orderBy('service_id')
                ->orderBy('month')
                ->get()
                ->toArray();

            $monthList = $this->yearMonth();
            $serviceAppointmentData = [];

            foreach ($serviceAppointmentReport as $appointment) {
                $serviceId = $appointment['service_id'];
                $month = $appointment['month'];
                $count = $appointment['count'];

                if (!isset($serviceAppointmentData[$serviceId])) {
                    $serviceAppointmentData[$serviceId] = array_fill(1, 12, 0);
                }
                $serviceAppointmentData[$serviceId][$month] = $count;
            }

            $serviceAppointmentCounts = [];
            foreach ($services as $serviceId => $serviceName) {
                $appointmentData = isset($serviceAppointmentData[$serviceId]) ? $serviceAppointmentData[$serviceId] : [];
                $appointmentCounts = [];

                for ($i = 1; $i <= 12; $i++) {
                    $appointmentCounts[] = isset($appointmentData[$i]) ? $appointmentData[$i] : 0;
                }

                $serviceAppointmentCounts[] = [
                    'name' => $serviceName,
                    'data' => $appointmentCounts
                ];
            }

            return response()->json(['serviceCounts' => $serviceAppointmentCounts, 'Listdata' => $monthList]);
        } elseif ($period == 'last-month') {
            $lastMonth = now()->subMonth();
            $lastMonthYear = $lastMonth->year;
            $lastMonthNumber = $lastMonth->month;
            $lastday = $lastMonth->endOfMonth()->day;

            $serviceAppointmentReport = Appointment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$lastMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$lastMonthNumber])
                ->selectRaw('service_id, DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('service_id, DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('service_id')
                ->orderBy('date')
                ->get()
                ->toArray();

            $serviceAppointmentData = [];
            foreach ($serviceAppointmentReport as $appointment) {
                $serviceId = $appointment['service_id'];
                $date = $appointment['date'];
                $count = $appointment['count'];

                if (!isset($serviceAppointmentData[$serviceId])) {
                    $serviceAppointmentData[$serviceId] = array_fill(1, $lastday, 0);
                }
                $day = (int) date('j', strtotime($date)); // day of the month
                $serviceAppointmentData[$serviceId][$day] = $count;
            }

            $serviceAppointmentCounts = [];
            foreach ($services as $serviceId => $serviceName) {
                $appointmentData = isset($serviceAppointmentData[$serviceId]) ? $serviceAppointmentData[$serviceId] : [];
                $appointmentCounts = [];

                for ($i = 1; $i <= $lastday; $i++) {
                    $appointmentCounts[] = isset($appointmentData[$i]) ? $appointmentData[$i] : 0;
                }

                $serviceAppointmentCounts[] = [
                    'name' => $serviceName,
                    'data' => $appointmentCounts
                ];
            }

            $dateList = [];
            for ($i = 1; $i <= $lastday; $i++) {
                $date = sprintf('%04d-%02d-%02d', $lastMonthYear, $lastMonthNumber, $i);
                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
            }

            return response()->json(['serviceCounts' => $serviceAppointmentCounts, 'Listdata' => $dateList]);
        } elseif ($period == 'this-month') {
            $currentMonth = now();
            $currentMonthYear = $currentMonth->year;
            $currentMonthNumber = $currentMonth->month;
            $lastday = $currentMonth->endOfMonth()->day;

            $serviceAppointmentReport = Appointment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentMonthNumber])
                ->selectRaw('service_id, DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('service_id, DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('service_id')
                ->orderBy('date')
                ->get()
                ->toArray();

            $serviceAppointmentData = [];
            foreach ($serviceAppointmentReport as $appointment) {
                $serviceId = $appointment['service_id'];
                $date = $appointment['date'];
                $count = $appointment['count'];

                if (!isset($serviceAppointmentData[$serviceId])) {
                    $serviceAppointmentData[$serviceId] = array_fill(1, $lastday, 0);
                }
                $day = (int) date('j', strtotime($date)); // day of the month
                $serviceAppointmentData[$serviceId][$day] = $count;
            }

            $serviceAppointmentCounts = [];
            foreach ($services as $serviceId => $serviceName) {
                $appointmentData = isset($serviceAppointmentData[$serviceId]) ? $serviceAppointmentData[$serviceId] : [];
                $appointmentCounts = [];

                for ($i = 1; $i <= $lastday; $i++) {
                    $appointmentCounts[] = isset($appointmentData[$i]) ? $appointmentData[$i] : 0;
                }

                $serviceAppointmentCounts[] = [
                    'name' => $serviceName,
                    'data' => $appointmentCounts
                ];
            }

            $dateList = [];
            for ($i = 1; $i <= $lastday; $i++) {
                $date = sprintf('%04d-%02d-%02d', $currentMonthYear, $currentMonthNumber, $i);
                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
            }

            return response()->json(['serviceCounts' => $serviceAppointmentCounts, 'Listdata' => $dateList]);
        } elseif ($period == 'seven-day') {
            $endDate = now()->format('d-m-Y');
            $startDate = now()->subDays(6)->format('d-m-Y');

            $serviceAppointmentReport = Appointment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereBetween('date', [$startDate, $endDate])
                ->selectRaw('service_id, DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('service_id, DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('service_id')
                ->orderBy('date')
                ->get()
                ->toArray();

            $serviceAppointmentData = [];

            foreach ($serviceAppointmentReport as $appointment) {
                $serviceId = $appointment['service_id'];
                $date = $appointment['date'];
                $count = $appointment['count'];

                if (!isset($serviceAppointmentData[$serviceId])) {
                    $serviceAppointmentData[$serviceId] = [];
                }
                $serviceAppointmentData[$serviceId][$date] = $count;
            }

            $serviceAppointmentCounts = [];
            foreach ($services as $serviceId => $serviceName) {
                $appointmentData = isset($serviceAppointmentData[$serviceId]) ? $serviceAppointmentData[$serviceId] : [];
                $appointmentCounts = [];

                $currentDate = strtotime($startDate);
                $endDateUnix = strtotime($endDate);

                while ($currentDate <= $endDateUnix) {
                    $date = date('Y-m-d', $currentDate);
                    $appointmentCounts[] = isset($appointmentData[$date]) ? $appointmentData[$date] : 0;
                    $currentDate = strtotime('+1 day', $currentDate);
                }

                $serviceAppointmentCounts[] = [
                    'name' => $serviceName,
                    'data' => $appointmentCounts
                ];
            }

            $dateList = [];
            $currentDate = strtotime($startDate);
            $endDateUnix = strtotime($endDate);

            while ($currentDate <= $endDateUnix) {
                $date = date('Y-m-d', $currentDate);
                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
                $currentDate = strtotime('+1 day', $currentDate);
            }

            return response()->json(['serviceCounts' => $serviceAppointmentCounts, 'Listdata' => $dateList]);
        } elseif ($period == 'between-date') {
            $dates = explode(' to ', $date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));

            $serviceAppointmentReport = Appointment::where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereBetween(DB::raw('STR_TO_DATE(`date`, "%d-%m-%Y")'), [$startDate, $endDate])
                ->selectRaw('service_id, DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('service_id, DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('service_id')
                ->orderBy('date')
                ->get()
                ->toArray();

            $serviceAppointmentData = [];

            foreach ($serviceAppointmentReport as $appointment) {
                $serviceId = $appointment['service_id'];
                $date = $appointment['date'];
                $count = $appointment['count'];

                if (!isset($serviceAppointmentData[$serviceId])) {
                    $serviceAppointmentData[$serviceId] = [];
                }
                $serviceAppointmentData[$serviceId][$date] = $count;
            }

            $serviceAppointmentCounts = [];
            foreach ($services as $serviceId => $serviceName) {
                $appointmentData = isset($serviceAppointmentData[$serviceId]) ? $serviceAppointmentData[$serviceId] : [];
                $appointmentCounts = [];

                $currentDate = strtotime($startDate);
                $endDateUnix = strtotime($endDate);

                while ($currentDate <= $endDateUnix) {
                    $date = date('Y-m-d', $currentDate);
                    $appointmentCounts[] = isset($appointmentData[$date]) ? $appointmentData[$date] : 0;
                    $currentDate = strtotime('+1 day', $currentDate);
                }

                $serviceAppointmentCounts[] = [
                    'name' => $serviceName,
                    'data' => $appointmentCounts
                ];
            }

            $dateList = [];
            $currentDate = strtotime($startDate);
            $endDateUnix = strtotime($endDate);

            while ($currentDate <= $endDateUnix) {
                $date = date('Y-m-d', $currentDate);
                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
                $currentDate = strtotime('+1 day', $currentDate);
            }

            return response()->json(['serviceCounts' => $serviceAppointmentCounts, 'Listdata' => $dateList]);
        }
    }
}
