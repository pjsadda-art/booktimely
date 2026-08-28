<?php

namespace Workdo\SMS\Listeners;
use App\Events\CompanySettingEvent;

class CompanySettingListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanySettingEvent $event): void
    {
        echo "CompanySettingListener called";die;
        $module = 'SMS1';
        $methodName = 'index';
        $controllerClass = "Workdo\\SMS\\Http\\Controllers\\Company\\SettingsController";
        if (class_exists($controllerClass)) {
            $controller = \App::make($controllerClass);
            if (method_exists($controller, $methodName)) {
                $html = $event->html;
                $settings = $html->getSettings();
                $output =  $controller->{$methodName}($settings);
                $html->add([
                    'html' => $output->toHtml(),
                    'order' => 570,
                    'module' => $module,
                    'permission' => 'sms manage'
                ]);
            }
        }
    }
}
