<?php

namespace Workdo\PromoCodes\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CalculateDiscountLis
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
        $promocode = $event->promocode;
        $request = $event->request;

        if(module_is_active('Discount', $service->created_by)){
            $service_price = isset($request['service_price']) ? $request['service_price'] : $service->price;

            if ($promocode->discount_type == 1) {
                $discount = $service_price * $promocode->discount_percentage / 100;
            } else {
                $discount = $promocode->flat_rate;
            }

            $final_amount = $service_price - $discount;
            $service_discount = $service->price - $service_price;
            $promocode->save();

            $discountArray = [
                'total_price' => $service->price,
                'service_after_promo' => $final_amount,
                'promo_discount' => $discount,
                'service_discount' => $service_discount,
            ];

            return $discountArray;
        }
    }
}
