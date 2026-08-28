<?php

namespace App\Listeners;

use App\Events\SuperAdminMenuEvent;

class SuperAdminMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(SuperAdminMenuEvent $event): void
    {
        $module = 'Base';
        $menu = $event->menu;
        $menu->add([
            'title' => __('Dashboard'),
            'icon' => 'home',
            'name' => 'dashboard',
            'parent' => null,
            'order' => 1,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'home',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'title' => __('Subscribers'),
            'icon' => 'users',
            'name' => 'subscribers',
            'parent' => null,
            'order' => 50,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'users.index',
            'module' => $module,
            'permission' => 'user manage'
        ]);
        $menu->add([
            'title' => __('Subscription'),
            'icon' => 'trophy',
            'name' => 'subscription',
            'parent' => null,
            'order' => 100,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => ''
        ]);
        $menu->add([
            'title' => __('Subscription Setting'),
            'icon' => '',
            'name' => 'subscription-setting',
            'parent' => 'subscription',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'plan.list',
            'module' => $module,
            'permission' => 'plan manage'
        ]);
        $menu->add([
            'title' => __('Coupon'),
            'icon' => '',
            'name' => 'coupon',
            'parent' => 'subscription',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'coupons.index',
            'module' => $module,
            'permission' => 'coupon manage'
        ]);
        $menu->add([
            'title' => __('Order'),
            'icon' => '',
            'name' => 'order',
            'parent' => 'subscription',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'plan.order.index',
            'module' => $module,
            'permission' => 'plan orders'
        ]);
        $menu->add([
            'title' => __('Bank Transfer Request'),
            'icon' => '',
            'name' => 'bank-transfer',
            'parent' => 'subscription',
            'order' => 40,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'bank-transfer-request.index',
            'module' => $module,
            'permission' => 'plan orders'
        ]);
        $menu->add([
            'title' => __('Email Template'),
            'icon' => 'template',
            'name' => 'email-templates',
            'parent' => null,
            'order' => 150,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'email-templates.index',
            'module' => $module,
            'permission' => 'email template manage'
        ]);
        $menu->add([
            'title' => __('Notification Template'),
            'icon' => 'notification',
            'name' => 'system-setup',
            'parent' => null,
            'order' => 160,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'notification-template.index',
            'module' => $module,
            'permission' => 'notification template manage'
        ]);
        $menu->add([
            'title' => __('Settings'),
            'icon' => 'settings',
            'name' => 'settings',
            'parent' => null,
            'order' => 1000,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'settings.index',
            'module' => $module,
            'permission' => 'setting manage'
        ]);
        $menu->add([
            'title' => __('Add-on Manager'),
            'icon' => 'layout-2',
            'name' => 'add-on-manager',
            'parent' => null,
            'order' => 1100,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'module.index',
            'module' => $module,
            'permission' => 'module manage'
        ]);
    }
}
