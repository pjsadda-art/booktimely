<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DepositInvoice;
use App\Models\DepositInvoicePayment;
use App\Services\DepositService;
use App\Services\OnlinePaymentGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The one customer-facing route that asks for money.
 *
 * Every feature that needs a customer to pay something links here, and here
 * only. The source system pointed its deposit link at a staff-only screen that
 * redirected to an admin page, so the customer arrived at a login form and the
 * deposit could never be paid — the fix is that there is exactly one public pay
 * route and it is this one.
 *
 * Deliberately unauthenticated: the invoice token *is* the credential. It is 48
 * hex characters from a CSPRNG and unique, so it is not guessable, and it
 * exposes nothing but one invoice.
 */
class DepositCheckoutController extends Controller
{
    protected DepositService $deposits;
    protected OnlinePaymentGate $gate;

    public function __construct(DepositService $deposits, OnlinePaymentGate $gate)
    {
        $this->deposits = $deposits;
        $this->gate = $gate;
    }

    /**
     * The checkout page.
     *
     * GET /deposit/pay/{token}
     */
    public function show($token)
    {
        $invoice = $this->findInvoice($token);

        if (empty($invoice)) {
            return view('deposit.checkout-missing');
        }

        $appointment = $this->appointmentFor($invoice);
        $gateways = $this->gate->availableGateways($invoice->business_id, $invoice->created_by);
        $currency = company_setting('defult_currancy_symbol', null, $invoice->business_id) ?: '$';
        $businessName = \App\Models\Business::find($invoice->business_id)->name ?? config('app.name');

        // Every service in the booking, not just the one row the invoice points
        // at: the deposit covers the whole group, so the customer should see
        // what they are paying against.
        $services = $this->servicesFor($invoice, $appointment);
        $customerName = $this->customerNameFor($invoice, $appointment);

        // Hours of notice before a cancellation forfeits the deposit. Shown so
        // the terms state a number rather than a vague "late cancellation".
        $forfeitHours = company_setting('deposit_forfeit_window_hours', null, $invoice->business_id);
        $forfeitHours = is_numeric($forfeitHours) ? (int) $forfeitHours : 24;

        return view('deposit.checkout', compact(
            'invoice',
            'appointment',
            'gateways',
            'currency',
            'businessName',
            'services',
            'customerName',
            'forfeitHours'
        ));
    }

    /**
     * Start a gateway payment.
     *
     * POST /deposit/pay/{token}/{gateway}
     */
    public function start(Request $request, $token, $gateway)
    {
        $invoice = $this->findInvoice($token);

        if (empty($invoice)) {
            return redirect()->route('deposit.pay', ['token' => $token]);
        }

        if ($invoice->isPaid()) {
            return redirect()->route('deposit.pay', ['token' => $token])
                ->with('success', __('This deposit has already been paid.'));
        }

        $available = $this->gate->availableGateways($invoice->business_id, $invoice->created_by);

        if (!isset($available[$gateway])) {
            return redirect()->route('deposit.pay', ['token' => $token])
                ->with('error', __('That payment method is not available.'));
        }

        if ($gateway === 'stripe') {
            return $this->startStripe($invoice);
        }

        // Other gateways declared ready in OnlinePaymentGate::GATEWAYS plug in
        // here. Each needs a start method and a return method; the settle path
        // below is shared, so a new gateway never re-implements the money rules.
        return redirect()->route('deposit.pay', ['token' => $token])
            ->with('error', __('That payment method is not supported yet.'));
    }

    /**
     * Gateway return leg.
     *
     * GET /deposit/pay/{token}/{gateway}/return
     */
    public function complete(Request $request, $token, $gateway)
    {
        $invoice = $this->findInvoice($token);

        if (empty($invoice)) {
            return view('deposit.checkout-missing');
        }

        // Already settled — a browser refresh or a gateway retry. Return early
        // rather than recording a second payment.
        if ($invoice->isPaid()) {
            return redirect()->route('deposit.pay', ['token' => $token]);
        }

        try {
            if ($gateway === 'stripe') {
                $this->completeStripe($request, $invoice);
            }
        } catch (\Exception $e) {
            Log::error('Deposit checkout failed: ' . $e->getMessage());

            return redirect()->route('deposit.pay', ['token' => $token])
                ->with('error', __('We could not confirm your payment. Please contact the salon.'));
        }

        return redirect()->route('deposit.pay', ['token' => $token]);
    }

    /* --------------------------------------------------------------------- */
    /* Stripe                                                                */
    /* --------------------------------------------------------------------- */

