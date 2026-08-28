<?php

namespace Workdo\Api\Providers;

use Illuminate\Support\ServiceProvider;
use Workdo\Api\Providers\EventServiceProvider;
use Workdo\Api\Providers\RouteServiceProvider;

class ApiServiceProvider extends ServiceProvider
{

    protected $moduleName = 'Api';
    protected $moduleNameLower = 'api';

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'api');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->registerTranslations();
        $this->registerMiddleware('custom.jwt');
    }

    protected function registerMiddleware($alias)
    {
        $router = $this->app['router'];

        $router->aliasMiddleware($alias, \Workdo\Api\Http\Middleware\CustomJwtAuth::class);
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
