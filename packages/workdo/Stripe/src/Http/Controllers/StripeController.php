<?php

namespace Workdo\Stripe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Models\Order;
use App\Traits\PaymentTrait;

class StripeController extends Controller
{
    use PaymentTrait;

    public $stripe_secret;

    public function settingConfig(Request $request)
    {
        return $this->paymentSetting($request, 'stripe manage', 'stripe_is_on', ['stripe_key' => 'required|string', 'stripe_secret' => 'required|string']);
    }

    public function stripePayment($stripe_secret, $pay, $currency, $price)
    {
        try {
            $stripe_formatted_price     = in_array($currency, ['MGA', 'BIF', 'CLP', 'PYG', 'DJF', 'RWF', 'GNF', 'UGX', 'JPY', 'VND', 'VUV', 'XAF', 'KMF', 'KRW', 'XOF', 'XPF',]) ? number_format($price, 2, '.', '') : number_format($price, 2, '.', '') * 100;
            \Stripe\Stripe::setApiKey($stripe_secret);
            $stripe_session = '';
            $stripe_session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency'      => $currency ?? '',
                            'unit_amount'   => (int) $stripe_formatted_price,
                            'product_data'  => [
                                'name'          => $pay['name'] ?? '',
                                'description'   => $pay['description'] ?? '',
                            ],
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'metadata' => [
                    'user_id'           => $pay['user_id'] ?? '',
                    'package_id'        => $pay['package_id'] ?? '',
                    'payment_frequency' => $pay['payment_frequency'] ?? '',
                    'code'              => $pay['code'] ?? '',
                ],
                'success_url'   => $pay['success_url'] ?? '',
                'cancel_url'    => $pay['cancel_url'] ?? '',
            ]);
            return (object) ['status' => 'success', 'session' => $stripe_session];
        } catch (\Exception $e) {
            return (object) ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Plan Payment
    public function planPayWithStripe(Request $request)
    {
        $pre_pay = $this->payThisPlan($request, 'Stripe');
        if ($pre_pay->status == 'success' && $pre_pay->plan_type !== 'free') {

            $this->stripe_secret = isset($pre_pay->settings['stripe_secret']) ? $pre_pay->settings['stripe_secret'] : '';
            $duration       = $pre_pay->duration;
            $payment_type   = $pre_pay->Order['payment_type'];

            $return_url_parameters  = function ($return_type) use ($duration, $payment_type) {
                return '&return_type=' . $return_type . '&payment_processor=stripe&payment_frequency=' . $duration . '&payment_type=' . $payment_type;
            };

            $pay =  $this->stripePayment($this->stripe_secret, [
                'name'              =>  !empty($pre_pay->plan->name) ? $pre_pay->plan->name : 'Basic Package',
                'description'       =>  $pre_pay->duration,
                'user_id'           =>  $pre_pay->user->id,
                'package_id'        =>  $pre_pay->plan->id,
                'payment_frequency' =>  $pre_pay->plan->duration,
                'success_url'       => route('plan.get.payment.status', ['plan_id' => $pre_pay->plan->id, 'order_id' => $pre_pay->order_id, 'other_order_id' => $pre_pay->other_order_id, $return_url_parameters('success')]),
                'cancel_url'        => route('plan.get.payment.status', ['plan_id' => $pre_pay->order_id, 'order_id' => $pre_pay->order_id, 'other_order_id' => $pre_pay->other_order_id, $return_url_parameters('cancel'),]),
            ], $pre_pay->currency, $pre_pay->price);

            if ($pay->status == 'success') {
                Session::put($pre_pay->other_order_id, $pay->session);
                Order::create($pre_pay->Order);
                $stripe_session =  $pay->session;
                return view('stripe::plan.request', compact('stripe_session'));
            } else {
                return redirect()->route('plans.index')->with($pay->status, $pay->message);
            }
        } else {
            return redirect()->route('plans.index')->with($pre_pay->status, $pre_pay->message);
        }
    }

    public function planGetStripeStatus(Request $request)
    {
        if ($request->return_type == 'success') {
            $adminSetting   =   getAdminAllSetting();
            $stripe         = new \Stripe\StripeClient(!empty($adminSetting['stripe_secret']) ? $adminSetting['stripe_secret'] : '');
            $stripe_session = Cache::get($request->other_order_id);
            $payment_intent = isset($stripe_session->payment_intent) ? $stripe_session->payment_intent : '';
            $receipt_url    = "";

            if (isset($payment_intent) && !empty($payment_intent)) {
                $paymentIntents     = $stripe->paymentIntents->retrieve($payment_intent, []);
                $receipt_url        = $paymentIntents->charges->data[0]->receipt_url;
            }

            Session::forget($request->other_order_id);
            $verify =  $this->statusThisPlan($request, $request->return_type, false, $receipt_url);
            return redirect()->route('plans.index')->with($verify->status, $verify->message);
        } else {
            return redirect()->route('plans.index')->with('error', __("The transaction has been failed"));
        }
    }



    // Appointment Payment
    public function appointmentPayWithStripe(Request $request)
    {
        $pre_pay = $this->payThisAppointment($request);

        $this->stripe_secret = isset($pre_pay->settings['stripe_secret']) ? $pre_pay->settings['stripe_secret'] : '';

        $return_url_parameters      = function ($return_type) {
            return '&return_type=' . $return_type;
        };

        $comapany_stripe_pay =  $this->stripePayment($this->stripe_secret, [
            'name'              =>  'Booking',
            'description'       =>  $pre_pay->business['slug'],
            'user_id'           =>  '',
            'package_id'        =>  $pre_pay->business['slug'],
            'payment_frequency' =>  "Appointment",
            'success_url'       => route('appointment.stripe.status', ['slug' => $pre_pay->business['slug'], 'order_id' => $pre_pay->order_id, 'other_order_id' => $pre_pay->other_order_id, $return_url_parameters('success'),]),
            'cancel_url'        => route('appointments.form', ['slug' => $pre_pay->business['slug'], 'order_id' => $pre_pay->order_id, 'other_order_id' => $pre_pay->other_order_id, $return_url_parameters('cancel'),]),
        ], $pre_pay->currency, $pre_pay->price);

        if ($comapany_stripe_pay->status == 'success') {
            Session::put($pre_pay->other_order_id, $comapany_stripe_pay->session);
            $comapany_stripe_pay = $comapany_stripe_pay ?? false;
            return response()->json(['status' => 'success', 'url' => $comapany_stripe_pay->session->url, 'message' => $pre_pay->message]);
        } else {
            isset($pre_pay->attachment) ? delete_file($pre_pay->attachment) : '';
            return response()->json(['status' => $comapany_stripe_pay->status, 'url' => $pre_pay->fail_url, 'message' => $comapany_stripe_pay->message]);
        }
    }

    public function getAppointmentPaymentStatus(Request $request, $slug)
    {
        if ($request->return_type == 'success') {
            $stripe_session = Session::get($request->other_order_id);
            $data_session   = Cache::get($request->order_id);
            $stripe         = new \Stripe\StripeClient(!empty(company_setting('stripe_secret', $data_session['business']['created_by'], $data_session['business']['id'])) ? company_setting('stripe_secret', $data_session['business']['created_by'], $data_session['business']['id']) : '');
            $payment_intent = isset($stripe_session->payment_intent) ? $stripe_session->payment_intent : '';
            $receipt_url    = "";
            if (isset($payment_intent) && !empty($payment_intent)) {
                $paymentIntents     = $stripe->paymentIntents->retrieve($payment_intent, []);
                $receipt_url        = $paymentIntents->charges->data[0]->receipt_url;
            }

            $status =  $this->statusThisAppointment($request, $slug, $request->return_type, false, $receipt_url);
            return redirect()->route('appointments.form', ['slug' => $status->slug, 'appointment' => $status->appointment])->withFragment('appointment')->with($status->status, $status->message);
        } else {
            return redirect()->route('appointments.form', ['slug' => $slug, 'appointment' => 'failed'])->withFragment('appointment')->with('error', __("The transaction has been failed"));
        }
    }
}