    protected function startStripe(DepositInvoice $invoice)
    {
        $settings = getCompanyAllSetting($invoice->created_by, $invoice->business_id);
        $currencyCode = company_setting('defult_currancy', null, $invoice->business_id) ?: 'USD';

        try {
            \Stripe\Stripe::setApiKey($settings['stripe_secret']);

            $session = \Stripe\Checkout\Session::create([
                'mode' => 'payment',
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => strtolower($currencyCode),
                        'unit_amount' => (int) round($invoice->outstanding() * 100),
                        'product_data' => [
                            'name' => __('Appointment deposit'),
                        ],
                    ],
                ]],
                'success_url' => route('deposit.pay.complete', [
                    'token' => $invoice->token,
                    'gateway' => 'stripe',
                ]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('deposit.pay', ['token' => $invoice->token]),
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe checkout session failed: ' . $e->getMessage());

            return redirect()->route('deposit.pay', ['token' => $invoice->token])
                ->with('error', __('We could not start the payment. Please try again.'));
        }

        return redirect($session->url);
    }

    protected function completeStripe(Request $request, DepositInvoice $invoice): void
    {
        $sessionId = $request->input('session_id');

        if (empty($sessionId)) {
            return;
        }

        $settings = getCompanyAllSetting($invoice->created_by, $invoice->business_id);
        \Stripe\Stripe::setApiKey($settings['stripe_secret']);

        $session = \Stripe\Checkout\Session::retrieve($sessionId);

        // Trust the gateway's own record of the charge, never the query string.
        if (($session->payment_status ?? null) !== 'paid') {
            return;
        }

        $this->settle($invoice, (float) $session->amount_total / 100, 'stripe', $session->payment_intent ?? $sessionId);
    }

    /* --------------------------------------------------------------------- */
    /* Shared settle path                                                    */
    /* --------------------------------------------------------------------- */

    /**
     * Record a gateway payment and move the booking on.
     *
     * Idempotent: a payment already recorded under the same gateway reference is
     * not recorded twice, because gateways retry their callbacks.
     */
    protected function settle(DepositInvoice $invoice, float $amount, string $method, ?string $reference): void
    {
        $alreadyRecorded = !empty($reference) && DepositInvoicePayment::where('deposit_invoice_id', $invoice->id)
            ->where('reference', $reference)
            ->exists();

        if ($alreadyRecorded) {
            return;
        }

        DepositInvoicePayment::create([
            'deposit_invoice_id' => $invoice->id,
            'amount' => $amount,
            'method' => $method,
            'reference' => $reference,
            'payment_date' => now()->toDateString(),
            'business_id' => $invoice->business_id,
            'created_by' => $invoice->created_by,
        ]);

        // Only the transition into `paid` runs the post-payment side effects.
        if (!$invoice->settle()) {
            return;
        }

        $appointment = $this->appointmentFor($invoice);

        if (!empty($appointment)) {
            $this->deposits->markPaid($appointment, $method);
        }
    }

    /* --------------------------------------------------------------------- */
    /* Lookups                                                               */
    /* --------------------------------------------------------------------- */

    /**
     * The services this deposit secures.
     *
     * @return array<int,string>
     */
    protected function servicesFor(DepositInvoice $invoice, ?Appointment $appointment): array
    {
        if (empty($invoice->common_number)) {
            return array_filter([$appointment->ServiceData->name ?? null]);
        }

        return Appointment::with('ServiceData')
            ->where('business_id', $invoice->business_id)
            ->where('created_by', $invoice->created_by)
            ->where('common_number', $invoice->common_number)
            ->orderBy('id')
            ->get()
            ->map(fn($row) => $row->ServiceData->name ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * The customer's name for the checkout header.
     *
     * `deposit_invoices.customer_id` holds a users.id, matching appointments —
     * not a customers.id. Getting that wrong here would greet the customer by
     * somebody else's name.
     */
    protected function customerNameFor(DepositInvoice $invoice, ?Appointment $appointment): ?string
    {
        if (!empty($invoice->customer_id)) {
            $user = \App\Models\User::find($invoice->customer_id);

            if (!empty($user)) {
                return $user->name;
            }
        }

        return $appointment->CustomerData->name ?? ($appointment->name ?? null);
    }

    protected function findInvoice($token): ?DepositInvoice
    {
        if (empty($token)) {
            return null;
        }

        return DepositInvoice::with('items')
            ->where('token', $token)
            ->where('invoice_type', DepositInvoice::TYPE_DEPOSIT)
            ->first();
    }

    /**
     * The booking this invoice belongs to.
     *
     * Prefers the group over the single stored appointment id, and quotes the
     * earliest-starting row: a customer told the start time of the third service
     * in their booking turns up an hour late.
     */
    protected function appointmentFor(DepositInvoice $invoice): ?Appointment
    {
        if (!empty($invoice->common_number)) {
            $group = Appointment::group($invoice->common_number, $invoice->business_id, $invoice->created_by);

            if ($group->isNotEmpty()) {
                return Appointment::earliestOf($group);
            }
        }

        return $invoice->appointment_id ? Appointment::find($invoice->appointment_id) : null;
    }
}
