<?php

namespace Workdo\PromoCodes\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'PromoCodes';
        $menu = $event->menu;
        $menu->add([
            'title' => 'Promo Codes',
            'icon' => 'ti ti-ticket custom-icon promo-codes',
            'name' => 'promocodes',
            'parent' => null,
            'order' => 230,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'promocode.index',
            'module' => $module,
            'permission' => 'promocode manage',
            'group' => 'codes & tickets'
        ]);
    }
}
