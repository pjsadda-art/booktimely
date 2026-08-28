<?php

namespace Workdo\ServiceTax\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'ServiceTax';
        $menu = $event->menu;
        $menu->add([
            'title' => 'Service Tax',
            'icon' => 'ti ti-ticket custom-icon service-tax',
            'name' => 'servicetax',
            'parent' => null,
            'order' => 232,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'servicetax.index',
            'module' => $module,
            'permission' => 'servicetax manage',
            'group' => 'codes & tickets'
        ]);
    }
}
