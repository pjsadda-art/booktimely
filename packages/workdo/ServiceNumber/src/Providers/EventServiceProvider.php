<?php

namespace Workdo\ServiceNumber\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CreateService;
use App\Events\UpdateService;
use Workdo\ServiceNumber\Listeners\ServiceNumberStoreLis;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        CreateService::class => [
            ServiceNumberStoreLis::class
        ],
        UpdateService::class => [
            ServiceNumberStoreLis::class
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
