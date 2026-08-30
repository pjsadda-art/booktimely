<?php

namespace App\Http\Controllers;

use App\Events\CreateAppoinment;
use App\Models\Appointment;
use App\Models\AppointmentPayment;
use App\Models\BusinessHours;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\CustomStatus;
use App\Models\EmailTemplate;
use App\Models\Location;
use App\Models\Service;
use App\Models\Staff;
use App\Services\RosterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Booking V2 calendar — display side only.
 *
 * Renders a FullCalendar Scheduler `resourceTimeGridDay` calendar backed by two
 * AJAX feeds: `staffs()` supplies the resource columns (one per staff member,
 * carrying that member's businessHours) and `events()` supplies the appointment
 * chips. Creating and editing appointments stays with AppointmentController.
 *
 * Notes on this port:
 *  - There is no `block_times` table in this schema, so the events feed emits
 *    appointments only. Every event still carries a `type` discriminator and a
 *    prefixed id ("appt-123") so block times can be appended later without
 *    colliding ids downstream.
 *  - There is no `custom_statuses.show_on_appointment_calendar` column, so the
 *    visibility gate is inert and every status renders. isStatusHidden() honours
 *    the column automatically if it is ever added.
 *  - There is no per-staff roster table, so every staff member inherits the
 *    business-wide working hours for the weekday being viewed.
 */
class BookingV2Controller extends Controller
{
    /** Chip colour used when a status carries no colour of its own. */
    protected const FALLBACK_COLOR = '#00ffff';

    /** Every businessHours window applies to all seven weekdays; the feed is per-date. */
    protected const ALL_DAYS = [0, 1, 2, 3, 4, 5, 6];

    /**
     * The calendar page itself. All JS lives inline in the view.
     */
    public function calendar(Request $request)
    {
        if (!Auth::user()->isAbleTo('appointment manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $locations = Location::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->orderBy('name')
            ->pluck('name', 'id');

        // Shaped here rather than in the view: the panel's status <select> only
        // needs id and title.
        $statuses = CustomStatus::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->orderBy('id')
            ->get()
            ->map(function ($status) {
                return ['id' => $status->id, 'title' => $status->title];
            })
            ->values();

        // Extra entries for the staff-mode picker, so a single column can be isolated.
        $staffOptions = Staff::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->orderBy('name')
            ->pluck('name', 'user_id');

        $weekStartDay = company_setting('week_start_day', Auth::user()->id, $businessId);
        $weekStartDay = is_numeric($weekStartDay) ? (int) $weekStartDay : 0;

        $slotInterval = $this->slotInterval();
        $calendarBufferBefore = $this->calendarBufferBefore();
        $calendarBufferAfter = $this->calendarBufferAfter();
        $licenseKey = config('fullcalendar.scheduler_license_key');
        $selectedLocation = $request->input('location_id');
        $currencySymbol = company_setting('defult_currancy_symbol', Auth::user()->id, $businessId) ?: '$';

        return view('appointment.booking-v2', compact(
            'locations',
            'statuses',
            'staffOptions',
            'weekStartDay',
            'slotInterval',
            'calendarBufferBefore',
            'calendarBufferAfter',
            'licenseKey',
            'selectedLocation',
            'currencySymbol'
        ));
    }

    /**
     * Move an appointment to a new slot and/or staff column (drag-drop).
     *
     * POST /bookings-v2/appointments/{id}/move  { date: d-m-Y, time: HH:MM-HH:MM, staff_id }
     *
     * Move-only: duration is fixed on the client (eventDurationEditable: false),
     * so this touches date, time and staff and nothing else. It deliberately
     * does not fire the reschedule notifications that the edit flow owns.
     */
    public function moveAppointment(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('appointment edit')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $request->validate([
            'date' => 'required|date_format:d-m-Y',
            'time' => ['required', 'regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/'],
            'staff_id' => 'nullable|numeric',
        ]);

        $appointment = Appointment::where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->find($id);

        if (empty($appointment)) {
            return response()->json(['error' => __('Appointment not found.')], 404);
        }

        $appointment->date = $request->input('date');
        $appointment->time = $request->input('time');

        if ($request->filled('staff_id')) {
            $appointment->staff_id = $request->input('staff_id');
        }

        $appointment->save();

        return response()->json([
            'success' => true,
            'message' => __('Appointment successfully moved.'),
        ]);
    }

    /**
     * Resources feed — one entry per staff column.
     *
     * GET /bookings-v2/staffs?date=<Y-m-d>&location_id=<id>&staff_id=<mode>
     *
     * `staff_id` selects the column set:
     *   roster           — staff at the location (default)
     *   all_appointments — only staff holding an appointment on that date
     *   <numeric id>     — that one staff member
     *
     * Responds with X-Staff-Mismatch: 1 when somebody holds an appointment that
     * day but does not appear in the returned columns, so the client can offer
     * to switch modes rather than silently dropping their chips.
     */
    public function staffs(Request $request)
    {
        if (!Auth::user()->isAbleTo('appointment manage')) {
            return response()->json([], 403);
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $date = $this->parseRequestDate($request->input('date')) ?? Carbon::today();
        $locationId = $request->input('location_id');
        $mode = $request->input('staff_id', 'roster');

        $allStaff = Staff::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->get();

        $staff = $allStaff;

        if (!empty($locationId)) {
            $staff = $staff->filter(function ($member) use ($locationId) {
                // Staff::$location_id is a CSV column exposed as an array by the model accessor.
                return in_array((string) $locationId, array_map('strval', (array) $member->location_id), true);
            });
        }

        // Staff holding an appointment on this date, used both by the
        // all_appointments mode and by the orphan check below.
        $bookedStaffIds = $this->bookedStaffIds($date, $locationId);

        if ($mode === 'all_appointments') {
            $staff = $staff->filter(function ($member) use ($bookedStaffIds) {
                return in_array((string) $member->user_id, $bookedStaffIds, true);
            });
        } elseif (is_numeric($mode)) {
            $staff = $staff->filter(function ($member) use ($mode) {
                return (string) $member->user_id === (string) $mode;
            });
        }

        $fallbackBusinessHours = $this->businessHoursFor($date, $businessId, $createdBy);
        $roster = app(RosterService::class);
        $rosterActive = $roster->isActivated($date);

        $resources = $staff->values()->map(function ($member) use ($fallbackBusinessHours, $roster, $date, $rosterActive) {
            if ($rosterActive) {
                // Roster activation date reached: the roster is authoritative
                // and legacy business hours are never used as a fallback — no
                // covering shift means the column is fully shaded (closed).
                $staffHours = $roster->hoursForStaffOnDate($member, $date);
                $businessHours = $staffHours !== null
                    ? [['startTime' => $staffHours['start'], 'endTime' => $staffHours['end'], 'daysOfWeek' => self::ALL_DAYS]]
                    : false;
            } else {
                // Before activation: roster is not consulted at all.
                $businessHours = $fallbackBusinessHours;
            }

            return [
                'id' => (string) $member->user_id,   // must match Appointment::$staff_id exactly
                'title' => $member->name,
                // `false` shades the whole column: staff/business is closed that day.
                'businessHours' => empty($businessHours) ? false : $businessHours,
            ];
        })->all();

        // An appointment whose staff has no column would vanish silently, so in
        // the default view append those staff as off-roster columns (fully
        // shaded) rather than dropping their chips. Narrowing modes are the
        // user's explicit choice and are left alone.
        $orphanIds = array_values(array_diff($bookedStaffIds, array_column($resources, 'id')));

        if ($mode !== 'all_appointments' && !is_numeric($mode)) {
            foreach ($orphanIds as $orphanId) {
                $member = $allStaff->firstWhere('user_id', $orphanId);

                $resources[] = [
                    'id' => (string) $orphanId,
                    'title' => $member->name ?? (__('Staff') . ' #' . $orphanId),
                    'businessHours' => false,
                ];
            }
        }

        // Alphabetical, matching resourceOrder on the client.
        usort($resources, function ($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });

        return response()->json($resources)
            ->header('X-Staff-Mismatch', count($orphanIds) > 0 ? '1' : '0');
    }

    /**
     * Events feed — appointment chips for the requested day.
     *
     * GET /bookings-v2/events?date=<Y-m-d>&location_id=<id>
     * A start/end range is also accepted for multi-day callers.
     *
     * The business's slot granularity rides along on every response as
     * X-Slot-Interval so the client can re-tune the grid without a page reload.
     */
    public function events(Request $request)
    {
        if (!Auth::user()->isAbleTo('appointment manage')) {
            return response()->json([], 403);
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $query = Appointment::with(['ServiceData', 'CustomerData', 'StatusData', 'StaffData'])
            ->where('business_id', $businessId)
            ->where('created_by', $createdBy);

        $this->applyDateFilter($query, $request);

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->input('location_id'));
        }

        if ($request->filled('staff_id') && is_numeric($request->input('staff_id'))) {
            $query->where('staff_id', $request->input('staff_id'));
        }

        $events = [];

        foreach ($query->get() as $appointment) {
            $status = $appointment->StatusData;

            if ($this->isStatusHidden($status)) {
                continue;
            }

            $window = $this->eventWindow($appointment);

            if ($window === null) {
                continue; // unparseable date/time — better absent than stacked at midnight
            }

            $service = $appointment->ServiceData;
            $customer = $appointment->CustomerData;

            $serviceName = $service->name ?? __('Service') . ' #' . $appointment->service_id;
            $customerName = $customer->name ?? ($appointment->name ?: __('Guest'));

            $events[] = [
                // Prefixed so block-time ids can join this array later without colliding.
                'id' => 'appt-' . $appointment->id,
                'appointment_id' => $appointment->id,
                'title' => $serviceName . ' - ' . $customerName,
                'resourceId' => (string) $appointment->staff_id,
                'start' => $window['start'],
                'end' => $window['end'],
                'color' => $this->statusColor($status),
                'url' => route('appointment.details', $appointment->id),
                'allDay' => false,
                'type' => 'appointment',
                'staff_name' => $appointment->StaffData->name ?? '-',
                'customer_name' => $customerName,
                'service_name' => $serviceName,
                'status_name' => $status->title ?? __('Pending'),
                'price' => $service->price ?? null,
            ];
        }

        return response()->json($events)
            ->header('X-Slot-Interval', (string) $this->slotInterval());
    }

    /**
     * Detail panel payload for a single appointment.
     *
     * GET /bookings-v2/appointments/{id}
     *
     * `group` is an array even though this schema stores one service per
     * appointment row and has no `common_number` to join rows by: a real
     * multi-service booking group would populate the same shape, so the panel
     * needs no changes if grouping is added later.
     */
    public function getAppointment($id)
    {
        if (!Auth::user()->isAbleTo('appointment manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $appointment = Appointment::with(['ServiceData', 'CustomerData', 'StatusData', 'StaffData', 'LocationData', 'payment', 'jobCard.files'])
            ->where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->find($id);

        if (empty($appointment)) {
            return response()->json(['error' => __('Appointment not found.')], 404);
        }

        $jobCardFiles = $appointment->jobCard && $appointment->jobCard->files
            ? $appointment->jobCard->files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->original_name,
                    'url' => check_file($file->file_path) ? get_file($file->file_path) : null,
                ];
            })->values()->all()
            : [];

        $service = $appointment->ServiceData;
        $customer = $appointment->CustomerData;
        $customerUser = $customer && $customer->customer ? $customer->customer : null;

        $mobile = $customerUser->mobile_no ?? $appointment->contact;
        $email = $customerUser->email ?? $appointment->email;

        // Booked values, not catalogue values: duration comes from the stored
        // time range and price from the payment row, so an override survives a
        // later edit to the Service record.
        $group = [[
            'id' => $appointment->id,
            'service' => $service->name ?? __('Service') . ' #' . $appointment->service_id,
            'service_id' => $appointment->service_id,
            'staff' => $appointment->StaffData->name ?? '-',
            'staff_id' => $appointment->staff_id,
            'time' => $appointment->time,
            'duration' => $this->durationFromRange($appointment->time),
            'price' => $appointment->payment
                ? (float) $appointment->payment->amount
                : (float) ($service->price ?? 0),
            'catalog_duration' => (int) ($service->duration ?? 0),
            'catalog_price' => (float) ($service->price ?? 0),
        ]];

        $total = 0;
        foreach ($group as $row) {
            $total += (float) $row['price'];
        }

        $history = $this->customerHistory($appointment);
        $invoice = $this->invoiceLinks($appointment);
        $deposit = $this->depositState($appointment);
        $whatsapp = $this->whatsappState($appointment, $mobile);

        return response()->json([
            'id' => $appointment->id,
            'status_id' => $appointment->appointment_status,
            'appointment_number' => Appointment::appointmentNumberFormat(
                $appointment->id,
                $appointment->created_by,
                $appointment->business_id
            ),
            'customer' => [
                'id' => $appointment->customer_id,
                'name' => $customer->name ?? ($appointment->name ?: __('Guest')),
                'mobile' => $mobile,
                'email' => $email,
                // No notification-preference column exists, so the badges reflect
                // which channels are actually reachable for this customer.
                'notification' => $this->notificationChannels($mobile, $email),
                'is_walkin' => empty($appointment->customer_id),
            ],
            'date' => $appointment->date,
            'time' => $appointment->time,
            'location' => [
                'id' => $appointment->location_id,
                'name' => $appointment->LocationData->name ?? '-',
            ],
            'total_amount' => $total,
            'notes' => $appointment->notes,
            'payment_type' => $appointment->payment_type,
            'invoice_id' => $invoice['invoice_id'],
            'invoice_url' => $invoice['invoice_url'],
            'checkout_url' => $invoice['checkout_url'],
            'deposit_invoice_id' => $invoice['deposit_invoice_id'],
            'deposit_invoice_url' => $invoice['deposit_invoice_url'],
            'invoice_status' => $deposit['invoice_status'],
            'deposit' => $deposit,
            'whatsapp' => $whatsapp,
            'statuses' => $this->statusMap(),
            // Vehicle details (REGO, Make & Model, …) live here as a label =>
            // value map; definitions come from the form-data feed.
            'custom_fields' => $this->readCustomFields($appointment),
            'custom_field_definitions' => $this->customFieldDefinitions(),
            'group' => $group,
            'previous' => $history['previous'],
            'upcoming' => $history['upcoming'],
            // No SMS log table exists in this schema; the panel hides the
            // section when this is empty.
            'sms_history' => [],
            'job_card_enabled' => jobCardFeatureEnabled(),
            'job_card_files' => jobCardFeatureEnabled() ? $jobCardFiles : [],
            'details_url' => route('appointment.details', $appointment->id),
        ]);
    }

    /**
     * Everything the create/edit forms need in one round trip.
     *
     * GET /bookings-v2/form-data?date=<Y-m-d>&location_id=<id>
     */
    public function formData(Request $request)
    {
        if (!Auth::user()->isAbleTo('appointment manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $date = $this->parseRequestDate($request->input('date')) ?? Carbon::today();
        $locationId = $request->input('location_id');

        $staff = Staff::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->orderBy('name')
            ->get();

        if (!empty($locationId)) {
            $staff = $staff->filter(function ($member) use ($locationId) {
                return in_array((string) $locationId, array_map('strval', (array) $member->location_id), true);
            });
        }

        // Services grouped by category, matching the <optgroup> structure the
        // service dropdown renders.
        $services = Service::with('Category')
            ->where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->orderBy('name')
            ->get();

        $grouped = [];

        foreach ($services as $service) {
            $category = $service->Category->name ?? __('Uncategorised');

            if (!isset($grouped[$category])) {
                $grouped[$category] = ['category' => $category, 'items' => []];
            }

            $grouped[$category]['items'][] = [
                'id' => $service->id,
                'name' => $service->name,
                'duration' => (int) $service->duration,
                'price' => (float) $service->price,
            ];
        }

        return response()->json([
            'staff' => $staff->values()->map(function ($member) {
                return ['id' => (string) $member->user_id, 'title' => $member->name];
            })->all(),
            'services' => array_values($grouped),
            'statuses' => $this->statusList(),
            'default_status' => resolveDefaultAppointmentStatus('calendar', $businessId, $createdBy)['status_id'],
            'business_hours' => $this->businessHoursFor($date, $businessId, $createdBy),
            'slot_interval' => $this->slotInterval(),
            'custom_fields_enabled' => $this->customFieldsEnabled(),
            'custom_fields' => $this->customFieldDefinitions(),
        ]);
    }

    /**
     * Customer typeahead for the create panel.
     *
     * GET /bookings-v2/customers?q=<term>
     *
     * Separate from the existing customers.search endpoint because the panel's
     * two-line result template needs name/mobile/email as distinct fields
     * rather than one pre-formatted label.
     */
    public function searchCustomers(Request $request)
    {
        if (!Auth::user()->isAbleTo('appointment manage')) {
            return response()->json(['results' => []], 403);
        }

        $term = trim((string) $request->input('q'));

        $customers = Customer::with('customer')
            ->where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', '%' . $term . '%')
                        ->orWhereHas('customer', function ($user) use ($term) {
                            $user->where('mobile_no', 'like', '%' . $term . '%')
                                ->orWhere('email', 'like', '%' . $term . '%');
                        });
                });
            })
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $customers->map(function ($customer) {
                return [
                    'id' => $customer->user_id,
                    'name' => $customer->name,
                    'mobile' => $customer->customer->mobile_no ?? '',
                    'email' => $customer->customer->email ?? '',
                ];
            })->values(),
        ]);
    }

    /**
     * "Convert to Appointment" on an accepted quotation
     * (proposal/modern-view.blade.php) — the customer plus each line item
     * that resolves to a real bookable Service (a Parts/product line has
     * nothing to book, so it's silently skipped), for booking-v2.blade.php
     * to hand straight to the same New Appointment panel everyone else
     * uses. Nothing is written here — staff still pick date/time/staff and
     * save it themselves, same as any other new appointment.
     *
     * GET /bookings-v2/proposal-prefill/{e_id}
     */
    public function proposalPrefill($e_id)
    {
        if (!Auth::user()->isAbleTo('appointment manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        try {
            $id = \Illuminate\Support\Facades\Crypt::decrypt($e_id);
        } catch (\Throwable $th) {
            return response()->json(['error' => __('Quotation not found.')], 404);
        }

        $businessId = getActiveBusiness();

        $proposal = \Workdo\Invoice\Entities\Proposal::where('id', $id)
            ->where('business_id', $businessId)
            ->first();

        if (!$proposal) {
            return response()->json(['error' => __('Quotation not found.')], 404);
        }

        // Index 2 = Accepted (Proposal::$statues) — mirrors the same gate
        // ProposalController::convert() enforces for Convert to Invoice.
        if ((int) $proposal->status !== 2) {
            return response()->json(['error' => __('Only accepted quotations can be converted to an appointment.')], 422);
        }

        $customerUser = $proposal->customer;

        $serviceIds = [];

        foreach ($proposal->items as $item) {
            $productService = \Workdo\ProductService\Entities\ProductService::where('id', $item->product_id)
                ->where('business_id', $businessId)
                ->first();

            if (!$productService || $productService->type !== 'service' || empty($productService->service_id)) {
                continue;
            }

            $units = max(1, (int) round($item->quantity));

            for ($i = 0; $i < $units; $i++) {
                $serviceIds[] = $productService->service_id;
            }
        }

        return response()->json([
            'customer' => $customerUser ? [
                'id' => $proposal->customer_id,
                'name' => $customerUser->name,
                'mobile' => $customerUser->mobile_no,
                'email' => $customerUser->email,
            ] : null,
            'serviceIds' => $serviceIds,
        ]);
    }

    /**
     * Previous/upcoming appointments for a customer, relative to today.
     *
     * GET /bookings-v2/customers/{id}/history
     *
     * Used by the create panel once a real customer is picked; walk-ins have no
     * record to look up.
     */
    public function customerAppointments($id)
    {
        if (!Auth::user()->isAbleTo('appointment manage')) {
            return response()->json(['previous' => [], 'upcoming' => []], 403);
        }

        return response()->json(
            $this->historyFor($id, Carbon::today(), null)
        );
    }

    /**
     * Create-panel save.
     *
     * POST /bookings-v2/appointments
     * {
     *   location_id, customer_id, staff_id, date, start_time, status_id, notes,
     *   custom_fields: { label: value },
     *   services: [ { service_id, duration, price } ]
     * }
     *
     * One booking is one staff member. This schema stores one service per
     * appointment row, so each service chip still becomes its own row, with
     * times cascading: every service starts where the previous one ended, which
     * is what the panel previews before saving.
     */
    public function storeAppointments(Request $request)
    {
        if (!Auth::user()->isAbleTo('appointment create')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = \Validator::make($request->all(), [
            'location_id' => 'required|numeric',
            'customer_id' => 'required|numeric',
            'staff_id' => 'required|numeric',
            'date' => 'required|date_format:d-m-Y',
            'start_time' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|numeric',
            'services.*.duration' => 'nullable|numeric|min:1|max:1440',
            'services.*.price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();
        // Manual/direct appointments made from the calendar are treated the
        // same as calendar bookings, so both use the calendar channel's
        // configured default status + notification template.
        $defaults = resolveDefaultAppointmentStatus('calendar', $businessId, $createdBy);
        $defaultStatus = $defaults['status_id'];

        // One car, one set of vehicle details — stored on every row the booking
        // creates, since custom_field lives per appointment row.
        $customFields = $this->sanitizeCustomFields($request->input('custom_fields'));
        $customFieldJson = !empty($customFields) ? json_encode($customFields) : null;

        $created = [];

        // One booking of three services is three rows sharing one group. Money
        // and status apply to the group, so a deposit raised against any row
        // covers the whole booking.
        $commonNumber = Appointment::newCommonNumber();

        try {
            DB::beginTransaction();

            $cursor = $request->input('start_time');

            foreach ($request->input('services') as $line) {
                $service = Service::where('business_id', $businessId)
                    ->where('created_by', $createdBy)
                    ->find($line['service_id']);

                if (empty($service)) {
                    continue;
                }

                // Per-booking overrides fall back to the service catalogue.
                $duration = isset($line['duration']) && $line['duration'] !== null && $line['duration'] !== ''
                    ? (int) $line['duration']
                    : (int) $service->duration;

                $price = isset($line['price']) && $line['price'] !== null && $line['price'] !== ''
                    ? (float) $line['price']
                    : (float) $service->price;

                $end = $this->addMinutes($cursor, $duration);

                $appointment = new Appointment();
                $appointment->common_number = $commonNumber;
                $appointment->customer_id = $request->input('customer_id');
                $appointment->location_id = $request->input('location_id');
                $appointment->service_id = $service->id;
                $appointment->staff_id = $request->input('staff_id');
                $appointment->date = $request->input('date');
                $appointment->time = $cursor . '-' . $end;
                $appointment->notes = $request->input('notes', '');
                $appointment->appointment_status = $request->filled('status_id')
                    ? $request->input('status_id')
                    : $defaultStatus;
                $appointment->payment_type = 'Manually';
                $appointment->custom_field = $customFieldJson;
                $appointment->business_id = $businessId;
                $appointment->created_by = $createdBy;
                $appointment->save();

                AppointmentPayment::create([
                    'appointment_id' => $appointment->id,
                    'payment_type' => $appointment->payment_type,
                    'amount' => $price,
                    'payment_date' => now(),
                    'business_id' => $businessId,
                    'created_by' => $createdBy,
                ]);

                $created[] = $appointment;
                $cursor = $end;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => __('Could not save the appointment.')], 500);
        }

        if (empty($created)) {
            return response()->json(['error' => __('No valid services were selected.')], 422);
        }

        // Fired after the transaction so listeners never observe a rolled-back row.
        foreach ($created as $appointment) {
            $this->dispatchCreated($appointment, $request);
        }

        // Evaluated once for the booking, not once per row: three services in
        // one booking is one deposit, raised against the whole group.
        $deposit = $this->evaluateDeposit($created[0]);

        return response()->json([
            'success' => true,
            'created' => count($created),
            'deposit_requested' => !empty($deposit),
            'message' => !empty($deposit)
                ? __('Appointment created. A deposit request has been sent to the customer.')
                : __('Appointment successfully created.'),
        ]);
    }

    /**
     * Edit-panel save.
     *
     * PUT /bookings-v2/appointments/{id}
     * {
     *   date, start_time, status_id, notes, staff_id,
     *   custom_fields: { label: value },
     *   services: [ { service_id, duration, price } ]
     * }
     *
     * Without a booking-group column an appointment row owns exactly one
     * service, so the first service updates this row and any further services
     * are saved as additional rows. Nothing is deleted here — removing a
     * booking stays with the existing delete flow.
     */
    public function updateAppointment(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('appointment edit')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = \Validator::make($request->all(), [
            'date' => 'required|date_format:d-m-Y',
            'start_time' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'staff_id' => 'required|numeric',
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|numeric',
            'services.*.duration' => 'nullable|numeric|min:1|max:1440',
            'services.*.price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $appointment = Appointment::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->find($id);

        if (empty($appointment)) {
            return response()->json(['error' => __('Appointment not found.')], 404);
        }

        // Distinguish "not supplied" from "cleared": only an absent key leaves
        // the stored values alone, so emptying REGO actually empties it.
        $customFieldsSupplied = $this->customFieldsEnabled() && $request->has('custom_fields');
        $customFields = $this->sanitizeCustomFields($request->input('custom_fields'));
        $customFieldJson = !empty($customFields) ? json_encode($customFields) : null;

        $extra = [];

        try {
            DB::beginTransaction();

            $cursor = $request->input('start_time');
            $isFirst = true;

            foreach ($request->input('services') as $line) {
                $service = Service::where('business_id', $businessId)
                    ->where('created_by', $createdBy)
                    ->find($line['service_id']);

                if (empty($service)) {
                    continue;
                }

                $duration = isset($line['duration']) && $line['duration'] !== null && $line['duration'] !== ''
                    ? (int) $line['duration']
                    : (int) $service->duration;

                $price = isset($line['price']) && $line['price'] !== null && $line['price'] !== ''
                    ? (float) $line['price']
                    : (float) $service->price;

                $end = $this->addMinutes($cursor, $duration);

                // The first service keeps this row's identity so the chip the
                // user clicked stays the same appointment.
                $target = $isFirst ? $appointment : new Appointment();

                if (!$isFirst) {
                    $target->customer_id = $appointment->customer_id;
                    $target->location_id = $appointment->location_id;
                    $target->business_id = $businessId;
                    $target->created_by = $createdBy;
                    $target->payment_type = $appointment->payment_type ?: 'Manually';
                }

                $target->service_id = $service->id;
                $target->staff_id = $request->input('staff_id');
                $target->date = $request->input('date');
                $target->time = $cursor . '-' . $end;
                $target->notes = $request->input('notes', '');

                if ($customFieldsSupplied) {
                    $target->custom_field = $customFieldJson;
                }

                if ($request->filled('status_id')) {
                    $target->appointment_status = $request->input('status_id');
                }

                $target->save();

                // Price rides on the payment row, so an override has to be
                // written there for both the kept row and any new ones.
                $payment = AppointmentPayment::firstOrNew(['appointment_id' => $target->id]);
                $payment->payment_type = $target->payment_type ?: 'Manually';
                $payment->amount = $price;
                $payment->business_id = $businessId;
                $payment->created_by = $createdBy;

                if (empty($payment->payment_date)) {
                    $payment->payment_date = now();
                }

                $payment->save();

                if (!$isFirst) {
                    $extra[] = $target;
                }

                $cursor = $end;
                $isFirst = false;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => __('Could not save the changes.')], 500);
        }

        foreach ($extra as $row) {
            $this->dispatchCreated($row, $request);
        }

        return response()->json([
            'success' => true,
            'id' => $appointment->id,
            'created' => count($extra),
            'message' => __('Appointment successfully updated.'),
        ]);
    }

    /* --------------------------------------------------------------------- */
    /* Internals                                                             */
    /* --------------------------------------------------------------------- */

    /**
     * Mirror AppointmentController@store's post-save side effects so bookings
     * made from the calendar notify exactly like bookings made from the form.
     *
     * Failures here are swallowed: a mail or listener problem must not make a
     * saved appointment look like it failed.
     */
    protected function dispatchCreated(Appointment $appointment, Request $request): void
    {
        try {
            $settings = getCompanyAllSetting();
            $defaults = resolveDefaultAppointmentStatus('calendar', $appointment->business_id, $appointment->created_by);
            $template = $defaults['template_name'];

            // No fallback template — an unmapped channel sends nothing.
            if (!empty($template) && !empty($settings[$template]) && $settings[$template] == true) {
                $customer = $appointment->CustomerData;
                $email = $customer && $customer->customer ? $customer->customer->email : $appointment->email;

                if (!empty($email)) {
                    $business = $appointment->business;

                    EmailTemplate::sendEmailTemplate($template, [$email], [
                        'company_name' => $business->name ?? '',
                        'service' => $appointment->ServiceData->name ?? '-',
                        'location' => $appointment->LocationData->name ?? '-',
                        'staff' => $appointment->StaffData->name ?? '-',
                        'appointment_date' => $appointment->date,
                        'appointment_time' => $appointment->time,
                        'appointment_number' => Appointment::appointmentNumberFormat(
                            $appointment->id,
                            $appointment->created_by,
                            $appointment->business_id
                        ),
                        'client_name' => $customer->name ?? $appointment->name,
                        'tracking_url' => !empty($business->slug)
                            ? route('find.appointment', ['businessSlug' => $business->slug])
                            : '',
                    ]);
                }
            }

            event(new CreateAppoinment($appointment, $request));
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * Run the automatic deposit rules against a freshly created booking.
     *
     * Failures are swallowed for the same reason dispatchCreated's are: a
     * booking that saved must not report as failed because the rules engine
     * could not raise a deposit — for instance when no gateway is configured.
     */
    protected function evaluateDeposit(Appointment $appointment): ?array
    {
        try {
            return app(\App\Services\DepositService::class)->evaluateAndRequest($appointment);
        } catch (\Exception $e) {
            report($e);

            return null;
        }
    }

    /**
     * Whether this business captures custom fields on appointments — the same
     * gate AppointmentController@store uses.
     */
    protected function customFieldsEnabled(): bool
    {
        return company_setting('custom_field_enable', Auth::user()->id, getActiveBusiness()) === 'on';
    }

    /**
     * The business's custom-field definitions. On a car-service tenant these are
     * rows like "REGO" and "Make & Model"; the values land in
     * `appointments.custom_field` as a label => value JSON map.
     *
     * @return array<int,array{label:string,type:string,value:string|null}>
     */
    protected function customFieldDefinitions(): array
    {
        if (!$this->customFieldsEnabled()) {
            return [];
        }

        return CustomField::where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->where('show_in_appointment', 1)
            ->orderBy('id')
            ->get()
            ->map(function ($field) {
                return [
                    'label' => $field->label,
                    'type' => $field->type,
                    'value' => $field->value,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Decode an appointment's stored custom-field map.
     *
     * @return array<string,string>
     */
    protected function readCustomFields(Appointment $appointment): array
    {
        $decoded = json_decode($appointment->custom_field ?? '', true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Keep only values whose label matches a defined field, so the stored JSON
     * cannot be stuffed with arbitrary keys from the client.
     *
     * @return array<string,string>
     */
    protected function sanitizeCustomFields($submitted): array
    {
        if (!is_array($submitted) || !$this->customFieldsEnabled()) {
            return [];
        }

        $allowed = array_column($this->customFieldDefinitions(), 'label');
        $clean = [];

        foreach ($submitted as $label => $value) {
            if (!in_array($label, $allowed, true)) {
                continue;
            }

            $clean[$label] = is_array($value) ? implode(',', $value) : (string) $value;
        }

        return $clean;
    }

    /**
     * Minutes covered by a stored "HH:MM-HH:MM" range — the booked duration,
     * which may differ from the service catalogue when it was overridden.
     */
    protected function durationFromRange(?string $range): int
    {
        $times = $this->splitTimeRange($range);
        $start = explode(':', $times['start']);
        $end = explode(':', $times['end']);

        $minutes = (((int) $end[0] * 60) + (int) $end[1]) - (((int) $start[0] * 60) + (int) $start[1]);

        return max(0, $minutes);
    }

    /**
     * Add minutes to an "HH:MM" clock time, clamped to the end of the day so a
     * long service cannot wrap a booking onto the next date.
     */
    protected function addMinutes(string $time, int $minutes): string
    {
        $parts = explode(':', $time);
        $total = ((int) $parts[0] * 60) + (int) ($parts[1] ?? 0) + max(0, $minutes);
        $total = min($total, (24 * 60) - 1);

        return sprintf('%02d:%02d', intdiv($total, 60), $total % 60);
    }

    /**
     * Which notification channels this customer can actually be reached on.
     * Stands in for the notification-preference column this schema lacks.
     */
    protected function notificationChannels(?string $mobile, ?string $email): string
    {
        $hasMobile = !empty($mobile);
        $hasEmail = !empty($email);

        if ($hasMobile && $hasEmail) {
            return 'both';
        }
        if ($hasMobile) {
            return 'sms';
        }
        if ($hasEmail) {
            return 'email';
        }

        return '';
    }

    /**
     * Deposit state for the detail panel.
     *
     * Carries the two gates as booleans so the panel never has to re-derive
     * them: `can_request` needs both a payment gateway and an unpaid bill,
     * while `can_take_payment` needs only the unpaid bill — a counter payment
     * is exactly what a salon without a gateway relies on.
     */
    protected function depositState(Appointment $appointment): array
    {
        $deposits = app(\App\Services\DepositService::class);
        $canRequest = $deposits->canRequestDeposit($appointment);
        $lock = $deposits->actionability($appointment);

        return [
            'status' => $appointment->deposit_status ?: 'none',
            'required' => (bool) $appointment->deposit_required,
            'amount' => $appointment->deposit_amount !== null ? (float) $appointment->deposit_amount : null,
            'link' => $deposits->linkFor($appointment),
            'method' => $appointment->deposit_method,
            'invoice_status' => $deposits->invoiceStatus($appointment),
            'can_request' => $canRequest['allowed'],
            'can_request_reason' => $canRequest['reason'],
            'can_take_payment' => $lock['allowed'],
            'lock_reason' => $lock['reason'],
            'request_url' => route('deposit.create', $appointment->id),
            'payment_url' => route('deposit.payment-form', $appointment->id),
        ];
    }

    /**
     * Invoice badge and checkout links, only when the Invoice module is active.
     *
     * Two independent invoices can exist for one appointment: the balance
     * invoice raised at checkout (`converted_invoice`), and the accounting
     * mirror of an on-the-spot deposit (`DepositInvoice::synced_invoice_id`,
     * written by DepositService::syncDepositInvoiceToAccounting()). Neither
     * implies the other, so both are resolved and returned separately.
     */
    protected function invoiceLinks(Appointment $appointment): array
    {
        $blank = [
            'invoice_id' => null,
            'invoice_url' => null,
            'checkout_url' => null,
            'deposit_invoice_id' => null,
            'deposit_invoice_url' => null,
        ];

        if (!module_is_active('Invoice')) {
            return $blank;
        }

        $invoiceId = $appointment->converted_invoice ?: null;

        $depositInvoiceId = null;

        if (!empty($appointment->deposit_invoice_id)) {
            $depositInvoice = \App\Models\DepositInvoice::find($appointment->deposit_invoice_id);
            $depositInvoiceId = $depositInvoice->synced_invoice_id ?? null;
        }

        return [
            'invoice_id' => $invoiceId,
            'invoice_url' => ($invoiceId && \Route::has('invoice.show'))
                ? route('invoice.show', \Crypt::encrypt($invoiceId))
                : null,
            'checkout_url' => (!$invoiceId && \Route::has('invoice.convert.appointment'))
                ? route('invoice.convert.appointment', $appointment->id)
                : null,
            'deposit_invoice_id' => $depositInvoiceId,
            'deposit_invoice_url' => ($depositInvoiceId && \Route::has('invoice.show'))
                ? route('invoice.show', \Crypt::encrypt($depositInvoiceId))
                : null,
        ];
    }

    /**
     * WhatsApp Chat tab gating, computed server-side so the client never has
     * to re-derive FR7/FR8's rules itself.
     *
     * `visible` covers FR8 (disabled or unconfigured or no permission ->
     * the tab doesn't appear at all); a visible tab with `has_valid_mobile`
     * false is FR7's warning state, shown instead of the chat.
     */
    protected function whatsappState(Appointment $appointment, ?string $mobile): array
    {
        $whatsapp = app(\App\Services\WhatsAppService::class);
        $businessId = $appointment->business_id;

        $enabled = $whatsapp->isEnabled($businessId);
        $canUse = $enabled && Auth::user()->isAbleTo('use_whatsapp_chat');

        return [
            'visible' => $canUse,
            'configured' => $whatsapp->isConfigured($businessId),
            'has_valid_mobile' => Customer::isValidAustralianMobile($mobile),
            'send_url' => route('bookings-v2.whatsapp.send', $appointment->id),
            'messages_url' => route('bookings-v2.whatsapp.messages', $appointment->id),
        ];
    }

    /**
     * Statuses as an id => title map, the shape the detail panel's <select> uses.
     */
    protected function statusMap(): array
    {
        $map = [];

        foreach ($this->statusList() as $status) {
            $map[$status['id']] = $status['title'];
        }

        return $map;
    }

    /**
     * @return array<int,array{id:int,title:string,color:string}>
     */
    protected function statusList(): array
    {
        return CustomStatus::where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->orderBy('id')
            ->get()
            ->map(function ($status) {
                return [
                    'id' => $status->id,
                    'title' => $status->title,
                    'color' => $this->statusColor($status),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Previous and upcoming appointments for the same customer, rendered as
     * status pills in the detail panel.
     */
    protected function customerHistory(Appointment $appointment): array
    {
        if (empty($appointment->customer_id)) {
            return ['previous' => [], 'upcoming' => []];
        }

        return $this->historyFor(
            $appointment->customer_id,
            $this->parseAppointmentDate($appointment->date),
            $appointment->id
        );
    }

    /**
     * Split a customer's appointments into past and future around an anchor date.
     *
     * @param  int|null  $excludeId  appointment to leave out (the one on screen)
     */
    protected function historyFor($customerId, ?Carbon $anchor, $excludeId = null): array
    {
        if (empty($customerId) || $anchor === null) {
            return ['previous' => [], 'upcoming' => []];
        }

        $siblings = Appointment::with(['StatusData', 'ServiceData', 'StaffData'])
            ->where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->where('customer_id', $customerId)
            ->when(!empty($excludeId), function ($query) use ($excludeId) {
                $query->where('id', '!=', $excludeId);
            })
            ->get();

        $previous = [];
        $upcoming = [];

        foreach ($siblings as $sibling) {
            $when = $this->parseAppointmentDate($sibling->date);

            if ($when === null) {
                continue;
            }

            $row = [
                'id' => $sibling->id,
                'date' => $sibling->date,
                'time' => $sibling->time,
                'service' => $sibling->ServiceData->name ?? '-',
                'staff' => $sibling->StaffData->name ?? '-',
                'status' => $sibling->StatusData->title ?? __('Pending'),
                'color' => $this->statusColor($sibling->StatusData),
                'sort' => $when->format('Y-m-d'),
            ];

            if ($when->lt($anchor)) {
                $previous[] = $row;
            } else {
                $upcoming[] = $row;
            }
        }

        usort($previous, function ($a, $b) {
            return strcmp($b['sort'], $a['sort']); // most recent first
        });
        usort($upcoming, function ($a, $b) {
            return strcmp($a['sort'], $b['sort']); // soonest first
        });

        return [
            'previous' => array_slice($previous, 0, 5),
            'upcoming' => array_slice($upcoming, 0, 5),
        ];
    }

    /**
     * Restrict a query to the requested day, or to a start/end range.
     *
     * `appointments.date` is a d-m-Y string rather than a DATE column, so a
     * single day is matched by formatting and a range needs STR_TO_DATE.
     */
    protected function applyDateFilter($query, Request $request): void
    {
        if ($request->filled('start') && $request->filled('end')) {
            $start = $this->parseRequestDate($request->input('start'));
            $end = $this->parseRequestDate($request->input('end'));

            if ($start && $end) {
                $query->whereRaw(
                    "STR_TO_DATE(`date`, '%d-%m-%Y') BETWEEN ? AND ?",
                    [$start->format('Y-m-d'), $end->format('Y-m-d')]
                );
            }

            return;
        }

        $date = $this->parseRequestDate($request->input('date')) ?? Carbon::today();
        $query->where('date', $date->format('d-m-Y'));
    }

    /**
     * Staff user ids holding an appointment on the given date.
     *
     * @return string[]
     */
    protected function bookedStaffIds(Carbon $date, $locationId = null): array
    {
        $query = Appointment::where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->where('date', $date->format('d-m-Y'));

        if (!empty($locationId)) {
            $query->where('location_id', $locationId);
        }

        return $query->pluck('staff_id')
            ->map(function ($id) {
                return (string) $id;
            })
            ->unique()
            ->values()
            ->all();
    }

    /**
     * businessHours windows for the weekday of $date: the working shift with
     * every break subtracted. The gaps render as FullCalendar's .fc-non-business
     * shading. Returns [] when the business is closed that day.
     */
    protected function businessHoursFor(Carbon $date, $businessId, $createdBy): array
    {
        $row = BusinessHours::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->where('day_name', $date->format('l'))
            ->first();

        if (empty($row) || $row->day_off == 'on' || empty($row->start_time) || empty($row->end_time)) {
            return [];
        }

        $shiftStart = date('H:i', strtotime($row->start_time));
        $shiftEnd = date('H:i', strtotime($row->end_time));

        $breaks = json_decode($row->break_hours ?? '', true);
        $breaks = is_array($breaks) ? $breaks : [];

        return $this->splitShift($shiftStart, $shiftEnd, $breaks);
    }

    /**
     * Subtract breaks from a shift, yielding the working windows between them.
     *
     * @param  array<int,array{start:string,end:string}>  $breaks
     */
    protected function splitShift(string $shiftStart, string $shiftEnd, array $breaks): array
    {
        $clean = [];

        foreach ($breaks as $break) {
            if (empty($break['start']) || empty($break['end'])) {
                continue;
            }

            // Clamp to the shift so a stray break cannot extend the working day.
            $start = max($shiftStart, date('H:i', strtotime($break['start'])));
            $end = min($shiftEnd, date('H:i', strtotime($break['end'])));

            if ($start < $end) {
                $clean[] = ['start' => $start, 'end' => $end];
            }
        }

        usort($clean, function ($a, $b) {
            return strcmp($a['start'], $b['start']);
        });

        $windows = [];
        $current = $shiftStart;

        foreach ($clean as $break) {
            if ($current < $break['start']) {
                $windows[] = [
                    'startTime' => $current,
                    'endTime' => $break['start'],
                    'daysOfWeek' => self::ALL_DAYS,
                ];
            }

            // max() rather than a bare assignment so overlapping breaks cannot
            // rewind the cursor and re-open a window already covered.
            $current = max($current, $break['end']);
        }

        if ($current < $shiftEnd) {
            $windows[] = [
                'startTime' => $current,
                'endTime' => $shiftEnd,
                'daysOfWeek' => self::ALL_DAYS,
            ];
        }

        return $windows;
    }

    /**
     * Naive local start/end datetimes for an appointment, or null when the
     * stored date cannot be parsed.
     *
     * Deliberately timezone-free: the client runs FullCalendar without a
     * `timeZone` option, so these render at face value with no conversion.
     *
     * @return array{start:string,end:string}|null
     */
    protected function eventWindow(Appointment $appointment): ?array
    {
        $date = $this->parseAppointmentDate($appointment->date);

        if ($date === null) {
            return null;
        }

        $ymd = $date->format('Y-m-d');
        $times = $this->splitTimeRange($appointment->time);

        return [
            'start' => $ymd . 'T' . $times['start'] . ':00',
            'end' => $ymd . 'T' . $times['end'] . ':00',
        ];
    }

    /**
     * Split a stored "HH:MM-HH:MM" range into its two ends.
     *
     * @return array{start:string,end:string}
     */
    protected function splitTimeRange(?string $range): array
    {
        $parts = array_map('trim', explode('-', (string) $range, 2));

        $start = !empty($parts[0]) ? $parts[0] : '00:00';
        $end = !empty($parts[1]) ? $parts[1] : null;

        $start = date('H:i', strtotime($start) ?: strtotime('00:00'));

        // A missing or inverted end would collapse the chip to zero height.
        if ($end === null) {
            $end = date('H:i', strtotime($start) + 900);
        } else {
            $end = date('H:i', strtotime($end) ?: strtotime($start) + 900);

            if ($end <= $start) {
                $end = date('H:i', strtotime($start) + 900);
            }
        }

        // The +15m fallback wraps past midnight for a late-evening start, which
        // would still leave end <= start. Clamp to the end of the day instead:
        // this storage format holds no date, so a chip cannot span two days.
        if ($end <= $start) {
            $end = '23:59';
        }

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Parse `appointments.date`, which is stored as a d-m-Y string.
     */
    protected function parseAppointmentDate(?string $date): ?Carbon
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d-m-Y', $date)->startOfDay();
        } catch (\Exception $e) {
            // Fall through: a few rows may predate the d-m-Y convention.
        }

        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse an ISO date sent by the client (FullCalendar's info.startStr).
     */
    protected function parseRequestDate($date): ?Carbon
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

    /**
     * Whether a status is configured to stay off the calendar.
     *
     * This schema has no `show_on_appointment_calendar` column, so the gate is
     * currently inert and everything renders. The column check is cached per
     * request so adding the column later switches the gate on with no code
     * change — and so cancelled appointments would then disappear rather than
     * render struck-through.
     */
    protected function isStatusHidden($status): bool
    {
        if (empty($status)) {
            return false; // no status row: show it rather than lose the booking
        }

        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn('custom_statuses', 'show_on_appointment_calendar');
        }

        if (!$hasColumn) {
            return false;
        }

        return (int) ($status->show_on_appointment_calendar ?? 1) === 0;
    }

    /**
     * Chip colour for a status. `status_color` is stored without a leading '#'.
     */
    protected function statusColor($status): string
    {
        $color = $status->status_color ?? null;

        if (empty($color)) {
            return self::FALLBACK_COLOR;
        }

        return '#' . ltrim($color, '#');
    }

    /**
     * Slot granularity in minutes: company setting first, config fallback,
     * clamped to a minimum of 5 so the grid stays renderable.
     */
    protected function slotInterval(): int
    {
        $interval = company_setting('calendar_slot_interval', Auth::user()->id, getActiveBusiness());

        if (!is_numeric($interval)) {
            $interval = config('fullcalendar.slot_interval', 15);
        }

        return max(5, (int) $interval);
    }

    /**
     * How far past a staff member's actual working hours the calendar grid
     * extends, in minutes — was hardcoded (30 before, 60 after) in the client;
     * now a per-business Setting, defaulting to those same numbers so nobody's
     * grid changes shape until they explicitly configure it.
     */
    protected function calendarBufferBefore(): int
    {
        $minutes = company_setting('calendar_buffer_before', Auth::user()->id, getActiveBusiness());

        return is_numeric($minutes) ? max(0, (int) $minutes) : 30;
    }

    protected function calendarBufferAfter(): int
    {
        $minutes = company_setting('calendar_buffer_after', Auth::user()->id, getActiveBusiness());

        return is_numeric($minutes) ? max(0, (int) $minutes) : 60;
    }
}
