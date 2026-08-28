<?php

namespace Workdo\Reports\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Reports';
        $menu = $event->menu;
        $menu->add([
            'title' => __('Reports'),
            'icon' => 'chart-bar',
            'name' => 'reports',
            'parent' => null,
            'order' => 290,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'report manage',
            'group' => 'contacts & reports'
        ]);
        $menu->add([
            'title' => __('Analytics Report'),
            'icon' => '',
            'name' => 'analytics',
            'parent' => 'reports',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'analytics.index',
            'module' => $module,
            'permission' => 'analytics manage',
            'group' => 'contacts & reports'
        ]);
        $menu->add([
            'title' => __('Appointment Status Report'),
            'icon' => '',
            'name' => 'appointmentstatusreport',
            'parent' => 'reports',
            'order' => 15,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'status-report.index',
            'module' => $module,
            'permission' => 'appointmentstatusreport manage',
            'group' => 'contacts & reports'
        ]);
        $menu->add([
            'title' => __('Customer Vs Guest Report'),
            'icon' => '',
            'name' => 'appointmentvsguestreport',
            'parent' => 'reports',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'customer-guest-report.index',
            'module' => $module,
            'permission' => 'customervsguestreport manage',
            'group' => 'contacts & reports'
        ]);
        $menu->add([
            'title' => __('Appointment Report'),
            'icon' => '',
            'name' => 'appointmentreport',
            'parent' => 'reports',
            'order' => 25,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'appointment-report.index',
            'module' => $module,
            'permission' => 'allappointmentreport manage',
            'group' => 'contacts & reports'
        ]);
        $menu->add([
            'title' => __('Service Appointment Report'),
            'icon' => '',
            'name' => 'serviceappointmentreport',
            'parent' => 'reports',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'service-appointment-report.index',
            'module' => $module,
            'permission' => 'serviceappointmentreport manage',
            'group' => 'contacts & reports'
        ]);
        $menu->add([
            'title' => __('Revenue Report'),
            'icon' => '',
            'name' => 'revenuereport',
            'parent' => 'reports',
            'order' => 35,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'revenue-report.index',
            'module' => $module,
            'permission' => 'revenuereport manage',
            'group' => 'contacts & reports'
        ]);
    }
}
