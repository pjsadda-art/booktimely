<?php

namespace Workdo\ServiceNumber\Listeners;

use App\Models\Service;

class ServiceNumberStoreLis
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
        if(module_is_active('ServiceNumber', $service->created_by)){
        $service = Service::find($event->service->id);
        
            if($service){
                $service->service_number = $request['service_number'];
                $service->save();
            }
        }
    }
}
