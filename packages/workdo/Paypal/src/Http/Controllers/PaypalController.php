<?php

namespace Workdo\Paypal\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Order;
use App\Traits\PaymentTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Workdo\Paypal\Trait\Paypal;

class PaypalController extends Controller
{
    use PaymentTrait, Paypal;

    // Setting Store
    public function setting(Request $request)
    {
        return $this->paymentSetting($request, 'paypal manage', 'paypal_payment_is_on', ['company_paypal_mode' => 'required|string', 'company_paypal_client_id' => 'required|string', 'company_paypal_secret_key' => 'required|string']);
    }

    // Plan Payment
    public function planPayWithPaypal(Request $request)
    {
        $pre_pay = $this->payThisPlan($request, 'Paypal');
        if ($pre_pay->status == 'success' && $pre_pay->plan_type !== 'free') {
            try {
                $this->customerConfig($pre_pay->settings);
                $payPalPayment = $this->payPalPayment([
                    "return_url"    => route('plan.get.paypal.status', [$pre_pay->plan->id, 'order_id' => $pre_pay->order_id,]),
                    "cancel_url"    => route('plan.get.paypal.status', [$pre_pay->plan->id, 'order_id' => $pre_pay->order_id,]),
                    "currency_code" => admin_setting('defult_currancy'),
                    "currency_code" => !empty(admin_setting('defult_currancy')) ? admin_setting('defult_currancy') : '',
                    "value"         => $pre_pay->price,
                ]);


                if ($payPalPayment->status == 'success') {
                    //set order
                    Order::create($pre_pay->Order);
                    return redirect()->away($payPalPayment->url);
                } else {
                    return redirect()->route('plans.index')->with($payPalPayment->status, $payPalPayment->message);
                }
            } catch (\Exception $e) {
                return redirect()->route('plans.index')->with('error', __($e->getMessage()));
            }
        } else {
            return redirect()->route('plans.index')->with($pre_pay->status, $pre_pay->message);
        }
    }

    public function planGetPaypalStatus(Request $request, $plan_id)
    {
        $this->customerConfig(getAdminAllSetting());
        $payment_status = $this->payPalPaymentStatus($request['token']);
        $verify = $this->statusThisPlan($request, $payment_status->status);
        return redirect()->route('plans.index')->with($verify->status, $verify->message);
    }



    // Appointment Payment
    public function appointmentPayWithPaypal(Request $request)
    {
        $pre_pay = $this->payThisAppointment($request);
        $this->customerConfig($pre_pay->settings);
        $payPalAppointmentPayment = $this->payPalPayment([
            "return_url"    => route('appointment.paypal.status', ['slug' => $pre_pay->business['slug'],  'order_id' => $pre_pay->order_id]),
            "cancel_url"    => route('appointment.paypal.status', ['slug' => $pre_pay->business['slug'],  'order_id' => $pre_pay->order_id]),
            "currency_code" => !empty(company_setting('defult_currancy', $pre_pay->business['created_by'], $pre_pay->business['id'])) ? company_setting('defult_currancy', $pre_pay->business['created_by'], $pre_pay->business['id']) : '',
            "value"         => $pre_pay->price,
        ]);

        $url = ($payPalAppointmentPayment->status == 'success') ? $payPalAppointmentPayment->url : '';
        if ($url) {
            return response()->json(['status' => 'success', 'url' => $url, 'message' => __('Your appointment in progress.')]);
        } else {            
            isset($pre_pay->attachment) ? delete_file($pre_pay->attachment) : '';
	        return response()->json(['status' => 'error', 'url' => $pre_pay->fail_url, 'message' => $payPalAppointmentPayment->message ?? __('The transaction has been failed.')]);
        }
    }

    public function getAppointmentPaymentStatus(Request $request, $slug)
    {
        $Session  = Cache::get($request->order_id);
        $this->customerConfig(getCompanyAllSetting($Session['business']['created_by'], $Session['business']['id']));
        $payment_status = $this->payPalPaymentStatus($request['token']);
        $verify = $this->statusThisAppointment($request, $slug, $payment_status->status);
        return redirect()->route('appointments.form', ['slug' => $slug, 'appointment' => $verify->appointment])->withFragment('appointment')->with($verify->status, $verify->message);
    }
}
