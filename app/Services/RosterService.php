<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Location;
use App\Models\Staff;
use App\Models\StaffShift;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Single source of truth for staff working hours and shift management.
 *
 * Resolution order for a given staff/date (see hoursForStaffOnDate):
 *   1. a `casual` shift on that exact date
 *   2. an active `end_dated` shift covering that date
 *   3. an active `continuous` shift for that weekday
 *   4. null — caller falls back to BusinessHours/BusinessHoliday as before.
 *
 * Nothing here ever mutates a past-dated row: edits and deletes only ever
 * close a row out (effective_to) or add a new one from today/a future date
 * onward, so historical roster data is preserved exactly as it was.
 */
class RosterService
{
    /**
     * Roster activation date. Before this date, the roster is not consulted
     * at all and every availability check must fall back to the legacy
     * BusinessHours/FlexibleDays logic exactly as before. On and after this
     * date, the roster is authoritative for every future-dated appointment
     * (calendar, manual creation, online booking widgets, dashboard) and
     * legacy hours are never used as a fallback — a staff member with no
     * covering shift is simply unavailable that day. Past appointments keep
     * referencing whichever logic was authoritative on their own date, which
     * this class does not touch: it only ever answers "what applies on this
     * date", never rewrites history.
     */
    public const ACTIVATION_DATE = '2026-08-24';

    protected AppointmentStatusClassifier $classifier;

    public function __construct(?AppointmentStatusClassifier $classifier = null)
    {
        $this->classifier = $classifier ?: new AppointmentStatusClassifier();
    }

    public function isActivated(Carbon $date): bool
    {
        return $date->toDateString() >= self::ACTIVATION_DATE;
    }

    /**
     * The single active shift covering a staff/date, in casual > end_dated >
     * continuous priority order, or null when none covers that date.
     */
    public function shiftForStaffOnDate(Staff $staff, Carbon $date): ?StaffShift
    {
        return StaffShift::where('staff_id', $staff->id)
            ->active()
            ->onDate($date)
            ->orderByRaw("FIELD(shift_type, 'casual', 'end_dated', 'continuous')")
            ->first();
    }

    /**
     * @return array{start:string,end:string}|null
     */
    public function hoursForStaffOnDate(Staff $staff, Carbon $date): ?array
    {
        $shift = $this->shiftForStaffOnDate($staff, $date);

        if (empty($shift)) {
            return null;
        }

        return [
            'start' => date('H:i', strtotime($shift->start_time)),
            'end' => date('H:i', strtotime($shift->end_time)),
        ];
    }

    /**
     * Weekly grid for a location: staff rows x 7 day columns, each cell
     * resolved hours plus per-staff and per-day total hours.
     */
    public function weekGrid(Location $location, Carbon $weekStart): array
    {
        $weekStart = $weekStart->copy()->startOfDay();
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $days[] = $weekStart->copy()->addDays($i);
        }

        $staffMembers = $location->staff()
            ->where('staff.business_id', $location->business_id)
            ->get();

        $rows = [];
        $dayTotals = array_fill(0, 7, 0.0);

        foreach ($staffMembers as $staff) {
            $cells = [];
            $staffTotal = 0.0;

            foreach ($days as $index => $date) {
                $hours = $this->hoursForStaffOnDate($staff, $date);
                $durationHours = 0.0;

                if ($hours !== null) {
                    $durationHours = $this->durationHours($hours['start'], $hours['end']);
                }

                $cells[] = [
                    'date' => $date->toDateString(),
                    'hours' => $hours,
                    'duration_hours' => $durationHours,
                ];

                $staffTotal += $durationHours;
                $dayTotals[$index] += $durationHours;
            }

            $rows[] = [
                'staff_id' => $staff->id,
                'staff_name' => $staff->name,
                'cells' => $cells,
                'total_hours' => $staffTotal,
            ];
        }

