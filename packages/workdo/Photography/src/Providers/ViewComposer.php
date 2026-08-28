<?php

namespace Workdo\Photography\Providers;

use Illuminate\Support\ServiceProvider;

class ViewComposer extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */

    public function boot(){
        view()->composer(['business.create'], function ($view)
        {
            if(\Auth::check())
            {
                if(module_is_active('Photography'))
                {
                    $view->getFactory()->startPush('theme_card', view('photography::theme.card',['btn'=>false]));
                }
            }
        });


        view()->composer(['business.manage'], function ($view)
        {
            if (\Auth::check()) {
                $businessId = \Request::segment(2);
                if(module_is_active('Photography'))
                {
                    $view->getFactory()->startPush('theme_card', view('photography::theme.card',['btn' => true, 'businessId' => $businessId]));
                }
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
