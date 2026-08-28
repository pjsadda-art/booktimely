<?php

namespace Workdo\ServiceTax\Listeners;

use App\Events\CompanySettingMenuEvent;

class CompanySettingMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanySettingMenuEvent $event): void
    {
        $module = 'ServiceTax';
        $menu = $event->menu;
        $menu->add([
            'title' => 'Service Tax',
            'name' => 'service-tax',
            'order' => 575,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'home',
            'navigation' => 'service-tax-sidenav',
            'module' => $module,
            'permission' => 'tax option setting manage'
        ]);
    }
}
