<?php

namespace App\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
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
            'route' => '',
            'module' => $module,
            'permission' => '',
            'group' => 'base'
        ]);
        $menu->add([
            'title' => __('Appointment Dashboard'),
            'icon' => '',
            'name' => 'appointment-dashboard',
            'parent' => 'dashboard',
            'order' => 5,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'appointment.dashboard',
            'module' => $module,
            'permission' => '',
            'group' => 'base'
        ]);
        $menu->add([
            'title' => __('Overview Dashboard'),
            'icon' => '',
            'name' => 'home',
            'parent' => 'dashboard',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'dashboard',
            'module' => $module,
            'permission' => '',
            'group' => 'base'
        ]);
        $menu->add([
            'title' => __('User Management'),
            'icon' => 'users',
            'name' => 'user-management',
            'parent' => null,
            'order' => 50,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'user manage',
            'group' => 'base'
        ]);
        $menu->add([
            'title' => __('User'),
            'icon' => '',
            'name' => 'user',
            'parent' => 'user-management',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'users.index',
            'module' => $module,
            'permission' => 'user manage',
            'group' => 'base'
        ]);
        $menu->add([
            'title' => __('Role'),
            'icon' => '',
            'name' => 'role',
            'parent' => 'user-management',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'roles.index',
            'module' => $module,
            'permission' => 'roles manage',
            'group' => 'base'
        ]);
        $menu->add([
            'title' => __('Business'),
            'icon' => 'credit-card',
            'name' => 'business',
            'parent' => null,
            'order' => 100,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'business manage',
            'group' => 'base'
        ]);
        $menu->add([
            'title' => __('Create Business'),
            'icon' => '',
            'name' => 'role',
            'parent' => 'business',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'business create',
            'group' => 'base'
        ]);
        $menu->add([
            'title' => __('Edit Business'),
            'icon' => '',
            'name' => 'role',
            'parent' => 'business',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'manage.business',
            'module' => $module,
            'permission' => 'business update',
            'group' => 'base'
        ]);
        $menu->add([
            'title' => __('Businesses'),
            'icon' => '',
            'name' => 'role',
            'parent' => 'business',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'business.index',
            'module' => $module,
            'permission' => 'business manage',
            'group' => 'base'
        ]);

        $menu->add([
            'title' => __('Customers'),
            'icon' => 'user',
            'name' => 'customers',
            'parent' => null,
            'order' => 150,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'customer.index',
            'module' => $module,
            'permission' => 'customer manage',
            'group' => 'base'
        ]);
        /* 'All Customers' and 'Duplicates' used to live here as a submenu.
           'Customers' now links straight to the list, and Duplicates moved to
           a button on that list's own page (grid and list views), next to the
           List/Grid View toggle — one menu link instead of a dropdown. */

        if(in_array('TeamBooking',$event->menu->modules))
        {
            $menu->add([
                'title' => __('Appointments'),
                'icon' => 'credit-card custom-icon appointments',
                'name' => 'appointments',
                'parent' => null,
                'order' => 170,
                'ignore_if' => [],
                'depend_on' => [],
                'route' => 'appointments.index',
                'module' => $module,
                'permission' => 'appointment manage',
                'group' => 'appointments'
            ]);
        }else{
            $menu->add([
                'title' => __('Appointments'),
                'icon' => 'credit-card custom-icon appointments',
                'name' => 'appointments',
                'parent' => null,
                'order' => 170,
                'ignore_if' => [],
                'depend_on' => [],
                'route' => 'appointment.index',
                'module' => $module,
                'permission' => 'appointment manage',
                'group' => 'appointments'
            ]);
        }
        $menu->add([
            'title' => __('Booking Calendar'),
            'icon' => 'calendar-time custom-icon calender',
            'name' => 'bookings-v2-calendar',
            'parent' => null,
            'order' => 200,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'bookings-v2.calendar',
            'module' => $module,
            'permission' => 'appointment manage',
            'group' => 'appointments'
        ]);
        $menu->add([
            'title' => __('Staff Roster'),
            // 'calendar-week' isn't a real Tabler Icons glyph in this app's
            // bundled font (public/assets/fonts/tabler-icons.min.css) — it
            // rendered blank. 'calendar-stats' is, and reads as a schedule/grid.
            'icon' => 'calendar-stats custom-icon calender',
            'name' => 'staff-roster',
            'parent' => null,
            'order' => 206,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'staff-roster.index',
            'module' => $module,
            'permission' => 'staff roster manage',
            'group' => 'appointments'
        ]);
        $menu->add([
            'title' => __('Roster Conflicts'),
            'icon' => 'alert-triangle custom-icon calender',
            'name' => 'staff-roster-conflicts',
            'parent' => null,
            'order' => 207,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'staff-roster.conflicts',
            'module' => $module,
            'permission' => 'staff roster manage',
            'group' => 'appointments'
        ]);
        $menu->add([
            'title' => __('Custom Status'),
            'icon' => 'tag',
            'name' => 'custom-status',
            'parent' => null,
            'order' => 210,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'custom-status.index',
            'module' => $module,
            'permission' => 'status manage',
            'group' => 'appointments'
        ]);
        // Inventory V2 — independent of the customer and deposit modules
        $menu->add([
            'title' => __('Inventory'),
            'icon' => 'box',
            'name' => 'inventory',
            'parent' => null,
            'order' => 250,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'inventory manage',
            'group' => 'inventory'
        ]);
        foreach (
            [
                ['Products', 'inventory-products', 10, 'inventory.products.index', []],
                ['Stock', 'inventory-stock', 20, 'inventory.stock.index', []],
                ['Purchases', 'inventory-purchases', 30, 'inventory.purchases.index', []],
                ['Internal Use', 'inventory-internal-use', 40, 'inventory.internal-use.index', []],
                ['Stock Summary', 'inventory-report-summary', 50, 'inventory.reports.summary', []],
                ['Stock Ledger', 'inventory-report-ledger', 60, 'inventory.reports.ledger', []],
                ['Valuation', 'inventory-report-valuation', 70, 'inventory.reports.valuation', []],
                ['Warehouses', 'inventory-warehouses', 80, 'inventory.reference.index', ['type' => 'warehouses']],
                ['Vendors', 'inventory-vendors', 90, 'inventory.reference.index', ['type' => 'vendors']],
                ['Categories', 'inventory-categories', 100, 'inventory.reference.index', ['type' => 'categories']],
                ['Sub-categories', 'inventory-sub-categories', 110, 'inventory.reference.index', ['type' => 'sub-categories']],
                ['Brands', 'inventory-brands', 120, 'inventory.reference.index', ['type' => 'brands']],
                ['Sub-brands', 'inventory-sub-brands', 130, 'inventory.reference.index', ['type' => 'sub-brands']],
                ['Units', 'inventory-units', 140, 'inventory.reference.index', ['type' => 'units']],
            ] as [$title, $name, $order, $route, $parameters]
        ) {
            $menu->add([
                'title' => __($title),
                'icon' => '',
                'name' => $name,
                'parent' => 'inventory',
                'order' => $order,
                'ignore_if' => [],
                'depend_on' => [],
                'route' => $route,
                'params' => $parameters,
                'module' => $module,
                'permission' => 'inventory manage',
                'group' => 'inventory'
            ]);
        }

        $menu->add([
            'title' => __('Contacts'),
            'icon' => 'phone',
            'name' => 'contacts',
            'parent' => null,
            'order' => 270,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'contacts.index',
            'module' => $module,
            'permission' => 'contact manage',
            'group' => 'contacts & reports'
        ]);
        $menu->add([
            'title' => __('Subscribers'),
            'icon' => 'mail',
            'name' => 'subscribers',
            'parent' => null,
            'order' => 280,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'subscribes.index',
            'module' => $module,
            'permission' => 'subscriber manage',
            'group' => 'contacts & reports'
        ]);
        $menu->add([
            'title' => __('Email Template'),
            'icon' => 'template',
            'name' => 'email-templates',
            'parent' => null,
            'order' => 1900,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'email-templates.index',
            'module' => $module,
            'permission' => '',
            'group' => 'others'
        ]);
        $menu->add([
            'title' => __('Settings'),
            'icon' => 'settings',
            'name' => 'settings',
            'parent' => null,
            'order' => 2000,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'setting manage',
            'group' => 'others'
        ]);
        $menu->add([
            'title' => __('System Settings'),
            'icon' => '',
            'name' => 'system-settings',
            'parent' => 'settings',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'settings.index',
            'module' => $module,
            'permission' => 'setting manage',
            'group' => 'others'
        ]);
        $menu->add([
            'title' => __('Setup Subscription Plan'),
            'icon' => '',
            'name' => 'setup-subscription-plan',
            'parent' => 'settings',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'plans.index',
            'module' => $module,
            'permission' => 'plan manage',
            'group' => 'others'
        ]);
        $menu->add([
            'title' => __('Order'),
            'icon' => '',
            'name' => 'order',
            'parent' => 'settings',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'plan.order.index',
            'module' => $module,
            'permission' => 'plan orders',
            'group' => 'others'
        ]);
    }
}
