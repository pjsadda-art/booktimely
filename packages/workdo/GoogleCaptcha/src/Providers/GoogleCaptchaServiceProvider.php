<?php

namespace Workdo\GoogleCaptcha\Providers;

use Illuminate\Support\ServiceProvider;
use Workdo\GoogleCaptcha\Providers\EventServiceProvider;
use Workdo\GoogleCaptcha\Providers\RouteServiceProvider;

class GoogleCaptchaServiceProvider extends ServiceProvider
{

    protected $moduleName = 'GoogleCaptcha';
    protected $moduleNameLower = 'googlecaptcha';

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'google-captcha');
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