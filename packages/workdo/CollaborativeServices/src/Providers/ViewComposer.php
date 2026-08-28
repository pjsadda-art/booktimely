<?php

namespace Workdo\CollaborativeServices\Providers;

use App\Models\Business;
use App\Models\Service;
use Illuminate\Support\Facades\Request;
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
            if(module_is_active('CollaborativeServices', $business->created_by)){
                $services = Service::where('created_by', creatorId())->where('business_id', $business->id)->where('service_type', null)->get();

                $view->getFactory()->startPush('collaborative_service_create', view('collaborative-services::service.collaborative_services_create', compact('services')));
            }
        });

        view()->composer(['service.edit'], function ($view){
            
            $business = Business::find(getActiveBusiness());
            if(module_is_active('CollaborativeServices', $business->created_by)){
                $services = Service::where('created_by', creatorId())->where('business_id', $business->id)->where('service_type', null)->get();

                $service_data = $view['service'];

                $view->getFactory()->startPush('collaborative_service_edit', view('collaborative-services::service.collaborative_services_edit', compact('services', 'service_data')));
            }
        });

        view()->composer(['web_layouts.appointment-form', 'form_layout.*.index'], function ($view)
        {
            $request = Request::instance();
            $slug = $request->segment(2);
            if(!$slug)
            {
                $slug = frontend_bussiness_slug();
            }
            $business = Business::where('slug', $slug)->first();
            $view->getFactory()->startPush('collaborative_services', view('collaborative-services::service.collaborative_services', compact('business')));
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
