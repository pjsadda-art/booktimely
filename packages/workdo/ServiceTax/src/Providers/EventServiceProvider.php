<?php

namespace Workdo\ServiceTax\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanyMenuEvent;
use App\Events\CompanySettingEvent;
use App\Events\CreateService;
use App\Events\UpdateService;
use App\Events\CompanySettingMenuEvent;
use App\Events\AppointmentPaymentData;
use Workdo\ServiceTax\Listeners\CompanyMenuListener;
use Workdo\ServiceTax\Listeners\ServiceTaxStoreLis;
use Workdo\ServiceTax\Listeners\CompanySettingListener;
use Workdo\ServiceTax\Listeners\CompanySettingMenuListener;
use Workdo\ServiceTax\Listeners\StoreAppointmentPaymentData;


class EventServiceProvider extends Provider
{
    
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        CompanySettingEvent::class => [
            CompanySettingListener::class,
        ],
        CompanySettingMenuEvent::class => [
            CompanySettingMenuListener::class,
        ],
        CompanyMenuEvent::class => [
            CompanyMenuListener::class,
        ],
        CreateService::class => [
            ServiceTaxStoreLis::class
        ],
        UpdateService::class => [
            ServiceTaxStoreLis::class
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
