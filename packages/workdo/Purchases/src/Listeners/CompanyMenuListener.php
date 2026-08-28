<?php

namespace Workdo\Purchases\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        // $module = 'Purchases';
        // $menu = $event->menu;
        // $menu->add([
        //     'title' => 'Purchases',
        //     'icon' => 'ti ti-ticket custom-icon purchases',
        //     'name' => 'purchases',
        //     'parent' => null,
        //     'order' => 232,
        //     'ignore_if' => [],
        //     'depend_on' => [],
        //     'route' => 'purchases.index',
        //     'module' => $module,
        //     'permission' => 'purchases manage',
        //     'group' => 'codes & tickets'
        // ]);
    }
}
