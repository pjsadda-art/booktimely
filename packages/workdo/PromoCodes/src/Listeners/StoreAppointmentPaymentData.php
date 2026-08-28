<?php

namespace Workdo\PromoCodes\Listeners;

use App\Models\AppointmentPayment;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Session;
use Workdo\PromoCodes\Entities\PromoCode;

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
        $request = $event->request;

        if (module_is_active('PromoCodes', $service->created_by) && ($request['promocode'] != null || $request['promo_code_id'] != null)) {
            $appointment_payment = $event->appointment_payment;

            if ($request['promo_code_id'] != null) {
                $promo_code = $request['promo_code_id'];
                $promoCode = PromoCode::find($promo_code);
            } elseif ($request['promocode']) {
                $promoCode = PromoCode::where('code', $request['promocode'])->first();
            }

            if (module_is_active('Discount', $service->created_by)) {
                $service_amount = $request['final_amount'];

            } else if(module_is_active('AdditionalServices', $service->created_by)){
                $service_amount = $request['additional_service_price'];

            } else if(module_is_active('FlexibleHours', $service->created_by)){
                $service_amount = $request['service_price'] ?? $request['flexible_hours'];

            }else {
                $service_amount = $service->price;
            }

            $payment = AppointmentPayment::find($appointment_payment->id);

            if ((isset($request['selectedCartIds']) && $request['selectedCartIds'] == 0) || !module_is_active('ShoppingCart', $service->created_by)) {

                if ($promoCode->discount_type == 1) {
                    $amount = $service_amount * $promoCode->discount_percentage / 100;
                } else {
                    $amount = $promoCode->flat_rate;
                }

                $after_promo_price = $service_amount - $amount;
                if (!Session::has('promo_code_used')) {
                    $promoCode->promo_used = $promoCode->promo_used + 1;
                    $promoCode->update();
                    Session::put('promo_code_used', true);
                }
                $payment->update([
                    'coupon_amount' => $amount,
                    'final_amount' => $after_promo_price,
                    'promo_code_id' => $promoCode->id,
                ]);
            }
        }
    }
}
