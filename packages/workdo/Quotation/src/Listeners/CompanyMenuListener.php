<?php

namespace Workdo\Quotation\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Quotation';
        $menu = $event->menu;
        $menu->add([
            'title' => 'Quotation',
            'icon' => 'ti ti-ticket custom-icon quotation',
            'name' => 'quotation',
            'parent' => null,
            'order' => 232,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'proposal.index',
            'module' => $module,
            'permission' => '',
            'group' => 'codes & tickets'
        ]);
    }
}
