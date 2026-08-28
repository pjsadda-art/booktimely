<?php

namespace Workdo\PromoCodes\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanyMenuEvent;
use App\Events\AppointmentPaymentData;
use Workdo\PromoCodes\Events\CalculateDiscount;
use Workdo\PromoCodes\Events\CalculatePromoCode;
use Workdo\PromoCodes\Listeners\CalculateDiscountLis;
use Workdo\PromoCodes\Listeners\CalculatePromoCodeLis;
use Workdo\PromoCodes\Listeners\StoreAppointmentPaymentData;
use Workdo\PromoCodes\Listeners\CompanyMenuListener;


class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        CompanyMenuEvent::class => [
            CompanyMenuListener::class,
        ],
        CalculateDiscount::class => [
            CalculateDiscountLis::class
        ],
        CalculatePromoCode::class => [
            CalculatePromoCodeLis::class,
        ],
        AppointmentPaymentData::class => [
            StoreAppointmentPaymentData::class
        ],

    ];

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
}
