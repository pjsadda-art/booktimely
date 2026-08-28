<?php

namespace Workdo\PromoCodes\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\AdditionalServices\Events\AdditionalServicePromo;
use Workdo\BulkAppointments\Events\CalculatePromo;
use Workdo\EasyDepositPayments\Events\CalculateDeposit;
use Workdo\FlexibleHours\Events\CalculateFlexibleHours;
use Workdo\PromoCodes\Events\CalculateDiscount;
use Workdo\PromoCodes\Events\CalculatePromoCode;
use Workdo\PromoCodes\Events\CalculateTax;
use Workdo\RepeatAppointments\Events\CalculateRepeatappointmentPromoCode;
use Workdo\SequentialAppointment\Events\SequentialPromoCodeCal;
use Workdo\ShoppingCart\Events\CalculateCart;
use Workdo\TeamBooking\Events\PromoCodeCalculate;

class CalculatePromoCodeLis
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
    public function handle(CalculatePromoCode $event)
    {
        $service = $event->service;
        $promocode = $event->promocode;
        $request = $event->request;

        if(isset($request['all_service_price']) && $request['all_service_price'] != null){
            $service_price = $request['all_service_price'];
        } else {
            $service_price = $service->price;
        }
        if ($promocode->discount_type == 1) {
            $discount = $service_price * $promocode->discount_percentage / 100;
        } else {
            $discount = $promocode->flat_rate;
        }
        $final_amount = $service_price - $discount;

        if (module_is_active('ServiceTax', $service->created_by)) {
            $data = event(new CalculateTax($service, $final_amount));
        }

        if (module_is_active('Discount', $service->created_by)){
            $promo_discount = event(new CalculateDiscount($service, $promocode, $request));
            $promo_discounts = $promo_discount[0];
        }

        if (module_is_active('ShoppingCart', $service->created_by)){
            $cart = event(new CalculateCart($service, $promocode, $request));
            $cart_discount = $cart[0];
        }

        if (module_is_active('EasyDepositPayments', $service->created_by)){
            $deposit = event(new CalculateDeposit($service, $promocode, $request));
            $deposit_discount = $deposit[0];
        }
        if (module_is_active('BulkAppointments', $service->created_by)){
            $bulk = event(new CalculatePromo($service, $promocode, $event->request));
            $bulk_discount = $bulk[0];
        }
        if (module_is_active('FlexibleHours', $service->created_by) && isset($request['flexible_id']) && $request['flexible_id'] !== 'undefined'){
            $flexible = event(new CalculateFlexibleHours($service, $promocode, $event->request));
            $flexible_price = $flexible[0];
        }

        if (module_is_active('AdditionalServices', $service->created_by) && $request['additional_total_price'] !== 'undefined' && $request['additional_total_price'] > 0){
            $additional = event(new AdditionalServicePromo($service, $promocode, $event->request));
            $additional_service = $additional[0];
        }

        if (module_is_active('TeamBooking', $service->created_by)){
            $team = event(new PromoCodeCalculate($service, $promocode, $event->request));
            $team_discount = $team[0];
        }
        if (module_is_active('SequentialAppointment', $service->created_by) && $request['sequentialServices'] != null){
            $sequential_service = event(new SequentialPromoCodeCal($service, $promocode, $event->request));
            $sequential_services = $sequential_service[0];
        }

        if (module_is_active('RepeatAppointments', $service->created_by) && $request['selectedDates'] != null && $request['bookedSlots'] != null){
            $repeatAppointmentData = event(new CalculateRepeatappointmentPromoCode($service, $promocode, $event->request));
            $repeatAppointment = $repeatAppointmentData[0];
        }

        $promoArray = [
            'promo_code_id' => $promocode->id,
            'final_amount' => $final_amount,
            'after_promo_tax' => isset($data) ? $data : '',
            'apply_discount' => $discount,
            'after_discount_promo' => isset($promo_discount) ? $promo_discounts : '',
            'cart_discount' => isset($cart_discount) ? $cart_discount : '',
            'deposit_discount' => isset($deposit_discount) ? $deposit_discount : '',
            'flexible_price' => isset($flexible_price) ? $flexible_price : '',
            'additional_service' => isset($additional_service) ? $additional_service : '',
            'bulk_discount' => isset($bulk_discount) ? $bulk_discount : '',
            'team_discount' => isset($team_discount) ? $team_discount : '',
            'sequential_services' => isset($sequential_services) ? $sequential_services : '',
            'repeat_appointment' => isset($repeatAppointment) ? $repeatAppointment : '',
        ];

        return $promoArray;
    }
}
