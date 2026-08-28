<?php

namespace Workdo\CarService\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;

use App\Events\DefaultData;
use Workdo\CarService\Listeners\CarServiceDataLis;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        DefaultData::class => [
            CarServiceDataLis::class,
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
