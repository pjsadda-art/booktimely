<?php

namespace Workdo\Invoice\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Invoice';
        $menu = $event->menu;
        $menu->add([
            'title' => 'Invoice',
            'icon' => 'file-invoice',
            'name' => 'invoice',
            'parent' => null,
            'order' => 232,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'invoice.index',
            'module' => $module,
            'permission' => 'invoice manage',
            'group' => 'codes & tickets'
        ]);
        $menu->add([
            'title' => 'Invoice Pay Types',
            'icon' => 'cash',
            'name' => 'invoice-pay-type',
            'parent' => null,
            'order' => 233,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'invoice-pay-type.index',
            'module' => $module,
            'permission' => 'invoice pay type manage',
            'group' => 'codes & tickets'
        ]);
        // $menu->add([
        //     'title' => __('Vendor'),
        //     'icon' => '',
        //     'name' => 'vendor',
        //     'parent' => null,
        //     'order' => 233,
        //     'ignore_if' => [],
        //     'depend_on' => [],
        //     'route' => 'vendors.index',
        //     'module' => $module,
        //     'permission' => '',
        //     'group' => 'codes & tickets'
        // ]);
    }
}
