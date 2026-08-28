<?php

namespace Workdo\Stripe\Providers;

use App\Models\Business;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use App\Facades\ModuleFacade as Module;

class ViewComposer extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */

    public function boot()
    {
        view()->composer(['plans.planpayment', 'plans.marketplace'], function ($view) {
            if (Auth::check() && Module::isEnabled('Stripe')) {
                $admin_settings = getAdminAllSetting();
                if ((isset($admin_settings['stripe_is_on']) ? $admin_settings['stripe_is_on'] : 'off') == 'on' && !empty($admin_settings['stripe_key']) && !empty($admin_settings['stripe_secret'])) {
                    $view->getFactory()->startPush('company_plan_payment', view('stripe::payment.plan_payment'));
                }
            }
        });

        view()->composer(['web_layouts.appointment-form', 'form_layout.*.index'], function ($view) {
            $slug = \Request::segment(2);
            if (!$slug) {
                $slug = frontend_bussiness_slug();
            }
            $business = Business::where('slug', $slug)->first();
            $settings = getCompanyAllSetting($business->created_by, $business->id);
            if (module_is_active('Stripe', $business->created_by) && (isset($settings['stripe_is_on']) ? $settings['stripe_is_on'] : 'off') == 'on' && !empty($settings['stripe_key']) && !empty($settings['stripe_secret'])) {
                $view->getFactory()->startPush('appointment_payment', view('stripe::payment.appointment'));
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
