<?php

namespace Workdo\AppointmentKanbanBoard\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'AppointmentKanbanBoard';
        $menu = $event->menu;
        $menu->add([
            'title' => __('Appointment Board'),
            'icon' => 'layout-kanban',
            'name' => 'appointmentkanbanboard',
            'parent' => null,
            'order' => 205,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'Appointment-Kanban-Board.index',
            'module' => $module,
            'permission' => 'appointment kanban board manage',
            'group' => 'appointments'
        ]);
    }
}