        return [
            'week_start' => $weekStart->toDateString(),
            'days' => array_map(fn ($d) => $d->toDateString(), $days),
            'rows' => $rows,
            'day_totals' => $dayTotals,
            'grand_total' => array_sum($dayTotals),
        ];
    }

    protected function durationHours(string $start, string $end): float
    {
        $startMinutes = $this->toMinutes($start);
        $endMinutes = $this->toMinutes($end);

        if ($endMinutes <= $startMinutes) {
            return 0.0;
        }

        return round(($endMinutes - $startMinutes) / 60, 2);
    }

    protected function toMinutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time));

        return ($h * 60) + $m;
    }

    /**
     * Create a shift, or (for continuous shifts) supersede whatever
     * continuous shift already covers that staff/weekday. `effective_from`
     * is clamped so nothing can be backdated.
     */
    public function createOrUpdateShift(array $data): StaffShift
    {
        $today = Carbon::today();
        $effectiveFrom = Carbon::parse($data['effective_from'] ?? $today)->max($today);

        if ($data['shift_type'] === 'continuous') {
            $existing = StaffShift::where('staff_id', $data['staff_id'])
                ->where('weekday', $data['weekday'])
                ->where('shift_type', 'continuous')
                ->active()
                ->first();

            if ($existing) {
                $existing->status = 'superseded';
                $existing->effective_to = $effectiveFrom->copy()->subDay();
                $existing->save();
            }
        }

        return StaffShift::create([
            'staff_id' => $data['staff_id'],
            'location_id' => $data['location_id'],
            'weekday' => $data['weekday'],
            'shift_type' => $data['shift_type'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'effective_from' => $data['shift_type'] === 'casual' ? $data['specific_date'] : $effectiveFrom,
            'effective_to' => $data['shift_type'] === 'end_dated' ? $data['effective_to'] : null,
            'specific_date' => $data['shift_type'] === 'casual' ? $data['specific_date'] : null,
            'status' => 'active',
            'replaces_shift_id' => $existing->id ?? null,
            'business_id' => $data['business_id'],
            'created_by' => $data['created_by'],
        ]);
    }

    /**
     * Appointments for this staff within the given range that are not
     * cancelled/no-show, shaped for the conflict warning list (spec §8).
     */
    public function checkAppointmentConflicts(Staff $staff, Carbon $from, ?Carbon $to, $businessId, $createdBy): Collection
    {
        $to = $to ?: $from;

        $appointments = Appointment::with(['ServiceData', 'CustomerData', 'StatusData'])
            ->where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->where('staff_id', $staff->user_id)
            ->whereRaw(
                "STR_TO_DATE(`date`, '%d-%m-%Y') BETWEEN ? AND ?",
                [$from->toDateString(), $to->toDateString()]
            )
            ->get();

        $slugMap = $this->classifier->slugMap($businessId, $createdBy);

        $conflicts = $appointments->filter(function ($appointment) use ($slugMap) {
            $classification = $this->classifier->classify(
                optional($appointment->StatusData)->title,
                $appointment->appointment_status,
                $slugMap
            );

            return $classification === AppointmentStatusClassifier::OTHER;
        });

        return $conflicts->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'date' => $appointment->date,
                'time' => $appointment->time,
                'service' => optional($appointment->ServiceData)->name,
                'customer' => optional($appointment->CustomerData)->name,
            ];
        })->values();
    }

    /**
     * Hard server-side validation for manual/calendar appointment creation
     * (spec §3.2). Returns null when the slot is acceptable, or an error
     * message otherwise. Before the activation date this always returns
     * null — the roster is not consulted, matching legacy behaviour where
     * manual creation had no server-side hours validation at all.
     */
    public function validateAppointmentSlot(Staff $staff, Carbon $date, string $startTime, string $endTime): ?string
    {
        if (!$this->isActivated($date)) {
            return null;
        }

        $hours = $this->hoursForStaffOnDate($staff, $date);

        if ($hours === null) {
            return __('Selected staff member is not rostered on for this date.');
        }

        if ($startTime < $hours['start'] || $endTime > $hours['end']) {
            return __('Selected time is outside the staff member\'s rostered hours (:start - :end).', [
                'start' => $hours['start'],
                'end' => $hours['end'],
            ]);
        }

        return null;
    }

    /**
     * Existing appointments dated on/after the activation date that no
     * longer fit the new roster (spec §4B) — the staff member has no
     * covering shift, or the appointment falls outside the shift's hours.
     * Flagged for admin review; nothing here is auto-cancelled or moved.
     */
    public function findRosterConflicts($businessId, $createdBy): Collection
    {
        $appointments = Appointment::with(['ServiceData', 'CustomerData', 'StatusData'])
            ->where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->whereRaw("STR_TO_DATE(`date`, '%d-%m-%Y') >= ?", [self::ACTIVATION_DATE])
            ->get();

        $slugMap = $this->classifier->slugMap($businessId, $createdBy);
        $staffByUserId = Staff::where('business_id', $businessId)->where('created_by', $createdBy)->get()->keyBy('user_id');

        return $appointments->filter(function ($appointment) use ($slugMap, $staffByUserId) {
            $classification = $this->classifier->classify(
                optional($appointment->StatusData)->title,
                $appointment->appointment_status,
                $slugMap
            );

            if ($classification !== AppointmentStatusClassifier::OTHER) {
                return false; // cancelled/no-show appointments are not live conflicts
            }

            $staff = $staffByUserId->get($appointment->staff_id);

            if (empty($staff)) {
                return false;
            }

            try {
                $date = Carbon::createFromFormat('d-m-Y', $appointment->date)->startOfDay();
            } catch (\Exception $e) {
                return false;
            }

            $parts = array_map('trim', explode('-', (string) $appointment->time, 2));

            if (count($parts) !== 2 || empty($parts[0]) || empty($parts[1])) {
                return false;
            }

            return $this->validateAppointmentSlot($staff, $date, $parts[0], $parts[1]) !== null;
        })->map(function ($appointment) use ($staffByUserId) {
            $staff = $staffByUserId->get($appointment->staff_id);

            return [
                'id' => $appointment->id,
                'date' => $appointment->date,
                'time' => $appointment->time,
                'staff' => optional($staff)->name,
                'service' => optional($appointment->ServiceData)->name,
                'customer' => optional($appointment->CustomerData)->name,
            ];
        })->values();
    }

    /**
     * Delete a shift per spec §9. Callers must have already confirmed there
     * are no appointment conflicts in the affected range (or the user chose
     * to proceed after reallocating/cancelling them).
     */
    public function deleteShift(StaffShift $shift, string $mode, ?Carbon $from = null): void
    {
        if ($shift->shift_type === 'casual') {
            $shift->delete();

            return;
        }

        $today = Carbon::today();
        $cutoff = $mode === 'from_date' && $from ? $from->copy()->max($today) : $today;

        if ($cutoff->lte($shift->effective_from)) {
            $shift->delete();

            return;
        }

        $shift->effective_to = $cutoff->copy()->subDay();
        $shift->status = $shift->effective_to->lt($shift->effective_from) ? 'cancelled' : 'active';
        $shift->save();
    }
}
