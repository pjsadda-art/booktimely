<?php

namespace Workdo\SMS\Listeners;

use App\Events\CompanySettingMenuEvent;

class CompanySettingMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanySettingMenuEvent $event): void
    {
        $module = 'SMS';
        $menu = $event->menu;
        $menu->add([
            'title' => 'SMS',
            'name' => 'sms',
            'order' => 570,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'home',
            'navigation' => 'sms-sidenav',
            'module' => $module,
            'permission' => 'sms manage'
        ]);
    }
}
