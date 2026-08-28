<?php

namespace Workdo\CompoundService\Providers;

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
            if(module_is_active('CompoundService', $business->created_by)){
                $services = Service::where('created_by', creatorId())->where('business_id', $business->id)->get();

                $view->getFactory()->startPush('compound_service_create', view('compound-service::service.compound_service', compact('services')));
            }
        });

        view()->composer(['service.edit'], function ($view){
            
            $business = Business::find(getActiveBusiness());
            if(module_is_active('CompoundService', $business->created_by)){
                $services = Service::where('created_by', creatorId())->where('business_id', $business->id)->get();

                $service_data = $view['service'];

                $view->getFactory()->startPush('compound_service_edit', view('compound-service::service.compound_service_edit', compact('services', 'service_data')));
            }
        });

        view()->composer(['service.create'], function ($view){
            $business = Business::find(getActiveBusiness());
            if(module_is_active('CompoundService', $business->created_by) && module_is_active('CollaborativeServices', $business->created_by)){
                $services = Service::where('created_by', creatorId())->where('business_id', $business->id)->get();

                $view->getFactory()->startPush('compound_and_collaborative_service_create', view('compound-service::compound_collaborative.create', compact('services')));
            }
        });

        view()->composer(['service.edit'], function ($view){
            
            $business = Business::find(getActiveBusiness());
            if(module_is_active('CompoundService', $business->created_by) && module_is_active('CollaborativeServices', $business->created_by)){
                $services = Service::where('created_by', creatorId())->where('business_id', $business->id)->get();

                $service_data = $view['service'];
                $view->getFactory()->startPush('compound_and_collaborative_service_edit', view('compound-service::compound_collaborative.edit', compact('services', 'service_data')));
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
