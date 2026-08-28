<?php

namespace Workdo\LandingPage\Listeners;

use App\Events\SuperAdminMenuEvent;

class SuperAdminMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(SuperAdminMenuEvent $event): void
    {
        $module = 'LandingPage';
        $menu = $event->menu;
        $menu->add([
            'title' => __('CMS'),
            'icon' => 'package',
            'name' => 'landing-page',
            'parent' => null,
            'order' => 500,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'landingpage manage'
        ]);
        $menu->add([
            'title' => __('Landing Page'),
            'icon' => 'settings',
            'name' => '',
            'parent' => 'landing-page',
            'order' => 5,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'landingpage.index',
            'module' => $module,
            'permission' => 'landingpage manage'
        ]);
        $menu->add([
            'title' => __('Marketplace'),
            'icon' => 'settings',
            'name' => '',
            'parent' => 'landing-page',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'marketplace.index',
            'module' => $module,
            'permission' => 'marketplace manage'
        ]);
        $menu->add([
            'title' => __('Custom Pages'),
            'icon' => 'settings',
            'name' => '',
            'parent' => 'landing-page',
            'order' => 15,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'custom_page.index',
            'module' => $module,
            'permission' => 'custompage manage'
        ]);
    }
}
