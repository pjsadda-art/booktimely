<?php

namespace Workdo\PromoCodes\Providers;

use App\Models\Business;
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
        view()->composer(['web_layouts.appointment-form', 'form_layout.*.index'], function ($view)
        {
            $request = Request::instance();
            $slug = $request->segment(2);
            if(!$slug)
            {
                $slug = frontend_bussiness_slug();
            }
            $business = Business::where('slug', $slug)->first();

            if(module_is_active('PromoCodes', $business->created_by)){
                $view->getFactory()->startPush('apply_coupon', view('promo-codes::promocode.promo_code_apply', compact('business')));
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
