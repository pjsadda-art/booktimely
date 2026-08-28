<?php

namespace Workdo\CompoundService\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CreateService;
use App\Events\UpdateService;
use Workdo\CompoundService\Events\CreateCompoundBooking;
use Workdo\CompoundService\Listeners\CompoundServiceStoreLis;
use Workdo\CompoundService\Listeners\CreateComponentBookingLis;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        CreateService::class => [
            CompoundServiceStoreLis::class
        ],
        UpdateService::class => [
            CompoundServiceStoreLis::class
        ],
        CreateCompoundBooking::class => [
            CreateComponentBookingLis::class
        ]
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
