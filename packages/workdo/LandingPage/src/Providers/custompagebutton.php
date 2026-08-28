<?php

namespace Workdo\LandingPage\Providers;

use Illuminate\Support\Facades\Route;
// use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\ServiceProvider;
use \Workdo\LandingPage\Entities\LandingPageSetting;
use Illuminate\Support\Facades\Schema;

class custompagebutton extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public $settings;


    public function boot()
    {
        view()->composer(['auth.*'], function ($view) {
            if (Schema::hasTable('landing_page_settings')) {
                $settings = \Workdo\LandingPage\Entities\LandingPageSetting::settings();
                $view->getFactory()->startPush('authcustombutton', view('landing-page::layouts.buttons', compact('settings')));
            }
        });
    }
}
