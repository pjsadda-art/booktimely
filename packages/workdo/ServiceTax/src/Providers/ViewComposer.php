<?php

namespace Workdo\ServiceTax\Providers;

use App\Models\Business;
use Workdo\ProductService\Entities\Tax;
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
            if(module_is_active('ServiceTax', $business->created_by)){
                $service_tax = Tax::where('created_by', creatorId())->where('business_id', $business->id)->pluck('name', 'id')->prepend(__('Select Service Tax'), '');

                $view->getFactory()->startPush('service_tax_create', view('service-tax::servicetax.service_tax_create', compact('service_tax')));
            }
        });

        view()->composer(['service.edit'], function ($view){
            
            $business = Business::find(getActiveBusiness());
            if(module_is_active('ServiceTax', $business->created_by)){
                $services = Tax::where('created_by', creatorId())->where('business_id', $business->id)->get();
                $service_tax = Tax::where('created_by', creatorId())->where('business_id', $business->id)->pluck('name', 'id')->prepend(__('Select Service Tax'), '');

                $service_data = $view['service'];

                $view->getFactory()->startPush('service_tax_edit', view('service-tax::servicetax.service_tax_edit', compact('services', 'service_data', 'service_tax')));
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
