<?php

namespace App\Listeners;

use App\Events\CompanySettingMenuEvent;

class CompanySettingMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanySettingMenuEvent $event): void
    {
        $module = 'Base';
        $menu = $event->menu;
        $menu->add([
            'title' => __('Brand Settings'),
            'name' => 'brand-settings',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'site-settings',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'title' => __('System Settings'),
            'name' => 'system-settings',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'system-settings',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'title' => __('Currency Settings'),
            'name' => 'currency-settings',
            'order' => 25,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'currency-settings',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'title' => __('Appointment Deposits'),
            'name' => 'deposit-settings',
            'order' => 28,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'deposit-settings',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'title' => __('WhatsApp Chat'),
            'name' => 'whatsapp-settings',
            'order' => 29,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'whatsapp-settings',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'title' => __('Week Start At'),
            'name' => 'week-settings',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'week-settings',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'title' => __('Booking Mode Settings'),
            'name' => 'booking-mode-settings',
            'order' => 170,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'booking-mode-sidenav',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'title' => __('Embedded Code'),
            'name' => 'embedded-code',
            'order' => 190,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'embedded-code-sidenav',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'title' => __('Custom JS'),
            'name' => 'custom-js',
            'order' => 200,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'custom-js-sidenav',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'title' => __('Custom CSS'),
            'name' => 'custom-css',
            'order' => 210,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'custom-css-sidenav',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'title' => __('Default Appointment Status'),
            'name' => 'default-appointment-status',
            'order' => 220,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'default-appointment-status-sidenav',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'title' => __('Email Settings'),
            'name' => 'email-settings',
            'order' => 500,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'email-sidenav',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'title' => __('Email Notification Settings'),
            'name' => 'email-notification-settings',
            'order' => 510,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'email-notification-sidenav',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'title' => __('Bank Transfer'),
            'name' => 'bank-transfer-settings',
            'order' => 1000,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'bank-transfer-sidenav',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
    }
}
