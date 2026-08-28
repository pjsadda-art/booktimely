<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentDepositLog;
use App\Models\Customer;
use App\Models\CustomStatus;
use App\Models\DepositInvoice;
use App\Models\DepositInvoiceItem;
use App\Models\DepositInvoicePayment;
use App\Models\Service;
use App\Models\SmsLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Everything a deposit can have done to it.
 *
 * A deposit belongs to a *booking group*, not to a single appointment row: it
 * is raised once for the whole `common_number` and written to every row in it.
 * Every method here therefore resolves the group first and writes to all of it.
 *
 * The automatic and the manual path share requestDeposit(), which is what keeps
 * them consistent — the only difference between them is who is recorded as
 * having asked, and that null is meaningful because it selects the message
 * template later.
 */
class DepositService
{
    public const NONE = 'none';
    public const PENDING = 'pending';
    public const PAID = 'paid';
    public const FORFEITED = 'forfeited';
    public const REFUNDED = 'refunded';

    protected OnlinePaymentGate $gate;
    protected CustomerNotifier $notifier;
    protected DepositRulesEngine $rules;

    public function __construct(
        ?OnlinePaymentGate $gate = null,
        ?CustomerNotifier $notifier = null,
        ?DepositRulesEngine $rules = null
    ) {
        $this->gate = $gate ?: new OnlinePaymentGate();
        $this->notifier = $notifier ?: new CustomerNotifier();
        $this->rules = $rules ?: new DepositRulesEngine();
    }

    /* --------------------------------------------------------------------- */
    /* Raising                                                               */
    /* --------------------------------------------------------------------- */

