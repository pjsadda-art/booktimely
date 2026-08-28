<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DepositInvoicePayment;
use App\Services\DepositService;
use App\Services\OnlinePaymentGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Staff-facing deposit actions.
 *
 * Every action here operates on a *booking group*: the appointment id in the
 * URL identifies the booking, and the service writes to every row sharing its
 * common_number.
 */
class DepositController extends Controller
{
    protected DepositService $deposits;
    protected OnlinePaymentGate $gate;

    public function __construct(DepositService $deposits, OnlinePaymentGate $gate)
    {
        $this->deposits = $deposits;
        $this->gate = $gate;
    }

    /**
     * The "request a deposit" modal.
     */
    public function create($id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return response()->json(['error' => __('Appointment not found.')], 404);
        }

        $group = $this->deposits->groupOf($appointment);
        $total = $this->deposits->groupTotal($group);

        $defaultPercentage = company_setting('deposit_default_percentage', null, $appointment->business_id);
        $defaultPercentage = is_numeric($defaultPercentage) ? (float) $defaultPercentage : 15;

        $gatewayReady = $this->gate->isOnlinePaymentReady($appointment->business_id, $appointment->created_by);
        $lock = $this->deposits->actionability($appointment);

        return view('deposit.request', compact(
            'appointment',
            'group',
            'total',
            'defaultPercentage',
            'gatewayReady',
            'lock'
        ));
    }

    /**
     * Raise a deposit manually.
     */
    public function store(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return redirect()->back()->with('error', __('Appointment not found.'));
        }

        $validator = \Validator::make($request->all(), [
            'mode' => 'required|in:percentage,fixed',
            'percentage' => 'required_if:mode,percentage|nullable|numeric|min:0.01|max:100',
            'amount' => 'required_if:mode,fixed|nullable|numeric|min:0.01',
            'message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        try {
            $result = $this->deposits->requestDeposit($appointment, [
                'percentage' => $request->input('mode') === 'percentage' ? $request->input('percentage') : null,
                'amount' => $request->input('mode') === 'fixed' ? $request->input('amount') : null,
                // A staff id here, never null: the null is reserved for the rule
                // engine and selects a different message template.
                'requested_by' => Auth::user()->id,
                'message' => $request->input('message'),
            ]);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()->with('error', __('The deposit could not be raised.'));
        }

        $warnings = [];

        foreach (['sms' => __('SMS'), 'email' => __('Email')] as $channel => $label) {
            $outcome = $result['notified'][$channel] ?? null;

            if (!empty($outcome) && empty($outcome['success'])) {
                $warnings[] = $label . ': ' . $outcome['message'];
            }
        }

        $message = __('Deposit request sent.');

        if (!empty($warnings)) {
            // The deposit is raised either way; say so, and say what did not go out.
            return redirect()->back()->with(
                'error',
                $message . ' ' . __('But some notifications failed —') . ' ' . implode(' ', $warnings)
            );
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * The "take payment now" modal.
     */
    public function paymentForm($id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return response()->json(['error' => __('Appointment not found.')], 404);
        }

        $group = $this->deposits->groupOf($appointment);
        $total = $this->deposits->groupTotal($group);
        $methods = DepositInvoicePayment::MANUAL_METHODS;
        $lock = $this->deposits->actionability($appointment);

        return view('deposit.pay-on-spot', compact('appointment', 'total', 'methods', 'lock'));
    }

    /**
     * Take a deposit at reception.
     *
     * Not gated on the online-payment check: a business with no gateway is
     * exactly the business that depends on this path.
     */
    public function payOnSpot(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return redirect()->back()->with('error', __('Appointment not found.'));
        }

        $validator = \Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:' . implode(',', array_keys(DepositInvoicePayment::MANUAL_METHODS)),
            'reference' => 'nullable|string|max:191',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        try {
            $this->deposits->payOnTheSpot(
                $appointment,
                (float) $request->input('amount'),
                $request->input('method'),
                $request->input('notes'),
                $request->input('reference')
            );
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            report($e);

            return redirect()->back()->with('error', __('The payment could not be recorded.'));
        }

        return redirect()->back()->with('success', __('Deposit payment recorded.'));
    }

    /**
     * The forfeit modal.
     */
    public function forfeitForm($id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return response()->json(['error' => __('Appointment not found.')], 404);
        }

        return view('deposit.forfeit', compact('appointment'));
    }

    public function forfeit(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return redirect()->back()->with('error', __('Appointment not found.'));
        }

        $validator = \Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        try {
            $this->deposits->forfeit($appointment, $request->input('reason'));
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __('Deposit marked as forfeited.'));
    }

    /**
     * The refund modal.
     */
    public function refundForm($id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return response()->json(['error' => __('Appointment not found.')], 404);
        }

        return view('deposit.refund', compact('appointment'));
    }

    public function refund(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return redirect()->back()->with('error', __('Appointment not found.'));
        }

        $validator = \Validator::make($request->all(), [
            'reference' => 'required|string|max:191',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        try {
            $this->deposits->refund($appointment, $request->input('reference'), $request->input('reason'));
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __('Deposit marked as refunded.'));
    }

    /**
     * Issue a fresh payment link, invalidating the previous one.
     *
     * Separate from resend: resend delivers the *same* link again to a corrected
     * number, whereas this kills the old URL. Use it when the old link has gone
     * somewhere it should not have.
     */
    public function regenerate($id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return response()->json(['success' => false, 'message' => __('Appointment not found.')], 404);
        }

        $result = $this->deposits->regenerateLink($appointment);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Resend the payment link. Answers JSON: the button lives on the customer
     * profile's Deposits tab and reports the real send outcome inline.
     */
    public function resend($id)
    {
        if (!Auth::user()->isAbleTo('deposit manage')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return response()->json(['success' => false, 'message' => __('Appointment not found.')], 404);
        }

        $result = $this->deposits->resendLink($appointment);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Tenancy-scoped lookup. Reads are scoped by business; both keys are used
     * here because every caller is acting inside one company's admin.
     */
    protected function findAppointment($id): ?Appointment
    {
        return Appointment::with(['ServiceData', 'CustomerData', 'payment'])
            ->where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->find($id);
    }
}
