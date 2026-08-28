<?php

namespace Workdo\Warehouse\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        // $module = 'Warehouse';
        // $menu = $event->menu;
        // $menu->add([
        //     'title' => 'Warehouse',
        //     'icon' => 'ti ti-ticket custom-icon warehouse',
        //     'name' => 'warehouse',
        //     'parent' => null,
        //     'order' => 232,
        //     'ignore_if' => [],
        //     'depend_on' => [],
        //     'route' => 'warehouse.index',
        //     'module' => $module,
        //     'permission' => 'warehouse manage',
        //     'group' => 'codes & tickets'
        // ]);
    }
}
