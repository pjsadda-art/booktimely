<?php

namespace Workdo\ServiceTax\Listeners;

use App\Models\Service;

class ServiceTaxStoreLis
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $request = $event->request;
        $service = $event->service;
        if(module_is_active('ServiceTax', $service->created_by)){
        $service = Service::find($event->service->id);
        
            if($service){
                $service->service_tax = $request['service_tax'];
                $service->save();
            }
        }
    }
}
