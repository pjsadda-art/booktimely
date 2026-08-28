<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Staff;
use App\Models\StaffShift;
use App\Services\RosterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffRosterController extends Controller
{
    protected RosterService $roster;

    public function __construct(RosterService $roster)
    {
        $this->roster = $roster;
    }

    protected function authorize_(): bool
    {
        return Auth::user()->isAbleTo('staff roster manage');
    }

    public function index(Request $request)
    {
        if (!$this->authorize_()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $locations = Location::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('staff_roster.index', compact('locations'));
    }

    /**
     * GET staff-roster/grid?location_id=&week_start=Y-m-d
     */
    public function grid(Request $request)
    {
        if (!$this->authorize_()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $location = Location::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->find($request->input('location_id'));

        if (empty($location)) {
            return response()->json(['error' => __('Location not found.')], 404);
        }

        $weekStart = $this->parseDate($request->input('week_start')) ?? Carbon::today();
        $weekStart = $weekStart->copy()->startOfWeek(Carbon::SUNDAY);

        return response()->json($this->roster->weekGrid($location, $weekStart));
    }

    /**
     * GET staff-roster/conflicts
     *
     * Admin review report (spec §4B): existing appointments dated on/after
     * the roster activation date that no longer fit the new roster (staff
     * has no covering shift, or the appointment time falls outside it).
     * Read-only — nothing here is auto-cancelled or moved.
     */
    public function conflicts(Request $request)
    {
        if (!$this->authorize_()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $conflicts = $this->roster->findRosterConflicts($businessId, $createdBy);

        return view('staff_roster.conflicts', compact('conflicts'));
    }

    /**
     * GET staff-roster/shifts/resolve?staff_id=&date=
     *
     * The grid endpoint returns resolved hours per cell, not a shift id (a
     * cell's hours can come from a casual, end-dated, or continuous row).
     * The delete flow needs the underlying row id, so it looks it up here
     * using the same resolution order as RosterService::hoursForStaffOnDate.
     */
    public function resolveShift(Request $request)
    {
        if (!$this->authorize_()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $staff = Staff::where('business_id', $businessId)->where('created_by', $createdBy)->find($request->input('staff_id'));
        $date = $this->parseDate($request->input('date'));

        if (empty($staff) || empty($date)) {
            return response()->json(['error' => __('Not found.')], 404);
        }

        $shift = StaffShift::where('staff_id', $staff->id)
            ->where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->active()
            ->onDate($date)
            ->orderByRaw("FIELD(shift_type, 'casual', 'end_dated', 'continuous')")
            ->first();

        if (empty($shift)) {
            return response()->json(['id' => null]);
        }

        return response()->json(['id' => $shift->id, 'shift_type' => $shift->shift_type]);
    }

    /**
     * POST staff-roster/shifts
     */
    public function store(Request $request)
    {
        if (!$this->authorize_()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $data = $this->validateShift($request);

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $staff = Staff::where('business_id', $businessId)->where('created_by', $createdBy)->find($data['staff_id']);
        $location = Location::where('business_id', $businessId)->where('created_by', $createdBy)->find($data['location_id']);

        if (empty($staff) || empty($location)) {
            return response()->json(['error' => __('Staff or location not found.')], 404);
        }

        $data['business_id'] = $businessId;
        $data['created_by'] = $createdBy;

        $shift = $this->roster->createOrUpdateShift($data);

        return response()->json(['success' => true, 'shift' => $shift]);
    }

    /**
     * PUT staff-roster/shifts/{id}
     *
     * Edits are always future-only: they are implemented as closing out the
     * existing row from today (or the requested date) and creating a fresh
     * one with the new values, so past dates are never touched.
     */
    public function update(Request $request, $id)
    {
        if (!$this->authorize_()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $shift = StaffShift::where('business_id', $businessId)->where('created_by', $createdBy)->find($id);

        if (empty($shift)) {
            return response()->json(['error' => __('Shift not found.')], 404);
        }

        $data = $this->validateShift($request);
        $data['business_id'] = $businessId;
        $data['created_by'] = $createdBy;
        $data['staff_id'] = $shift->staff_id;
        $data['location_id'] = $data['location_id'] ?? $shift->location_id;

        if ($shift->shift_type === 'casual') {
            $shift->delete();
        } else {
            $this->roster->deleteShift($shift, 'today_onward');
        }

        $new = $this->roster->createOrUpdateShift($data);

        return response()->json(['success' => true, 'shift' => $new]);
    }

    /**
     * DELETE staff-roster/shifts/{id}
     *
     * Always runs the appointment-conflict check first and returns a 409
     * with the conflict list when any exist (spec §8) — deletion is blocked
     * outright until every conflicting appointment is reassigned or
     * cancelled; there is no override. The UI re-checks by calling this
     * endpoint again after the user resolves the appointments.
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->authorize_()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $shift = StaffShift::where('business_id', $businessId)->where('created_by', $createdBy)->find($id);

        if (empty($shift)) {
            return response()->json(['error' => __('Shift not found.')], 404);
        }

        $mode = $request->input('mode', 'today_onward');
        $fromDate = $this->parseDate($request->input('from_date'));

        [$rangeFrom, $rangeTo] = $this->deletionRange($shift, $mode, $fromDate);

        $staff = $shift->staff;
        $conflicts = $this->roster->checkAppointmentConflicts($staff, $rangeFrom, $rangeTo, $businessId, $createdBy);

        if ($conflicts->isNotEmpty()) {
            return response()->json([
                'error' => __('This staff member has existing appointments during this shift period. Please reallocate all appointments before deleting this shift.'),
                'appointments' => $conflicts,
            ], 409);
        }

        $this->roster->deleteShift($shift, $mode, $fromDate);

        return response()->json(['success' => true]);
    }

    protected function deletionRange(StaffShift $shift, string $mode, ?Carbon $fromDate): array
    {
        if ($shift->shift_type === 'casual') {
            $date = $shift->specific_date;

            return [$date, $date];
        }

        $today = Carbon::today();
        $from = $mode === 'from_date' && $fromDate ? $fromDate->copy()->max($today) : $today;
        $to = $shift->effective_to ?: $from->copy()->addYear();

        return [$from, $to];
    }

    protected function validateShift(Request $request): array
    {
        $shiftType = $request->input('shift_type');

        $rules = [
            'staff_id' => 'required|integer',
            'location_id' => 'required|integer',
            'shift_type' => 'required|in:continuous,end_dated,casual',
            'start_time' => 'required',
            'end_time' => 'required',
        ];

        if ($shiftType === 'casual') {
            $rules['specific_date'] = 'required|date';
        } elseif ($shiftType === 'end_dated') {
            $rules['effective_from'] = 'required|date';
            $rules['effective_to'] = 'required|date|after_or_equal:effective_from';
            $rules['weekday'] = 'required|integer|min:0|max:6';
        } else {
            $rules['effective_from'] = 'nullable|date';
            $rules['weekday'] = 'required|integer|min:0|max:6';
        }

        $validated = $request->validate($rules);

        if ($shiftType === 'casual' && empty($validated['weekday'] ?? null)) {
            $validated['weekday'] = Carbon::parse($validated['specific_date'])->dayOfWeek;
        }

        return $validated;
    }

    protected function parseDate($date): ?Carbon
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }
}
