<?php

namespace Workdo\ServiceTax\Listeners;
use App\Events\CompanySettingEvent;

class CompanySettingListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanySettingEvent $event): void
    {
        //echo "in listener";die;
        $module = 'ServiceTax';
        $methodName = 'index';
        $controllerClass = "Workdo\\ServiceTax\\Http\\Controllers\\Company\\SettingsController";
       // echo $controllerClass;die;
        if (class_exists($controllerClass)) {
            $controller = \App::make($controllerClass);
            if (method_exists($controller, $methodName)) {
                $html = $event->html;
                $settings = $html->getSettings();
                $output =  $controller->{$methodName}($settings);
                $html->add([
                    'html' => $output->toHtml(),
                    'order' => 575,
                    'module' => $module,
                    'permission' => 'tax option setting manage'
                ]);
            }
        }
    }
}
