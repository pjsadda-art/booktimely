<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DepositInvoice;
use App\Models\DepositInvoicePayment;
use App\Services\DepositService;
use App\Services\OnlinePaymentGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Deposit endpoints for the mobile app and integrations.
 *
 * Thin over DepositService — every rule (the gateway gate, the paid-in-full
 * lockout, idempotent payment, the Reconfirmed transition) lives there and is
 * shared with the web screens. Re-implementing any of it here is how the two
 * surfaces drift into disagreeing about the same booking.
 */
class DepositApiController extends Controller
{
    protected DepositService $deposits;
    protected OnlinePaymentGate $gate;

    public function __construct(DepositService $deposits, OnlinePaymentGate $gate)
    {
        $this->deposits = $deposits;
        $this->gate = $gate;
    }

    /**
     * POST /api/appointments/{id}/request-deposit
     *
     * { "mode": "percentage"|"fixed", "percentage": 20, "amount": 50, "message": "…" }
     */
    public function requestDeposit(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return $this->fail(__('Permission denied.'), 403);
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return $this->fail(__('Appointment not found.'), 404);
        }

        $validator = \Validator::make($request->all(), [
            'mode' => 'required|in:percentage,fixed',
            'percentage' => 'required_if:mode,percentage|nullable|numeric|min:0.01|max:100',
            'amount' => 'required_if:mode,fixed|nullable|numeric|min:0.01',
            'message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 422);
        }

        try {
            $result = $this->deposits->requestDeposit($appointment, [
                'percentage' => $request->input('mode') === 'percentage' ? $request->input('percentage') : null,
                'amount' => $request->input('mode') === 'fixed' ? $request->input('amount') : null,
                // A real staff id: null is reserved for the rule engine and
                // selects the automatic message template instead.
                'requested_by' => Auth::user()->id,
                'message' => $request->input('message'),
            ]);
        } catch (\RuntimeException $e) {
            // Business rules — no gateway configured, already paid in full,
            // nothing priced to take a deposit against.
            return $this->fail($e->getMessage(), 422);
        } catch (\Exception $e) {
            report($e);

            return $this->fail(__('The deposit could not be raised.'), 500);
        }

        $appointment->refresh();

        return response()->json([
            'status' => 'success',
            'depositAmount' => round((float) $result['amount'], 2),
            'paymentLink' => $result['pay_url'],
            'depositStatus' => $appointment->deposit_status,
            'appointmentStatus' => $appointment->appointment_status,
            'notified' => [
                'sms' => (bool) ($result['notified']['sms']['success'] ?? false),
                'email' => (bool) ($result['notified']['email']['success'] ?? false),
            ],
        ]);
    }

    /**
     * POST /api/appointments/{id}/deposit/manual-payment
     *
     * { "method": "cash", "amount": 50, "notes": "Paid at reception" }
     *
     * Deposits taken at the counter — cash, PayID, bank transfer, EFTPOS, manual
     * card. Deliberately NOT gated on the online-payment check: a salon with no
     * Stripe or PayPal is exactly the one that relies on this path, and gating
     * it would leave them unable to record a deposit at all.
     */
    public function manualDepositPayment(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return $this->fail(__('Permission denied.'), 403);
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return $this->fail(__('Appointment not found.'), 404);
        }

        $validator = \Validator::make($request->all(), [
            'method' => 'required|string|max:32',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
            'reference' => 'nullable|string|max:191',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 422);
        }

        $method = $this->normaliseMethod($request->input('method'));

        if ($method === null) {
            return $this->fail(__('Unknown payment method: :method', ['method' => $request->input('method')]), 422);
        }

        try {
            // Goes through the same routine as the web counter screen, so the
            // money lands on a real payment row against the deposit invoice and
            // is visible to financial reporting — not just a flipped flag.
            $result = $this->deposits->payOnTheSpot(
                $appointment,
                (float) $request->input('amount'),
                $method,
                $request->input('notes'),
                $request->input('reference')
            );
        } catch (\RuntimeException $e) {
            // Covers the paid-in-full lockout and an already-closed deposit.
            return $this->fail($e->getMessage(), 422);
        } catch (\Exception $e) {
            report($e);

            return $this->fail(__('The payment could not be recorded.'), 500);
        }