    /**
     * Evaluate the automatic rules for a new booking and raise a deposit if a
     * rule matched. Safe to call for every created appointment.
     *
     * Called once per booking group, not once per row: three services in one
     * booking is one deposit.
     */
    public function evaluateAndRequest(Appointment $appointment): ?array
    {
        // Already carrying a deposit — an earlier row of the same group raised it.
        if ($appointment->deposit_status && $appointment->deposit_status !== self::NONE) {
            return null;
        }

        $verdict = $this->rules->evaluate($appointment);

        if (empty($verdict['required'])) {
            return null;
        }

        try {
            return $this->requestDeposit($appointment, [
                'percentage' => $verdict['percentage'],
                'requested_by' => null,   // null means the rule engine raised it
                'message' => $verdict['reason'],
            ]);
        } catch (\Exception $e) {
            // A booking must still save when the deposit could not be raised —
            // for instance when no gateway is configured. Report, do not throw.
            Log::warning('Automatic deposit not raised: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Raise a deposit against a booking group. The one routine both the
     * automatic and the manual path go through.
     *
     * @param  array{percentage?:float|null,amount?:float|null,requested_by?:int|null,message?:string|null}  $options
     * @return array{amount:float,invoice:DepositInvoice,pay_url:string,notified:array}
     *
     * @throws \RuntimeException when the request cannot be raised
     */
    public function requestDeposit(Appointment $appointment, array $options = []): array
    {
        $businessId = $appointment->business_id;
        $createdBy = $appointment->created_by;

        // The gate that matters most. A deposit request is nothing but a payment
        // link, so refuse to create one that cannot be paid.
        if (!$this->gate->isOnlinePaymentReady($businessId, $createdBy)) {
            throw new \RuntimeException($this->gate->unavailableReason());
        }

        if ($this->mainInvoicePaid($appointment)) {
            throw new \RuntimeException(__('This booking has already been paid in full.'));
        }

        // 1. Load every appointment sharing the common_number and sum their
        //    service prices. The group is the unit of money.
        $group = $this->groupOf($appointment);
        $total = $this->groupTotal($group);

        if ($total <= 0) {
            throw new \RuntimeException(__('This booking has no priced services, so there is nothing to take a deposit against.'));
        }

        // 2. Resolve the amount — a percentage of that total, or a fixed figure.
        $percentage = isset($options['percentage']) && is_numeric($options['percentage'])
            ? (float) $options['percentage']
            : null;
        $fixed = isset($options['amount']) && is_numeric($options['amount'])
            ? (float) $options['amount']
            : null;

        if ($fixed !== null && $fixed > 0) {
            $amount = round($fixed, 2);
            $percentage = null;   // null when a fixed amount was used instead
        } elseif ($percentage !== null && $percentage > 0) {
            $amount = round($total * ($percentage / 100), 2);
        } else {
            throw new \RuntimeException(__('Provide either a deposit percentage or a fixed amount.'));
        }

        if ($amount <= 0) {
            throw new \RuntimeException(__('The resolved deposit amount is zero.'));
        }

        $amount = min($amount, round($total, 2));

        // 3. Resolve and remember the post-payment status now, so the payment
        //    path never has to re-infer it, and look up Deposit Pending.
        //    The appointment is passed because an already-confirmed booking is
        //    being reconfirmed, and must land on Reconfirmed rather than
        //    Confirmed once paid. Once it moves to Deposit Pending below, that
        //    distinction is no longer recoverable.
        $confirmStatus = $this->confirmStatus($businessId, $createdBy, $appointment);
        $pendingStatus = CustomStatus::findBySlug('deposit-pending', $businessId, $createdBy);

        $requestedBy = $options['requested_by'] ?? null;
        $isAuto = $requestedBy === null;
        $earliest = Appointment::earliestOf($group) ?: $appointment;
        $oldStatusId = $appointment->appointment_status;

        $invoice = null;

        DB::transaction(function () use (
            $group,
            $appointment,
            $amount,
            $percentage,
            $confirmStatus,
            $pendingStatus,
            $requestedBy,
            $isAuto,
            $options,
            $businessId,
            $createdBy,
            $oldStatusId,
            &$invoice
        ) {
            // 4. Create the deposit invoice and its single line item up front.
            //    Creating it eagerly means the customer-facing checkout page
            //    only ever has to look an invoice up, never create one.
            $invoice = $this->createDepositInvoice($appointment, $amount, $businessId, $createdBy);

            // 5. Update the whole group and move it to Deposit Pending.
            $fields = [
                'deposit_required' => 1,
                'deposit_percentage' => $percentage,
                'deposit_amount' => $amount,
                'deposit_status' => self::PENDING,
                'deposit_invoice_id' => $invoice->id,
                'deposit_confirm_status_id' => $confirmStatus->id ?? null,
                'deposit_requested_by' => $requestedBy,
                'deposit_message' => $options['message'] ?? null,
                // A re-raise must not carry the previous attempt's outcome.
                'deposit_method' => null,
                'deposit_payment_notes' => null,
                'deposit_paid_at' => null,
                'deposit_forfeit_reason' => null,
                'deposit_refund_reference' => null,
            ];

            if (!empty($pendingStatus)) {
                $fields['appointment_status'] = $pendingStatus->id;
            }

            $appointment->groupQuery()->update($fields);

            // 6. Write an audit row.
            AppointmentDepositLog::create([
                'appointment_id' => $appointment->id,
                'common_number' => $appointment->common_number,
                'event_type' => $isAuto
                    ? AppointmentDepositLog::REQUESTED_AUTO
                    : AppointmentDepositLog::REQUESTED_MANUAL,
                'amount' => $amount,
                'old_status_id' => $oldStatusId,
                'new_status_id' => $pendingStatus->id ?? null,
                'staff_id' => $requestedBy,
                'reason' => $options['message'] ?? null,
                'business_id' => $businessId,
                'created_by' => $createdBy,
            ]);
        });

        // 7. Build the payment link and notify the customer. Outside the
        //    transaction: a mail server timeout must not roll back a raised
        //    deposit.
        $notified = $this->notifyRequest($appointment, $invoice, $amount, $isAuto, $earliest);

        return [
            'amount' => $amount,
            'invoice' => $invoice,
            'pay_url' => $invoice->payUrl(),
            'notified' => $notified,
        ];
    }

    /* --------------------------------------------------------------------- */
    /* Getting paid                                                          */
    /* --------------------------------------------------------------------- */

    /**
     * Mark a deposit paid. Called by the gateway callback once the invoice is
     * fully paid, and by the on-the-spot path once it has settled the invoice.
     *
     * Idempotent by contract: gateways retry, and a second call must be a no-op
     * rather than a second status change and a second confirmation message.
     *
     * @return bool true when this call is the one that marked it paid
     */
    public function markPaid(Appointment $appointment, ?string $method = null, ?string $notes = null): bool
    {
        // Already paid — return early rather than re-running the side effects.
        if ($appointment->deposit_status === self::PAID) {
            return false;
        }

        $businessId = $appointment->business_id;
        $createdBy = $appointment->created_by;

        $confirmStatusId = $appointment->deposit_confirm_status_id
            ?: optional($this->confirmStatus($businessId, $createdBy))->id;

        $oldStatusId = $appointment->appointment_status;

        DB::transaction(function () use ($appointment, $method, $notes, $confirmStatusId, $oldStatusId, $businessId, $createdBy) {
            $fields = [
                'deposit_status' => self::PAID,
                'deposit_paid_at' => now(),
            ];

            if (!empty($method)) {
                $fields['deposit_method'] = $method;
                $fields['payment_type'] = $this->paymentTypeLabel($method);
            }
            if (!empty($notes)) {
                $fields['deposit_payment_notes'] = $notes;
            }
            if (!empty($confirmStatusId)) {
                // The status captured at request time, not one re-inferred now.
                $fields['appointment_status'] = $confirmStatusId;
            }

            $appointment->groupQuery()->update($fields);

            AppointmentDepositLog::create([
                'appointment_id' => $appointment->id,
                'common_number' => $appointment->common_number,
                'event_type' => AppointmentDepositLog::PAID,
                'amount' => $appointment->deposit_amount,
                'old_status_id' => $oldStatusId,
                'new_status_id' => $confirmStatusId,
                'staff_id' => \Auth::check() ? \Auth::user()->id : null,
                'reason' => $method ? __('Paid by :method', ['method' => $method]) : null,
                'business_id' => $businessId,
                'created_by' => $createdBy,
            ]);
        });

        return true;
    }

    /**
     * Take a deposit at reception: cash, PayID, bank transfer, EFTPOS or a
     * manual card payment.
     *
     * This mirrors the gateway path rather than just flipping a flag — it
     * creates the deposit invoice if absent, syncs the line item to the amount
     * actually collected, writes a real payment record and settles the invoice.
     * Anything less and the money is invisible to every financial report.
     *
     * Deliberately NOT gated on isOnlinePaymentReady(): a business with no
     * gateway is exactly the business that depends on this path.
     */
    public function payOnTheSpot(Appointment $appointment, float $amount, string $method, ?string $notes = null, ?string $reference = null): array
    {
        if ($amount <= 0) {
            throw new \RuntimeException(__('Enter an amount greater than zero.'));
        }

        if ($this->mainInvoicePaid($appointment)) {
            throw new \RuntimeException(__('This booking has already been paid in full.'));
        }

        if (in_array($appointment->deposit_status, [self::FORFEITED, self::REFUNDED], true)) {
            throw new \RuntimeException(__('This deposit has already been closed.'));
        }

        $businessId = $appointment->business_id;
        $createdBy = $appointment->created_by;
        $amount = round($amount, 2);

        $invoice = DB::transaction(function () use ($appointment, $amount, $method, $notes, $reference, $businessId, $createdBy) {
            $invoice = $this->resolveDepositInvoice($appointment, $amount, $businessId, $createdBy);

            // Sync the line item and the invoice total to what was actually
            // collected, so the invoice never claims a figure nobody paid.
            $invoice->total = $amount;
            $invoice->save();

            $item = $invoice->items()->first();

            if (empty($item)) {
                $item = new DepositInvoiceItem(['deposit_invoice_id' => $invoice->id]);
            }

            $item->deposit_invoice_id = $invoice->id;
            $item->description = __('Deposit for booking :number', ['number' => $appointment->common_number]);
            $item->quantity = 1;
            $item->price = $amount;
            $item->save();

            DepositInvoicePayment::create([
                'deposit_invoice_id' => $invoice->id,
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference,
                'notes' => $notes,
                'payment_date' => now()->toDateString(),
                'received_by' => \Auth::check() ? \Auth::user()->id : null,
                'business_id' => $businessId,
                'created_by' => $createdBy,
            ]);

            $invoice->settle();

            // The group has to know which invoice holds its money, even when the
            // deposit was never formally requested first.
            $appointment->groupQuery()->update([
                'deposit_required' => 1,
                'deposit_amount' => $amount,
                'deposit_invoice_id' => $invoice->id,
            ]);

            return $invoice;
        });

        $appointment->refresh();
        $this->markPaid($appointment, $method, $notes);

        // Best-effort: the accounting invoice is a mirror of money already
        // collected above, so a failure here must not undo the deposit itself.
        try {
            $this->syncDepositInvoiceToAccounting($appointment, $invoice, $amount, $method);
        } catch (\Throwable $e) {
            report($e);
        }

        return ['invoice' => $invoice, 'amount' => $amount];
    }

    protected function paymentTypeLabel(string $method): string
    {
        return DepositInvoicePayment::MANUAL_METHODS[$method] ?? ucfirst(str_replace('_', ' ', $method));
    }

    /* --------------------------------------------------------------------- */
    /* Accounting mirror                                                     */
    /* --------------------------------------------------------------------- */

    /**
     * Mirror a collected deposit into the real accounting Invoice module, so
     * it shows up in the business's actual invoice list and reports rather
     * than only in the app's own deposit tracking.
     *
     * Tagged `invoice_module = 'appointment_deposit'`, distinct from the
     * `'appointment'` module the balance/checkout invoice uses
     * (InvoiceController::convertToInvoice) — both can share the same
     * appointment_id without one being mistaken for the other by the
     * duplicate-invoice guard there.
     *
     * A no-op when the Invoice module is inactive, or when this deposit
     * invoice was already mirrored (payOnTheSpot can be called more than
     * once for the same booking, e.g. a top-up payment).
     */
    protected function syncDepositInvoiceToAccounting(
        Appointment $appointment,
        DepositInvoice $depositInvoice,
        float $amount,
        string $method
    ): void {
        if (!module_is_active('Invoice') || !empty($depositInvoice->synced_invoice_id)) {
            return;
        }

        $businessId = $appointment->business_id;
        $createdBy = $appointment->created_by;

        $customer = $this->customerOf($appointment);

        if (empty($customer)) {
            return;
        }

        $product = $this->resolveDepositProduct($businessId, $createdBy);
        $nextId = DB::select("SHOW TABLE STATUS LIKE 'invoices'")[0]->Auto_increment;

        $accountingInvoice = new \Workdo\Invoice\Entities\Invoice();
        $accountingInvoice->appointment_id = $appointment->id;
        $accountingInvoice->customer_id = $customer->id;
        $accountingInvoice->user_id = $appointment->customer_id;
        $accountingInvoice->issue_date = now()->toDateString();
        $accountingInvoice->due_date = now()->toDateString();
        $accountingInvoice->business_id = $businessId;
        $accountingInvoice->created_by = $createdBy;
        $accountingInvoice->category_id = 2;
        $accountingInvoice->shipping_display = 0;
        $accountingInvoice->account_type = 'Account';
        $accountingInvoice->module = 'account';
        $accountingInvoice->invoice_id = $nextId;
        $accountingInvoice->status = 4;   // Paid — only ever created once money has actually been collected
        $accountingInvoice->invoice_module = 'appointment_deposit';
        $accountingInvoice->save();

        $invoiceProduct = new \Workdo\Invoice\Entities\InvoiceProduct();
        $invoiceProduct->invoice_id = $accountingInvoice->id;
        $invoiceProduct->product_id = $product->id;
        $invoiceProduct->product_type = 'service';
        $invoiceProduct->tax = '';   // no tax on a deposit
        $invoiceProduct->discount = 0;
        $invoiceProduct->quantity = 1;
        $invoiceProduct->price = $amount;
        $invoiceProduct->description = $product->name;
        $invoiceProduct->save();

        $invoicePayment = new \Workdo\Invoice\Entities\InvoicePayment();
        $invoicePayment->invoice_id = $accountingInvoice->id;
        $invoicePayment->date = now()->toDateString();
        $invoicePayment->amount = $amount;
        $invoicePayment->payment_method = 0;
        $invoicePayment->payment_type = $this->paymentTypeLabel($method);
        $invoicePayment->reference = __('Deposit Paid');
        $invoicePayment->description = __('Deposit paid against appointment number :number', ['number' => $appointment->common_number]);
        $invoicePayment->save();

        $depositInvoice->synced_invoice_id = $accountingInvoice->id;
        $depositInvoice->save();
    }

    /**
     * The catalog "service" a deposit line item is billed against — created
     * once per business, the same way every real service gets a
     * ProductService row (see ServiceController::store()), since the Invoice
     * module always resolves an item's displayed name through this catalog
     * rather than through free text.
     */
    protected function resolveDepositProduct($businessId, $createdBy): \Workdo\ProductService\Entities\ProductService
    {
        return \Workdo\ProductService\Entities\ProductService::firstOrCreate(
            [
                'name' => 'Deposit',
                'type' => 'service',
                'business_id' => $businessId,
                'created_by' => $createdBy,
            ],
            [
                'sku' => 'DEPOSIT',
                'unit_id' => 1,
                'description' => __('Deposit collected against a booking.'),
            ]
        );
    }

    /* --------------------------------------------------------------------- */
    /* Closing                                                               */
    /* --------------------------------------------------------------------- */

    /**
     * Keep the deposit after a no-show or a late cancellation.
     *
     * Moves no money by itself — the money is already collected. This records
     * the decision and the reason for it.
     */
    public function forfeit(Appointment $appointment, string $reason): void
    {
        if ($appointment->deposit_status !== self::PAID && $appointment->deposit_status !== self::PENDING) {
            throw new \RuntimeException(__('Only a pending or paid deposit can be forfeited.'));
        }

        DB::transaction(function () use ($appointment, $reason) {
            $appointment->groupQuery()->update([
                'deposit_status' => self::FORFEITED,
                'deposit_forfeit_reason' => $reason,
            ]);

            AppointmentDepositLog::create([
                'appointment_id' => $appointment->id,
                'common_number' => $appointment->common_number,
                'event_type' => AppointmentDepositLog::FORFEITED,
                'amount' => $appointment->deposit_amount,
                'old_status_id' => $appointment->appointment_status,
                'new_status_id' => $appointment->appointment_status,
                'staff_id' => \Auth::check() ? \Auth::user()->id : null,
                'reason' => $reason,
                'business_id' => $appointment->business_id,
                'created_by' => $appointment->created_by,
            ]);
        });
    }

    /**
     * Forfeit automatically when a booking is marked No Show, or Cancelled
     * inside the penalty window.
     *
     * Called from the appointment status observer, so it fires whichever screen
     * changed the status. Two rules:
     *
     *   - **No Show** always forfeits. A no-show is by definition discovered at
     *     the appointment time, so there is no window to be inside of.
     *   - **Cancelled** forfeits only within `deposit_forfeit_window_hours` of
     *     the start (default 24). A customer who cancels a week out keeps their
     *     deposit; that is the whole point of having a window.
     *
     * @return bool whether it forfeited
     */
    public function autoForfeit(Appointment $appointment, string $classification): bool
    {
        // Only money that has actually been taken can be kept. A pending deposit
        // was never paid, so there is nothing to forfeit.
        if ($appointment->deposit_status !== self::PAID) {
            return false;
        }

        if ($classification === AppointmentStatusClassifier::NO_SHOW) {
            $this->forfeit($appointment, __('Automatic: customer did not attend.'));

            return true;
        }

        if ($classification !== AppointmentStatusClassifier::CANCELLED) {
            return false;
        }

        $hours = company_setting('deposit_forfeit_window_hours', null, $appointment->business_id);
        $hours = is_numeric($hours) ? (int) $hours : 24;

        // 0 means never forfeit on cancellation, only on a no-show.
        if ($hours <= 0) {
            return false;
        }

        $start = $this->startsAt($appointment);

        if ($start === null) {
            // An unparseable date is not grounds to keep somebody's money.
            return false;
        }

        if ($start->gt(now()->addHours($hours))) {
            return false;   // cancelled with enough notice
        }

        $this->forfeit($appointment, __(
            'Automatic: cancelled within :hours hours of the appointment.',
            ['hours' => $hours]
        ));

        return true;
    }

    /**
     * When the booking actually starts, from the d-m-Y date and HH:MM-HH:MM time.
     */
    protected function startsAt(Appointment $appointment): ?\Carbon\Carbon
    {
        if (empty($appointment->date)) {
            return null;
        }

        try {
            $date = \Carbon\Carbon::createFromFormat('d-m-Y', $appointment->date)->startOfDay();
        } catch (\Exception $e) {
            try {
                $date = \Carbon\Carbon::parse($appointment->date)->startOfDay();
            } catch (\Exception $e) {
                return null;
            }
        }

        $start = trim(explode('-', (string) $appointment->time)[0] ?? '');

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $start, $matches)) {
            $date->setTime((int) $matches[1], (int) $matches[2]);
        }

        return $date;
    }

    /**
     * Issue a fresh payment link, invalidating the old one.
     *
     * Rotating the invoice token is what actually makes the previous URL dead —
     * anything less is a new link that does not stop the old one working, which
     * is exactly the situation staff reach for this button to fix (a link sent
     * to the wrong number, or forwarded on).
     *
     * @return array{success:bool,message:string,link:?string}
     */
    public function regenerateLink(Appointment $appointment): array
    {
        if ($appointment->deposit_status !== self::PENDING) {
            return ['success' => false, 'message' => __('Only a deposit awaiting payment has a link to reissue.'), 'link' => null];
        }

        $invoice = $appointment->deposit_invoice_id
            ? DepositInvoice::find($appointment->deposit_invoice_id)
            : null;

        if (empty($invoice)) {
            return ['success' => false, 'message' => __('This deposit has no payment link.'), 'link' => null];
        }

        $invoice->token = DepositInvoice::newToken();
        $invoice->save();

        AppointmentDepositLog::create([
            'appointment_id' => $appointment->id,
            'common_number' => $appointment->common_number,
            'event_type' => AppointmentDepositLog::LINK_REGENERATED,
            'amount' => $appointment->deposit_amount,
            'old_status_id' => $appointment->appointment_status,
            'new_status_id' => $appointment->appointment_status,
            'staff_id' => \Auth::check() ? \Auth::user()->id : null,
            'reason' => __('Previous payment link was invalidated.'),
            'business_id' => $appointment->business_id,
            'created_by' => $appointment->created_by,
        ]);

        return [
            'success' => true,
            'message' => __('A new payment link has been issued. The previous one no longer works.'),
            'link' => $invoice->payUrl(),
        ];
    }

    /**
     * The current payment link for a booking, or null when there isn't one.
     *
     * Always derived from the invoice rather than read from a stored column:
     * a cached URL survives a token rotation and silently keeps pointing at a
     * link that no longer works.
     */
    public function linkFor(Appointment $appointment): ?string
    {
        if (empty($appointment->deposit_invoice_id)) {
            return null;
        }

        $invoice = DepositInvoice::find($appointment->deposit_invoice_id);

        return $invoice ? $invoice->payUrl() : null;
    }

    /**
     * Record that a paid deposit was returned.
     *
     * The actual refund happens in the gateway or the till; this records the
     * decision and the external reference that proves it.
     */
    public function refund(Appointment $appointment, string $reference, ?string $reason = null): void
    {
        if ($appointment->deposit_status !== self::PAID) {
            throw new \RuntimeException(__('Only a paid deposit can be refunded.'));
        }

        DB::transaction(function () use ($appointment, $reference, $reason) {
            $appointment->groupQuery()->update([
                'deposit_status' => self::REFUNDED,
                'deposit_refund_reference' => $reference,
            ]);

            AppointmentDepositLog::create([
                'appointment_id' => $appointment->id,
                'common_number' => $appointment->common_number,
                'event_type' => AppointmentDepositLog::REFUNDED,
                'amount' => $appointment->deposit_amount,
                'old_status_id' => $appointment->appointment_status,
                'new_status_id' => $appointment->appointment_status,
                'staff_id' => \Auth::check() ? \Auth::user()->id : null,
                'reason' => $reason ?: __('Refund reference: :reference', ['reference' => $reference]),
                'business_id' => $appointment->business_id,
                'created_by' => $appointment->created_by,
            ]);
        });
    }

    /* --------------------------------------------------------------------- */
    /* Resending the link                                                    */
    /* --------------------------------------------------------------------- */

    /**
     * Re-send the payment link, for the common case of a corrected mobile
     * number.
     *
     * Creates no new deposit record and changes no deposit column. The link is
     * rebuilt from the invoice each time rather than replayed from storage, so
     * a stale URL is impossible. It sends synchronously and reads the message
     * log back, so staff are told whether the SMS actually left.
     *
     * @return array{success:bool,message:string}
     */
    public function resendLink(Appointment $appointment): array
    {
        if ($appointment->deposit_status !== self::PENDING) {
            return ['success' => false, 'message' => __('This deposit is not awaiting payment.')];
        }

        if (!$this->gate->isOnlinePaymentReady($appointment->business_id, $appointment->created_by)) {
            return ['success' => false, 'message' => $this->gate->unavailableReason()];
        }

        $invoice = $appointment->deposit_invoice_id
            ? DepositInvoice::find($appointment->deposit_invoice_id)
            : null;

        if (empty($invoice)) {
            return ['success' => false, 'message' => __('This deposit has no payment link to resend.')];
        }

        $customer = $this->customerOf($appointment);

        if (empty($customer) || empty($customer->customer->mobile_no ?? null)) {
            return ['success' => false, 'message' => __('This customer has no mobile number on file.')];
        }

        // Its own short debounce against double-clicks. Deliberately shorter than
        // the pipeline-wide suppression window, which this send bypasses: a
        // suppression window that swallowed a deliberate resend would make the
        // button silently do nothing, which is the exact failure it exists to fix.
        $last = SmsLog::latestFor($customer->customer->mobile_no, 'deposit_request_manual', $appointment->business_id);

        if (!empty($last) && $last->created_at && $last->created_at->gt(now()->subSeconds(20))) {
            return ['success' => false, 'message' => __('A link was just sent. Wait a moment before trying again.')];
        }

        $group = $this->groupOf($appointment);
        $earliest = Appointment::earliestOf($group) ?: $appointment;

        $outcome = $this->notifier->sendSms(
            $customer->customer->mobile_no,
            'deposit_request_manual',
            $this->templateData($customer, $earliest, $appointment->deposit_amount, $invoice),
            $appointment->business_id,
            $appointment->created_by,
            true,                       // bypass the duplicate-suppression window
            $customer->user_id,
            $appointment->id
        );

        AppointmentDepositLog::create([
            'appointment_id' => $appointment->id,
            'common_number' => $appointment->common_number,
            'event_type' => $outcome['success']
                ? AppointmentDepositLog::LINK_RESENT
                : AppointmentDepositLog::LINK_RESEND_FAILED,
            'amount' => $appointment->deposit_amount,
            'old_status_id' => $appointment->appointment_status,
            'new_status_id' => $appointment->appointment_status,
            'staff_id' => \Auth::check() ? \Auth::user()->id : null,
            'reason' => $outcome['message'],
            'business_id' => $appointment->business_id,
            'created_by' => $appointment->created_by,
        ]);

        return [
            'success' => $outcome['success'],
            'message' => $outcome['success']
                ? __('Payment link sent to :mobile.', ['mobile' => $customer->customer->mobile_no])
                : $outcome['message'],
        ];
    }

    /* --------------------------------------------------------------------- */
    /* Shared helpers                                                        */
    /* --------------------------------------------------------------------- */

    /**
     * `Workdo\Invoice` stores status as an index into Invoice::$statues —
     * 0 Draft, 1 Sent, 2 Unpaid, 3 Partialy Paid, 4 Paid.
     */
    protected const INVOICE_MODULE_PAID = 4;

    /**
     * Whether every deposit action should be refused because the booking has
     * already been settled in full.
     *
     * Two sources, because the platform has two kinds of invoice:
     *
     *  1. `appointments.converted_invoice` — the booking's real bill, raised by
     *     the Invoice module. This is the one that matters in practice, and
     *     checking only the local table meant the lockout never fired at all.
     *  2. A local appointment-type DepositInvoice, for installs without the
     *     Invoice module.
     *
     * Scoped strictly to the *appointment* invoice in both cases. Matching any
     * invoice would mean the booking's own deposit invoice becoming paid
     * instantly locks every deposit action, which reads to staff as the feature
     * being broken.
     */
    public function mainInvoicePaid(Appointment $appointment): bool
    {
        if (!empty($appointment->converted_invoice) && module_is_active('Invoice', $appointment->created_by)) {
            try {
                $invoice = \Workdo\Invoice\Entities\Invoice::find($appointment->converted_invoice);

                if (!empty($invoice) && (int) $invoice->status === self::INVOICE_MODULE_PAID) {
                    return true;
                }
            } catch (\Throwable $e) {
                // The module can be present but its tables absent on a partial
                // install. A lookup failure must not lock deposits.
                report($e);
            }
        }

        if (empty($appointment->common_number)) {
            return false;
        }

        return DepositInvoice::where('business_id', $appointment->business_id)
            ->where('common_number', $appointment->common_number)
            ->where('invoice_type', DepositInvoice::TYPE_APPOINTMENT)
            ->where('status', DepositInvoice::PAID)
            ->exists();
    }

    /**
     * The booking's bill status, as the UI and API report it: paid | unpaid.
     */
    public function invoiceStatus(Appointment $appointment): string
    {
        return $this->mainInvoicePaid($appointment) ? 'paid' : 'unpaid';
    }

    /**
     * Whether staff may raise a *new* deposit request on this booking.
     *
     * Both gates in one call, so every surface — details panel, listing,
     * calendar popup, API — asks the same question and cannot disagree.
     *
     * @return array{allowed:bool,reason:?string}
     */
    public function canRequestDeposit(Appointment $appointment): array
    {
        $lock = $this->actionability($appointment);

        if (!$lock['allowed']) {
            return $lock;
        }

        if (!$this->gate->isOnlinePaymentReady($appointment->business_id, $appointment->created_by)) {
            return ['allowed' => false, 'reason' => $this->gate->unavailableReason()];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Whether staff should be offered any deposit action on this booking.
     *
     * @return array{allowed:bool,reason:?string}
     */
    public function actionability(Appointment $appointment): array
    {
        if ($this->mainInvoicePaid($appointment)) {
            return ['allowed' => false, 'reason' => __('The booking has been paid in full.')];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * @return \Illuminate\Support\Collection<int,Appointment>
     */
    public function groupOf(Appointment $appointment)
    {
        if (empty($appointment->common_number)) {
            return collect([$appointment]);
        }

        $group = Appointment::group($appointment->common_number, $appointment->business_id, $appointment->created_by);

        return $group->isEmpty() ? collect([$appointment]) : collect($group->all());
    }

    /**
     * Sum of the group's service prices.
     *
     * Prefers the price actually booked (which rides on the payment row and may
     * have been overridden) over the current catalogue price.
     */
    public function groupTotal($group): float
    {
        $total = 0.0;

        foreach ($group as $appointment) {
            $payment = $appointment->payment;

            if (!empty($payment) && is_numeric($payment->amount)) {
                $total += (float) $payment->amount;
                continue;
            }

            $service = $appointment->ServiceData ?: Service::find($appointment->service_id);
            $total += (float) ($service->price ?? 0);
        }

        return round($total, 2);
    }

    /**
     * The status a paid deposit moves the booking to.
     *
     * Resolved from where the booking stands *at request time*, which is the
     * whole reason it is stored on the appointment rather than worked out again
     * during payment:
     *
     *   - a booking already Confirmed (or Reconfirmed) is being *re*-confirmed,
     *     so paying moves it to Reconfirmed;
     *   - anything else is a first confirmation, so paying moves it to Confirmed
     *     — or to whatever the business picked in deposit_confirm_status.
     *
     * Deciding this at payment time instead would give the wrong answer, because
     * by then the booking is sitting in Deposit Pending and no longer remembers
     * what it was before.
     */
    public function confirmStatus($businessId, $createdBy, ?Appointment $appointment = null): ?CustomStatus
    {
        if (!empty($appointment) && $this->isAlreadyConfirmed($appointment, $businessId, $createdBy)) {
            $reconfirmed = CustomStatus::findBySlug('reconfirmed', $businessId, $createdBy);

            if (!empty($reconfirmed)) {
                return $reconfirmed;
            }
        }

        $preferred = company_setting('deposit_confirm_status', null, $businessId);

        if (!empty($preferred)) {
            $status = CustomStatus::where('business_id', $businessId)
                ->where('created_by', $createdBy)
                ->where('id', $preferred)
                ->first();

            if (!empty($status)) {
                return $status;
            }
        }

        return CustomStatus::findBySlug('confirmed', $businessId, $createdBy);
    }

    /**
     * Whether this booking is already confirmed, and so a deposit raised
     * against it is a reconfirmation rather than a first confirmation.
     */
    protected function isAlreadyConfirmed(Appointment $appointment, $businessId, $createdBy): bool
    {
        if (empty($appointment->appointment_status)) {
            return false;
        }

        $current = CustomStatus::where('business_id', $businessId)
            ->where('created_by', $createdBy)
            ->where('id', $appointment->appointment_status)
            ->first();

        if (empty($current)) {
            return false;
        }

        // Matched on slug, not title: a business that renamed "Confirmed" to
        // "Locked in" must still get a reconfirmation here.
        return in_array($current->slug, ['confirmed', 'reconfirmed'], true);
    }

    public function customerOf(Appointment $appointment): ?Customer
    {
        if (empty($appointment->customer_id)) {
            return null;
        }

        // appointments.customer_id is a *user* id, so this joins on user_id.
        return Customer::with('customer')
            ->where('business_id', $appointment->business_id)
            ->where('created_by', $appointment->created_by)
            ->where('user_id', $appointment->customer_id)
            ->first();
    }

    /**
     * Create the deposit invoice and its single line item.
     */
    protected function createDepositInvoice(Appointment $appointment, float $amount, $businessId, $createdBy): DepositInvoice
    {
        $invoice = DepositInvoice::create([
            'invoice_number' => 'DEP-' . str_pad((string) (DepositInvoice::where('business_id', $businessId)->count() + 1), 5, '0', STR_PAD_LEFT),
            'common_number' => $appointment->common_number,
            'appointment_id' => $appointment->id,
            'customer_id' => $appointment->customer_id,
            'invoice_type' => DepositInvoice::TYPE_DEPOSIT,
            'status' => DepositInvoice::UNPAID,
            'total' => $amount,
            'paid_total' => 0,
            'token' => DepositInvoice::newToken(),
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
            'business_id' => $businessId,
            'created_by' => $createdBy,
        ]);

        DepositInvoiceItem::create([
            'deposit_invoice_id' => $invoice->id,
            'description' => __('Deposit for booking :number', ['number' => $appointment->common_number]),
            'quantity' => 1,
            'price' => $amount,
        ]);

        return $invoice;
    }

    protected function resolveDepositInvoice(Appointment $appointment, float $amount, $businessId, $createdBy): DepositInvoice
    {
        $invoice = null;

        if (!empty($appointment->deposit_invoice_id)) {
            $invoice = DepositInvoice::find($appointment->deposit_invoice_id);
        }

        if (empty($invoice)) {
            $invoice = $this->createDepositInvoice($appointment, $amount, $businessId, $createdBy);
        }

        if ($invoice->id !== $appointment->deposit_invoice_id) {
            $appointment->groupQuery()->update(['deposit_invoice_id' => $invoice->id]);
            $appointment->refresh();
        }

        return $invoice;
    }

    /**
     * Send the request email and SMS.
     *
     * Which template is used comes from whether the rule engine or a staff
     * member raised it — that is the whole reason deposit_requested_by is
     * nullable rather than defaulting to the acting user.
     */
    protected function notifyRequest(Appointment $appointment, DepositInvoice $invoice, float $amount, bool $isAuto, Appointment $earliest): array
    {
        $customer = $this->customerOf($appointment);

        if (empty($customer)) {
            return ['sms' => null, 'email' => null];
        }

        return $this->notifier->send(
            $customer,
            $isAuto ? 'deposit_request_auto' : 'deposit_request_manual',
            $this->templateData($customer, $earliest, $amount, $invoice),
            $appointment->business_id,
            $appointment->created_by,
            false,
            $appointment->id
        );
    }

    /**
     * Template tokens.
     *
     * The date and time come from the earliest-starting row in the group: a
     * customer told the time of the third service in their booking turns up an
     * hour late.
     */
    public function templateData(Customer $customer, Appointment $earliest, $amount, DepositInvoice $invoice): array
    {
        $currency = company_setting('defult_currancy_symbol', null, $earliest->business_id) ?: '$';

        return [
            'customer_name' => $customer->name,
            'client_name' => $customer->name,
            'appointment_date' => $earliest->date,
            'appointment_time' => $earliest->time,
            'deposit_amount' => $currency . number_format((float) $amount, 2),
            'payment_link' => $invoice->payUrl(),
            'business_name' => $this->notifier->businessName($earliest->business_id),
        ];
    }
}
