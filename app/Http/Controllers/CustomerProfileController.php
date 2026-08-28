<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentDepositLog;
use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\DepositInvoice;
use App\Models\JobCard;
use App\Models\SmsLog;
use App\Services\AppointmentStatusClassifier;
use App\Services\CustomerReliabilityService;
use App\Services\DepositService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * The customer profile: one page of tabbed panels, each answering one question
 * about the customer.
 *
 * Every panel is loaded through tab(), which catches its own failures and
 * degrades to an empty panel. That is the load-bearing decision on this screen:
 * a customer profile is the page staff open when something has *already* gone
 * wrong, so it has to render. One broken relationship must not take the page
 * down and leave the front desk with nothing.
 */
class CustomerProfileController extends Controller
{
    protected CustomerReliabilityService $reliability;
    protected DepositService $deposits;
    protected AppointmentStatusClassifier $classifier;

    public function __construct(
        CustomerReliabilityService $reliability,
        DepositService $deposits,
        AppointmentStatusClassifier $classifier
    ) {
        $this->reliability = $reliability;
        $this->deposits = $deposits;
        $this->classifier = $classifier;
    }

    /**
     * GET /customer/{customer}/profile
     */
    public function show($id)
    {
        if (!Auth::user()->isAbleTo('customer manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $customer = Customer::with('customer')
            ->where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->find($id);

        if (empty($customer)) {
            return redirect()->route('customer.index')->with('error', __('Customer not found.'));
        }

        $panels = [
            'summary' => $this->tab('summary', fn() => $this->summaryPanel($customer)),
            'appointments' => $this->tab('appointments', fn() => $this->appointmentsPanel($customer)),
            'job_cards' => $this->tab('job_cards', fn() => $this->jobCardsPanel($customer)),
            'invoices' => $this->tab('invoices', fn() => $this->invoicesPanel($customer)),
            'wallet' => $this->tab('wallet', fn() => $this->walletPanel($customer)),
            'deposits' => $this->tab('deposits', fn() => $this->depositsPanel($customer)),
            'notes' => $this->tab('notes', fn() => $this->notesPanel($customer)),
            'loyalty' => $this->tab('loyalty', fn() => $this->loyaltyPanel($customer)),
            'sms' => $this->tab('sms', fn() => $this->smsPanel($customer)),
        ];

        $currency = company_setting('defult_currancy_symbol', null, $customer->business_id) ?: '$';

        return view('customer.profile', compact('customer', 'panels', 'currency'));
    }

    /**
     * Run one panel's data load in isolation.
     *
     * A panel that throws returns `failed` and renders as a short apology in its
     * own tab, leaving every other tab intact.
     */
    protected function tab(string $name, callable $loader): array
    {
        try {
            return ['failed' => false, 'data' => $loader()];
        } catch (\Throwable $e) {
            // Reported, not swallowed: a permanently empty tab that nobody knew
            // was broken is how these bugs survive for years.
            Log::error("Customer profile panel '{$name}' failed: " . $e->getMessage());

            return ['failed' => true, 'data' => []];
        }
    }

    /* --------------------------------------------------------------------- */
    /* Panels                                                                */
    /* --------------------------------------------------------------------- */

    /**
     * Contact details, lifetime totals, and the reliability signal.
     */
    protected function summaryPanel(Customer $customer): array
    {
        $businessId = $customer->business_id;
        $createdBy = $customer->created_by;

        // appointments.customer_id is a *user* id.
        $appointments = Appointment::query()
            ->leftJoin('custom_statuses', 'custom_statuses.id', '=', 'appointments.appointment_status')
            ->where('appointments.business_id', $businessId)
            ->where('appointments.created_by', $createdBy)
            ->where('appointments.customer_id', $customer->user_id)
            ->get([
                'appointments.id',
                'appointments.appointment_status',
                'custom_statuses.title as status_title',
            ]);

        $slugMap = $this->classifier->slugMap($businessId, $createdBy);

        $counts = ['completed' => 0, 'cancelled' => 0, 'no_shows' => 0];

        foreach ($appointments as $appointment) {
            $class = $this->classifier->classify($appointment->status_title, $appointment->appointment_status, $slugMap);

            if ($class === AppointmentStatusClassifier::NO_SHOW) {
                $counts['no_shows']++;
            } elseif ($class === AppointmentStatusClassifier::CANCELLED) {
                $counts['cancelled']++;
            } elseif (stripos((string) $appointment->status_title, 'complete') !== false) {
                $counts['completed']++;
            }
        }

        // Invoices key on users.id here, matching how deposit invoices are written.
        $invoices = DepositInvoice::where('business_id', $businessId)
            ->where('customer_id', $customer->user_id)
            ->get();

        return [
            'reliability' => $this->reliability->forCustomer($customer->user_id, $businessId, $createdBy),
            'total_appointments' => $appointments->count(),
            'completed' => $counts['completed'],
            'cancelled' => $counts['cancelled'],
            'no_shows' => $counts['no_shows'],
            'lifetime_sales' => round((float) $invoices->sum('paid_total'), 2),
            'outstanding' => round((float) $invoices->sum(fn($invoice) => $invoice->outstanding()), 2),
        ];
    }

    /**
     * Full booking history with status.
     */
    protected function appointmentsPanel(Customer $customer): array
    {
        return Appointment::with(['ServiceData', 'StaffData', 'StatusData', 'LocationData'])
            ->where('business_id', $customer->business_id)
            ->where('created_by', $customer->created_by)
            ->where('customer_id', $customer->user_id)
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->all();
    }

    /**
     * Every scanned job card across this customer's appointments.
     */
    protected function jobCardsPanel(Customer $customer): array
    {
        return JobCard::with(['files', 'appointment'])
            ->where('business_id', $customer->business_id)
            ->where('created_by', $customer->created_by)
            ->where('customer_id', $customer->user_id)
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->all();
    }

    /**
     * Every invoice raised for this customer, deposits included.
     */
    protected function invoicesPanel(Customer $customer): array
    {
        return DepositInvoice::where('business_id', $customer->business_id)
            ->where('customer_id', $customer->user_id)
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->all();
    }

    /**
     * Balance plus the full transaction ledger.
     */
    protected function walletPanel(Customer $customer): array
    {
        return [
            // Reconciled from the ledger rather than read from the cache column:
            // the ledger is the truth and this is the page where a wrong balance
            // gets noticed.
            'balance' => $customer->reconcileWallet(),
            'transactions' => $customer->walletTransactions()->limit(200)->get()->all(),
        ];
    }

    /**
     * Outstanding deposits with a resend action, then the full event log.
     */
    protected function depositsPanel(Customer $customer): array
    {
        $businessId = $customer->business_id;
        $createdBy = $customer->created_by;

        $appointments = Appointment::with(['ServiceData', 'depositInvoice'])
            ->where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->where('customer_id', $customer->user_id)
            ->where('deposit_status', '!=', DepositService::NONE)
            ->orderByDesc('id')
            ->get();

        // One row per booking group, not per appointment: a three-service booking
        // carries the same deposit on all three rows and would otherwise be
        // listed three times.
        $bookings = [];

        // Staff names resolved in one query rather than one per row.
        $staffNames = \App\Models\User::whereIn(
            'id',
            $appointments->pluck('deposit_requested_by')->filter()->unique()->all()
        )->pluck('name', 'id')->all();

        foreach ($appointments as $appointment) {
            $key = $appointment->common_number ?: ('id-' . $appointment->id);

            if (isset($bookings[$key])) {
                continue;
            }

            $hasMobile = !empty($customer->customer->mobile_no ?? null);

            $bookings[$key] = [
                'appointment' => $appointment,
                // Null stays null: the view renders it as "Automatic rule",
                // which is what a null deposit_requested_by means.
                'requested_by' => $appointment->deposit_requested_by
                    ? ($staffNames[$appointment->deposit_requested_by] ?? null)
                    : null,
                // Visible only when the deposit is pending, a link exists, and
                // the customer has a mobile number — the case it exists for is a
                // corrected mobile number.
                'can_resend' => $appointment->deposit_status === DepositService::PENDING
                    && !empty($appointment->deposit_invoice_id)
                    && $hasMobile,
            ];
        }

        $logs = AppointmentDepositLog::with('staff')
            ->where('business_id', $businessId)
            ->whereIn('appointment_id', $appointments->pluck('id')->all())
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->all();

        return ['bookings' => array_values($bookings), 'logs' => $logs];
    }

    protected function notesPanel(Customer $customer): array
    {
        return $customer->notes()->with('staff')->limit(200)->get()->all();
    }

    /**
     * Visit counter or points ledger, depending on the configured mode. The two
     * modes are mutually exclusive.
     */
    protected function loyaltyPanel(Customer $customer): array
    {
        $businessId = $customer->business_id;

        if (company_setting('loyalty_program', null, $businessId) !== 'on') {
            return ['enabled' => false, 'mode' => null];
        }

        $mode = company_setting('loyalty_program_type', null, $businessId) ?: 'service';

        if ($mode === 'points') {
            return [
                'enabled' => true,
                'mode' => 'points',
                'points' => (int) $customer->loyalty_points,
                'transactions' => $customer->loyaltyTransactions()->limit(200)->get()->all(),
            ];
        }

        $required = company_setting('loyalty_visits_required', null, $businessId);

        return [
            'enabled' => true,
            'mode' => 'service',
            'required' => is_numeric($required) ? (int) $required : 6,
            'visits' => $customer->serviceVisits()->with('service')->get()->all(),
        ];
    }

    /**
     * Every message to and from the customer's mobile, inbound replies included.
     */
    protected function smsPanel(Customer $customer): array
    {
        $mobile = $customer->customer->mobile_no ?? null;

        return SmsLog::where('business_id', $customer->business_id)
            ->where(function ($query) use ($customer, $mobile) {
                $query->where('user_id', $customer->user_id);

                // Also match on the number itself, so inbound replies logged
                // before the number was linked to a user still appear.
                if (!empty($mobile)) {
                    $query->orWhere('mobile_no', $mobile);
                }
            })
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->all();
    }

    /* --------------------------------------------------------------------- */
    /* Writes                                                                */
    /* --------------------------------------------------------------------- */

    /**
     * Add a staff note.
     */
    public function storeNote(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('customer edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $customer = $this->findCustomer($id);

        if (empty($customer)) {
            return redirect()->back()->with('error', __('Customer not found.'));
        }

        $validator = \Validator::make($request->all(), ['note' => 'required|string|max:2000']);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        CustomerNote::create([
            'customer_id' => $customer->id,
            'note' => $request->input('note'),
            'staff_id' => Auth::user()->id,
            'business_id' => $customer->business_id,
            'created_by' => $customer->created_by,
        ]);

        return redirect()->back()->with('success', __('Note added.'));
    }

    /**
     * Toggle the manual high-risk flag.
     *
     * This is rule 1 of the deposit engine and always wins, so it is an
     * explicit, audited staff decision rather than a side effect of anything.
     */
    public function toggleRisk(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('customer edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $customer = $this->findCustomer($id);

        if (empty($customer)) {
            return redirect()->back()->with('error', __('Customer not found.'));
        }

        $customer->is_high_risk = !$customer->is_high_risk;
        $customer->save();

        return redirect()->back()->with('success', $customer->is_high_risk
            ? __('Customer flagged as high risk. New bookings will require a deposit.')
            : __('High-risk flag removed.'));
    }

    /**
     * Set the Communication Group toggles (SMS/email opt-in).
     *
     * Each toggle is validated server-side against the customer's actual
     * mobile/email regardless of what the client sent — see
     * Customer::setCommunicationPreferences().
     */
    public function updateCommunication(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('customer edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $customer = $this->findCustomer($id);

        if (empty($customer)) {
            return redirect()->back()->with('error', __('Customer not found.'));
        }

        $result = $customer->setCommunicationPreferences(
            $request->boolean('communication_sms'),
            $request->boolean('communication_email')
        );

        $downgraded = ($request->boolean('communication_sms') && !$result['sms'])
            || ($request->boolean('communication_email') && !$result['email']);

        return redirect()->back()->with(
            $downgraded ? 'error' : 'success',
            $downgraded
                ? __('Saved, but a toggle was left off because the matching contact detail on file is invalid.')
                : __('Communication preferences updated.')
        );
    }

    protected function findCustomer($id): ?Customer
    {
        return Customer::with('customer')
            ->where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->find($id);
    }
}