        $appointment->refresh();

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => __('Deposit marked as paid manually'),
            'appointment' => [
                'id' => $appointment->id,
                'deposit_status' => $appointment->deposit_status,
                'deposit_amount' => (float) $appointment->deposit_amount,
                'deposit_method' => $appointment->deposit_method,
                // Confirmed, or Reconfirmed when the booking was already
                // confirmed when the deposit was raised.
                'appointment_status' => $appointment->appointment_status,
                'invoice_status' => $this->deposits->invoiceStatus($appointment),
            ],
            'invoiceId' => $result['invoice']->id ?? null,
        ]);
    }

    /**
     * Map an incoming method name onto the stored vocabulary.
     *
     * The API accepts the shorter public names ("bank", "other") as well as the
     * stored ones, so a caller written against either spelling works.
     */
    protected function normaliseMethod($method): ?string
    {
        $method = strtolower(trim((string) $method));

        $aliases = [
            'bank' => 'bank_transfer',
            'banktransfer' => 'bank_transfer',
            'bank-transfer' => 'bank_transfer',
            'other' => 'manual_card',
            'card' => 'manual_card',
            'pos' => 'manual_card',
            'manual' => 'manual_card',
        ];

        $method = $aliases[$method] ?? $method;

        return array_key_exists($method, DepositInvoicePayment::MANUAL_METHODS) ? $method : null;
    }

    /**
     * POST /api/deposits/{appointmentId}/payment-callback
     *
     * Records an external payment and moves the booking on.
     *
     * Unauthenticated by design — gateways call it server-to-server — so it is
     * authorised by the invoice token instead, which the caller must supply and
     * which is not guessable. Never trust the appointment id alone here.
     */
    public function paymentCallback(Request $request, $appointmentId)
    {
        $validator = \Validator::make($request->all(), [
            'token' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:191',
            'method' => 'nullable|string|max:32',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 422);
        }

        $appointment = Appointment::find($appointmentId);

        if (empty($appointment)) {
            return $this->fail(__('Appointment not found.'), 404);
        }

        $invoice = $appointment->deposit_invoice_id
            ? DepositInvoice::find($appointment->deposit_invoice_id)
            : null;

        // The token must match *this* booking's invoice. Comparing in constant
        // time so a caller cannot probe for a valid token byte by byte.
        if (empty($invoice) || !hash_equals((string) $invoice->token, (string) $request->input('token'))) {
            return $this->fail(__('Invalid payment token.'), 403);
        }

        // Already settled — a gateway retry. Answer success without recording a
        // second payment, so retries are safe.
        if ($appointment->deposit_status === DepositService::PAID) {
            return response()->json([
                'status' => 'success',
                'alreadyPaid' => true,
                'depositStatus' => $appointment->deposit_status,
                'appointmentStatus' => $appointment->appointment_status,
            ]);
        }

        $amount = $request->filled('amount')
            ? (float) $request->input('amount')
            : $invoice->outstanding();
        $method = $request->input('method', 'gateway');
        $reference = $request->input('reference');

        try {
            // Same guard as the web checkout: a repeat of a reference already
            // recorded must not become a second payment row.
            $duplicate = !empty($reference) && DepositInvoicePayment::where('deposit_invoice_id', $invoice->id)
                ->where('reference', $reference)
                ->exists();

            if (!$duplicate) {
                DepositInvoicePayment::create([
                    'deposit_invoice_id' => $invoice->id,
                    'amount' => $amount,
                    'method' => $method,
                    'reference' => $reference,
                    'payment_date' => now()->toDateString(),
                    'business_id' => $invoice->business_id,
                    'created_by' => $invoice->created_by,
                ]);
            }

            // Only the transition into `paid` runs the post-payment side effects.
            if ($invoice->settle()) {
                $this->deposits->markPaid($appointment, $method);
            }
        } catch (\Exception $e) {
            report($e);

            return $this->fail(__('The payment could not be recorded.'), 500);
        }

        $appointment->refresh();

        return response()->json([
            'status' => 'success',
            'depositStatus' => $appointment->deposit_status,
            'appointmentStatus' => $appointment->appointment_status,
            'invoiceStatus' => $invoice->fresh()->status,
        ]);
    }

    /**
     * POST /api/appointments/{id}/forfeit-deposit
     */
    public function forfeit(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return $this->fail(__('Permission denied.'), 403);
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return $this->fail(__('Appointment not found.'), 404);
        }

        $validator = \Validator::make($request->all(), ['reason' => 'required|string|max:500']);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 422);
        }

        try {
            $this->deposits->forfeit($appointment, $request->input('reason'));
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return response()->json([
            'status' => 'success',
            'depositStatus' => $appointment->fresh()->deposit_status,
        ]);
    }

    /**
     * POST /api/appointments/{id}/refund-deposit
     */
    public function refund(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return $this->fail(__('Permission denied.'), 403);
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return $this->fail(__('Appointment not found.'), 404);
        }

        $validator = \Validator::make($request->all(), [
            'reference' => 'required|string|max:191',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first(), 422);
        }

        try {
            $this->deposits->refund($appointment, $request->input('reference'), $request->input('reason'));
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return response()->json([
            'status' => 'success',
            'depositStatus' => $appointment->fresh()->deposit_status,
        ]);
    }

    /**
     * GET /api/appointments/{id}/deposit
     *
     * The booking's current deposit state, for a details screen.
     */
    public function show($id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return $this->fail(__('Permission denied.'), 403);
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return $this->fail(__('Appointment not found.'), 404);
        }

        $lock = $this->deposits->actionability($appointment);
        $canRequest = $this->deposits->canRequestDeposit($appointment);

        return response()->json([
            'status' => 'success',
            // The two things a client needs to decide which buttons to show.
            'invoiceStatus' => $this->deposits->invoiceStatus($appointment),
            'canRequestDeposit' => $canRequest['allowed'],
            'canRequestDepositReason' => $canRequest['reason'],
            // Counter payments stay available even when the above is false —
            // that is the point of the on-the-spot path.
            'canTakeManualPayment' => $lock['allowed'],
            'deposit' => [
                'required' => (bool) $appointment->deposit_required,
                'status' => $appointment->deposit_status ?: 'none',
                'amount' => $appointment->deposit_amount !== null ? (float) $appointment->deposit_amount : null,
                'percentage' => $appointment->deposit_percentage !== null ? (float) $appointment->deposit_percentage : null,
                // Derived from the invoice, so it is never a stale URL.
                'paymentLink' => $this->deposits->linkFor($appointment),
                'method' => $appointment->deposit_method,
                'paidAt' => $appointment->deposit_paid_at?->toIso8601String(),
                'forfeitReason' => $appointment->deposit_forfeit_reason,
                'refundReference' => $appointment->deposit_refund_reference,
                // Null means the automatic rule engine raised it.
                'requestedBy' => $appointment->deposit_requested_by,
                'actionsAllowed' => $lock['allowed'],
                'lockedReason' => $lock['reason'],
            ],
            'onlinePaymentReady' => $this->gate->isOnlinePaymentReady(
                $appointment->business_id,
                $appointment->created_by
            ),
        ]);
    }

    /* --------------------------------------------------------------------- */

    protected function findAppointment($id): ?Appointment
    {
        return Appointment::with(['ServiceData', 'CustomerData', 'payment'])
            ->where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->find($id);
    }

    protected function fail(string $message, int $code)
    {
        return response()->json(['status' => 'error', 'message' => $message], $code);
    }
}
