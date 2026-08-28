<?php

namespace Workdo\Paypal\Providers;

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
            if (Auth::check() && Module::isEnabled('Paypal')) {
                $admin_settings = getAdminAllSetting();
                if (isset($admin_settings['paypal_payment_is_on']) && $admin_settings['paypal_payment_is_on'] == 'on' && !empty($admin_settings['company_paypal_client_id']) && !empty($admin_settings['company_paypal_secret_key'])) {
                    $view->getFactory()->startPush('company_plan_payment', view('paypal::payment.plan_payment'));
                }
            }
        });

        view()->composer(['web_layouts.appointment-form', 'form_layout.*.index'], function ($view) {
            $slug = \Request::segment(2);
            if (!$slug) {
                $slug = frontend_bussiness_slug();
            }
            $business = Business::where('slug', $slug)->first();
            $admin_settings = getCompanyAllSetting($business->created_by, $business->id);
            if (module_is_active('Paypal', $business->created_by) && isset($admin_settings['paypal_payment_is_on']) && $admin_settings['paypal_payment_is_on'] == 'on' && !empty($admin_settings['company_paypal_client_id']) && !empty($admin_settings['company_paypal_secret_key'])) {
                $view->getFactory()->startPush('appointment_payment', view('paypal::payment.appointment'));
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
