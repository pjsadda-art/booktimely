<?php

namespace Workdo\ServiceNumber\Providers;

use App\Models\Business;
use App\Models\Service;
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
        view()->composer(['service.create'], function ($view){
            $business = Business::find(getActiveBusiness());
            if(module_is_active('ServiceNumber', $business->created_by)){
                $services = Service::where('created_by', creatorId())->where('business_id', $business->id)->get();

                $view->getFactory()->startPush('service_number_create', view('service-number::service.create', compact('services')));
            }
        });

        view()->composer(['service.edit'], function ($view){
            
            $business = Business::find(getActiveBusiness());
            if(module_is_active('ServiceNumber', $business->created_by)){
                $services = Service::where('created_by', creatorId())->where('business_id', $business->id)->get();

                $service_data = $view['service'];

                $view->getFactory()->startPush('service_number_edit', view('service-number::service.edit', compact('services', 'service_data')));
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
