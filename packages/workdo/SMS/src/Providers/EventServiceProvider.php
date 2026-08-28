<?php

namespace Workdo\SMS\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanySettingEvent;
use App\Events\CompanySettingMenuEvent;
use Workdo\SMS\Listeners\CompanySettingListener;
use Workdo\SMS\Listeners\CompanySettingMenuListener;
use Workdo\SMS\Listeners\AppointmentReminderLis;
use Workdo\SMS\Listeners\AppointmentStatusLis;
use Workdo\SMS\Listeners\CompleteToDoLis;
use Workdo\SMS\Listeners\CreateAppoinmentListener;
use Workdo\SMS\Listeners\CreatePublicTicketLis;
use Workdo\SMS\Listeners\CreateTicketLis;
use Workdo\SMS\Listeners\CreateToDoLis;
use Workdo\SMS\Listeners\CreateUserLisnter;
use App\Events\AppointmentReminder;
use App\Events\AppointmentStatus;
use App\Events\CreateAppoinment;
use App\Events\CreateUser;
use Workdo\SupportTicket\Events\CreatePublicTicket;
use Workdo\SupportTicket\Events\CreateTicket;
use Workdo\ToDo\Events\CompleteToDo;
use Workdo\ToDo\Events\CreateToDo;

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
        AppointmentReminder::class => [
            AppointmentReminderLis::class,
        ],
        AppointmentStatus::class => [
            AppointmentStatusLis::class,
        ],
        CreateAppoinment::class => [
            CreateAppoinmentListener::class,
        ],
        CreateUser::class => [
            CreateUserLisnter::class,
        ],
        CreateTicket::class => [
            CreateTicketLis::class
        ],
        CreatePublicTicket::class => [
            CreatePublicTicketLis::class
        ],
        CreateToDo::class => [
            CreateToDoLis::class
        ],
        CompleteToDo::class => [
            CompleteToDoLis::class
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
