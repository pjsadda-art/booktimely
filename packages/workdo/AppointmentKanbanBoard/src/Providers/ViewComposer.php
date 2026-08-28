<?php

namespace Workdo\AppointmentKanbanBoard\Providers;
use Illuminate\Support\ServiceProvider;

class ViewComposer extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */

    public function boot()
    {
        view()->composer(['appointment.index','appointment.calendar'], function ($view) {
            if(module_is_active('AppointmentKanbanBoard')){
                $view->getFactory()->startPush('addButtonHook', view('appointment-kanban-board::appointment.addhook'));
            }
        });
    }

    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
