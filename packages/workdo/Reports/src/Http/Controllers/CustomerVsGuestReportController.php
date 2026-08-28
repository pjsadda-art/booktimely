<?php

namespace Workdo\Reports\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class CustomerVsGuestReportController extends Controller
{

    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('customervsguestreport manage')) {
            $customers = Customer::where('business_id', getActiveBusiness())
                ->where('created_by', creatorId())
                ->pluck('name', 'user_id');

            $customerIds = $customers->keys()->toArray();

            $currentYear = date('Y');

            $customerAppointments = Appointment::whereIn('customer_id', $customerIds)
                ->where('business_id', getActiveBusiness())
                ->where('created_by', creatorId())
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->selectRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) as month, COUNT(*) as count')
                ->groupByRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y"))')
                ->orderBy('month')
                ->get()->toArray();
            $guestAppointments = Appointment::whereNull('customer_id')
                ->where('business_id', getActiveBusiness())
                ->where('created_by', creatorId())
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->selectRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) as month, COUNT(*) as count')
                ->groupByRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y"))')
                ->orderBy('month')
                ->get()->toArray();
            $monthList = $this->yearMonth();
            $customerData = [];
            foreach ($customerAppointments as $appointment) {
                $customerData[$appointment['month']] = $appointment['count'];
            }
            $guestData = [];
            foreach ($guestAppointments as $appointment) {
                $guestData[$appointment['month']] = $appointment['count'];
            }
            $customerCounts = [];
            $guestCounts = [];
            for ($i = 1; $i <= 12; $i++) {
                $customerCounts[] = isset($customerData[$i]) ? $customerData[$i] : 0;
                $guestCounts[] = isset($guestData[$i]) ? $guestData[$i] : 0;
            }

            return view('reports::reports.customer_vs_guest_report', compact('customerCounts', 'guestCounts', 'monthList'));
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

    public function fetchCustomerReportData(Request $request)
    {
        $period = $request->input('period');
        $date = $request->input('date');
        $businessId = getActiveBusiness();
        $creatorId = creatorId();
        $customers = Customer::where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->pluck('name', 'user_id');

        $customerIds = $customers->keys()->toArray();


        if ($period == 'year') {
            $currentYear = date('Y');
            $customerAppointments = Appointment::whereIn('customer_id', $customerIds)
                ->where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->selectRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) as month, COUNT(*) as count')
                ->groupByRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y"))')
                ->orderBy('month')
                ->get()->toArray();
            $guestAppointments = Appointment::whereNull('customer_id')
                ->where('business_id', $businessId)
                ->where('created_by', $creatorId)
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentYear])
                ->selectRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) as month, COUNT(*) as count')
                ->groupByRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y"))')
                ->orderBy('month')
                ->get()->toArray();
            $monthList = $this->yearMonth();
            $customerData = [];
            foreach ($customerAppointments as $appointment) {
                $customerData[$appointment['month']] = $appointment['count'];
            }
            $guestData = [];
            foreach ($guestAppointments as $appointment) {
                $guestData[$appointment['month']] = $appointment['count'];
            }
            $customerCounts = [];
            $guestCounts = [];
            for ($i = 1; $i <= 12; $i++) {
                $customerCounts[] = isset($customerData[$i]) ? $customerData[$i] : 0;
                $guestCounts[] = isset($guestData[$i]) ? $guestData[$i] : 0;
            }
            return response()->json(['guestCounts' => $guestCounts, 'customerCounts' => $customerCounts, 'Listdata' => $monthList]);
        } elseif ($period == 'last-month') {
            $currentYear = now()->year;
            $lastMonth = now()->subMonth();
            $lastMonthYear = $lastMonth->year;
            $lastMonthNumber = $lastMonth->month;
            $lastday = $lastMonth->endOfMonth()->day;

            $customerAppointments = Appointment::whereIn('customer_id', $customerIds)
                ->where('business_id', getActiveBusiness())
                ->where('created_by', creatorId())
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$lastMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$lastMonthNumber])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('date')
                ->get()
                ->toArray();

            $guestAppointments = Appointment::whereNull('customer_id')
                ->where('business_id', getActiveBusiness())
                ->where('created_by', creatorId())
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$lastMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$lastMonthNumber])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('date')
                ->get()
                ->toArray();

            $customerData = [];
            foreach ($customerAppointments as $appointment) {
                $customerData[$appointment['date']] = $appointment['count'];
            }

            $guestData = [];
            foreach ($guestAppointments as $appointment) {
                $guestData[$appointment['date']] = $appointment['count'];
            }

            $customerCounts = [];
            $guestCounts = [];
            $dateList = [];

            for ($i = 1; $i <= $lastday; $i++) {
                $date = sprintf('%04d-%02d-%02d', $lastMonthYear, $lastMonthNumber, $i);

                if (isset($customerData[$date])) {
                    $customerCounts[] = $customerData[$date];
                } else {
                    $customerCounts[] = 0;
                }
                if (isset($guestData[$date])) {
                    $guestCounts[] = $guestData[$date];
                } else {
                    $guestCounts[] = 0;
                }
                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
            }
            return response()->json(['guestCounts' => $guestCounts, 'customerCounts' => $customerCounts, 'Listdata' => $dateList]);
        } elseif ($period == 'this-month') {
            $currentYear = now()->year;
            $currentMonth = now();
            $currentMonthYear = $currentMonth->year;
            $currentMonthNumber = $currentMonth->month;

            // Calculate the last day of the last month
            $lastday = $currentMonth->endOfMonth()->day;

            $customerAppointments = Appointment::whereIn('customer_id', $customerIds)
                ->where('business_id', getActiveBusiness())
                ->where('created_by', creatorId())
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentMonthNumber])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('date')
                ->get()
                ->toArray();

            $guestAppointments = Appointment::whereNull('customer_id')
                ->where('business_id', getActiveBusiness())
                ->where('created_by', creatorId())
                ->whereRaw('YEAR(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentMonthYear])
                ->whereRaw('MONTH(STR_TO_DATE(`date`, "%d-%m-%Y")) = ?', [$currentMonthNumber])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('date')
                ->get()
                ->toArray();

            $customerData = [];
            foreach ($customerAppointments as $appointment) {
                $customerData[$appointment['date']] = $appointment['count'];
            }

            $guestData = [];
            foreach ($guestAppointments as $appointment) {
                $guestData[$appointment['date']] = $appointment['count'];
            }

            $customerCounts = [];
            $guestCounts = [];
            $dateList = [];

            for ($i = 1; $i <= $lastday; $i++) {
                $date = sprintf('%04d-%02d-%02d', $currentMonthYear, $currentMonthNumber, $i);

                if (isset($customerData[$date])) {
                    $customerCounts[] = $customerData[$date];
                } else {
                    $customerCounts[] = 0;
                }
                if (isset($guestData[$date])) {
                    $guestCounts[] = $guestData[$date];
                } else {
                    $guestCounts[] = 0;
                }
                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
            }
            return response()->json(['guestCounts' => $guestCounts, 'customerCounts' => $customerCounts, 'Listdata' => $dateList]);
        } elseif ($period == 'seven-day') {
            $endDate = date('d-m-Y');
            $startDate = date('d-m-Y', strtotime($endDate . ' -6 days'));
            $customerAppointments = Appointment::whereIn('customer_id', $customerIds)
                ->where('business_id', getActiveBusiness())
                ->where('created_by', creatorId())
                ->whereBetween('date', [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('date')
                ->get()
                ->toArray();

            $guestAppointments = Appointment::whereNull('customer_id')
                ->where('business_id', getActiveBusiness())
                ->where('created_by', creatorId())
                ->whereBetween('date', [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('date')
                ->get()
                ->toArray();

            $customerData = [];
            $guestData = [];

            foreach ($customerAppointments as $appointment) {
                $customerData[$appointment['date']] = $appointment['count'];
            }

            foreach ($guestAppointments as $appointment) {
                $guestData[$appointment['date']] = $appointment['count'];
            }

            $customerCounts = [];
            $guestCounts = [];
            $dateList = [];

            $currentDate = strtotime($startDate);
            $endDateUnix = strtotime($endDate);

            while ($currentDate <= $endDateUnix) {
                $date = date('Y-m-d', $currentDate);
                $customerCounts[] = $customerData[$date] ?? 0;
                $guestCounts[] = $guestData[$date] ?? 0;
                $currentDate = strtotime('+1 day', $currentDate);
                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
            }

            return response()->json(['guestCounts' => $guestCounts, 'customerCounts' => $customerCounts, 'Listdata' => $dateList]);
        } elseif ($period == 'between-date') {
            $dates = explode(' to ', $date);
            $startDate = date('d-m-Y', strtotime($dates[0]));
            $endDate = date('d-m-Y', strtotime($dates[1]));

            $customerAppointments = Appointment::whereIn('customer_id', $customerIds)
                ->where('business_id', getActiveBusiness())
                ->where('created_by', creatorId())
                ->whereBetween('date', [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('date')
                ->get()
                ->toArray();

            $guestAppointments = Appointment::whereNull('customer_id')
                ->where('business_id', getActiveBusiness())
                ->where('created_by', creatorId())
                ->whereBetween('date', [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d") as date, COUNT(*) as count')
                ->groupByRaw('DATE_FORMAT(STR_TO_DATE(`date`, "%d-%m-%Y"), "%Y-%m-%d")')
                ->orderBy('date')
                ->get()
                ->toArray();

            $customerData = [];
            $guestData = [];

            foreach ($customerAppointments as $appointment) {
                $customerData[$appointment['date']] = $appointment['count'];
            }

            foreach ($guestAppointments as $appointment) {
                $guestData[$appointment['date']] = $appointment['count'];
            }

            $customerCounts = [];
            $guestCounts = [];
            $dateList = [];

            $currentDate = strtotime($startDate);
            $endDateUnix = strtotime($endDate);

            while ($currentDate <= $endDateUnix) {
                $date = date('Y-m-d', $currentDate);
                $customerCounts[] = $customerData[$date] ?? 0;
                $guestCounts[] = $guestData[$date] ?? 0;
                $currentDate = strtotime('+1 day', $currentDate);
                $formattedDate = date('d-m-Y', strtotime($date));
                $dateList[] = $formattedDate;
            }

            return response()->json(['guestCounts' => $guestCounts, 'customerCounts' => $customerCounts, 'Listdata' => $dateList]);
        }
    }
    
}