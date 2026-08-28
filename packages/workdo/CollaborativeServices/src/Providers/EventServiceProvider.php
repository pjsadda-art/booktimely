<?php

namespace Workdo\CollaborativeServices\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CreateService;
use App\Events\UpdateService;
use Workdo\CollaborativeServices\Listeners\CollaborativeServiceStoreLis;
use Workdo\CollaborativeServices\Events\StoreCollaborativeServices;
use Workdo\CollaborativeServices\Listeners\StoreCollaborativeServices as ListenersStoreCollaborativeServices;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        CreateService::class => [
            CollaborativeServiceStoreLis::class
        ],
        UpdateService::class => [
            CollaborativeServiceStoreLis::class
        ],
        StoreCollaborativeServices::class => [
            ListenersStoreCollaborativeServices::class
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
