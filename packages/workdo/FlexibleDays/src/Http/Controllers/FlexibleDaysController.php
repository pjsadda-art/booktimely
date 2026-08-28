<?php

namespace Workdo\FlexibleDays\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessHours;
use App\Models\Staff;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\FlexibleDays\Entities\FlexibleDayBreaks;
use Workdo\FlexibleDays\Entities\FlexibleDays;
use Workdo\FlexibleDays\Entities\FlexibleStaffHours;
use Workdo\ServiceSlotScheduler\Entities\ServiceScheduleDay;

class FlexibleDaysController extends Controller
{

    public function index()
    {
        return view('flexibledays::index');
    }

    public function create($id)
    {
        $staff = Staff::where('user_id', $id)->get()->first();
        $flexible_days = FlexibleDays::where('staff_id', $staff->user_id)->where('business_id', getActiveBusiness())->where('created_by', creatorId())->get();
        $flexible_breaks = FlexibleDayBreaks::where('business_id', getActiveBusiness())->where('created_by', creatorId())->get();
        $flexible_data = ['json_encode_data' => json_encode($flexible_days), 'flexible_breaks' => $flexible_breaks];
        return view('flexible-days::flexible_days.create', compact('staff', 'flexible_data'));
    }

    public function store(Request $request)
    {
        try {
            $post = $request->all();
            $flexible_days = $request->flexible_days;
            $staff_id = $post['staff_id'];
            unset($post['_token'], $post['staff_id'], $post['flexible_days']);
            foreach ($post as $key => $daydata) {
                $dayname = $key;
                $start = $daydata['start'] ?? "9:30";
                $end = $daydata['end'] ?? "18:00";
                $day_off = $daydata['day_off'] ?? 'off';
                $repeater = json_encode(isset($daydata['repeater']) ? $daydata['repeater'] : '');
                FlexibleStaffHours::updateOrCreate(['day_name' => $dayname, 'business_id' => getActiveBusiness(), 'created_by' => creatorId(), 'staff_id' => $staff_id], ['start_time' => $start, 'end_time' => $end, 'break_hours' => $repeater, 'day_off' => $day_off]);
            }
            if (isset($flexible_days)) {
                foreach ($flexible_days as $key => $flexible_day) {

                    $date = $flexible_day['date'] ?? '';
                    $start = $flexible_day['start_time'] ?? '';
                    $end = $flexible_day['end_time'] ?? '';
                    $id = isset($flexible_day['id']) ? $flexible_day['id']  : null;

                    if (!empty($date) && !empty($start) && !empty($end)) {
                        FlexibleDays::updateOrCreate(
                            [
                                'id' => $id
                            ],
                            [
                                'date' => $date,
                                'business_id' => getActiveBusiness(),
                                'created_by' => creatorId(),
                                'staff_id' => $staff_id,
                                'start_time' => $start,
                                'end_time' => $end
                            ]
                        );
                    }
                }
            }

            $tab = 3;
            return redirect()->back()->with('success', __('The staff flexible hours has been created successfully.'))->with('tab', $tab);
        } catch (Exception $e) {
            $tab = 3;
            \Log::info($e);
            return redirect()->back()->with('error', __('Something went wrong.'))->with('tab', $tab);
        }
    }

    public function flexibleDaysDelete(Request $request)
    {
        try {
            FlexibleDays::where('id', $request->staff_id)->delete();
            return response()->json(['success' => __('The staff flexible hours has been deleted.')]);
        } catch (Exception $e) {
            return response()->json(['error' => __('Something went wrong')]);
        }
    }

    public function dayOffCheck(Request $request)
    {
        try {
            $business = Business::where('id', $request->business)->first();
            $staff = FlexibleStaffHours::where('created_by', $business->created_by)
                    ->where('business_id', $business->id)
                    ->where('staff_id', $request->staff)->first();

            if (module_is_active('FlexibleDays', $business->created_by) && isset($staff)) {
                $busineshours = FlexibleStaffHours::where('created_by', $business->created_by)
                    ->where('business_id', $business->id)
                    ->where('staff_id', $request->staff)
                    ->where('day_off', 'on')
                    ->select('day_name')
                    ->get()
                    ->pluck('day_name')
                    ->map(function ($day) {
                        return date('w', strtotime($day));
                    })
                    ->toArray();
            } else {
                $busineshours = BusinessHours::where('created_by', $business->created_by)
                    ->where('business_id', $business->id)
                    ->where('day_off', 'on')
                    ->select('day_name')
                    ->get()
                    ->pluck('day_name')
                    ->map(function ($day) {
                        return date('w', strtotime($day));
                    })
                    ->toArray();
            }

            return response()->json(['dayOffArray' => $busineshours, 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error']);
        }
    }
}
