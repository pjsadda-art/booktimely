<?php

namespace Workdo\GoogleCaptcha\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanyMenuEvent;
use App\Events\CompanySettingEvent;
use App\Events\CompanySettingMenuEvent;
use App\Events\SuperAdminSettingMenuEvent;
use Workdo\GoogleCaptcha\Listeners\CompanyMenuListener;
use Workdo\GoogleCaptcha\Listeners\CompanySettingListener;
use Workdo\GoogleCaptcha\Listeners\CompanySettingMenuListener;
use Workdo\GoogleCaptcha\Listeners\SuperAdminSettingMenuListener;
use Workdo\GoogleCaptcha\Listeners\VerifyReCaptchaTokenLis;
use Workdo\GoogleCaptcha\Events\VerifyReCaptchaToken;
use Workdo\GoogleCaptcha\Listeners\SuperAdminSettingListener;
use App\Events\SuperAdminSettingEvent;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        SuperAdminSettingMenuEvent::class => [
            SuperAdminSettingMenuListener::class,
        ],
        SuperAdminSettingEvent::class => [
            SuperAdminSettingListener::class,
        ],
        VerifyReCaptchaToken::class => [
            VerifyReCaptchaTokenLis::class,
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
