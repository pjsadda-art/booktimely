<?php

namespace Workdo\Api\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Api';
        $menu = $event->menu;
        $menu->add([
            'title' => __('API'),
            'icon' => 'vector-triangle custom-icon api',
            'name' => 'api',
            'parent' => null,
            'order' => 295,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'api.index',
            'module' => $module,
            'permission' => 'api manage',
            'group' => 'others'
        ]);
    }
}
