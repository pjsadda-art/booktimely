<?php

namespace Workdo\GoogleCaptcha\Listeners;
use App\Events\SuperAdminSettingMenuEvent;

class SuperAdminSettingMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(SuperAdminSettingMenuEvent $event): void
    {
        $module = 'GoogleCaptcha';
        $menu = $event->menu;
        $menu->add([
            'title' => 'GoogleCaptcha',
            'name' => 'googlecaptcha',
            'order' => 600,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'recaptcha-sidenav',
            'module' => $module,
            'permission' => 'recaptcha manage'
        ]);
    }
}
