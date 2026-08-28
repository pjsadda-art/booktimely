<?php

namespace Workdo\ServiceTax\Listeners;

use App\Models\AppointmentPayment;
use Workdo\ServiceTax\Entities\ServiceTax;
use App\Models\Setting;

class StoreAppointmentPaymentData
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
       
        $service = $event->service;

        if (module_is_active('ServiceTax', $service->created_by)) {
            $appointment_payment = $event->appointment_payment;
            $amount = $appointment_payment->amount;

            if ($service->service_tax) {
                    $service_tax = ServiceTax::find($service->service_tax);
                    $amount = ($service_tax->rate / 100) * (float)$amount;
            }

            $payment = AppointmentPayment::find($appointment_payment->id);
            $setting = Setting::where('created_by', $service->created_by)->where('key','service_tax_option')->first();
            if ($payment) {
                $payment->update([
                    'tax_amount' => $amount,
                    'final_amount' => $setting->value == 'exclusive' ? $appointment_payment->amount + $amount : $appointment_payment->amount,
                    'tax_type' => $setting->value,
                ]);
            }
        }
    }
}
