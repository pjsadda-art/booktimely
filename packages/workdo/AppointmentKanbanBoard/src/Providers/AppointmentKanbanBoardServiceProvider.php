<?php

namespace Workdo\AppointmentKanbanBoard\Providers;

use Illuminate\Support\ServiceProvider;
use Workdo\AppointmentKanbanBoard\Providers\EventServiceProvider;
use Workdo\AppointmentKanbanBoard\Providers\RouteServiceProvider;

class AppointmentKanbanBoardServiceProvider extends ServiceProvider
{

    protected $moduleName = 'AppointmentKanbanBoard';
    protected $moduleNameLower = 'appointmentkanbanboard';

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'appointment-kanban-board');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->registerTranslations();
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(__DIR__.'/../Resources/lang');
        }
    }
}