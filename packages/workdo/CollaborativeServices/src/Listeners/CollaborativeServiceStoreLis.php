<?php

namespace Workdo\CollaborativeServices\Listeners;

use App\Models\Service;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CollaborativeServiceStoreLis
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


    public function handle($event)
    {
        $request = $event->request;
        $service = $event->service;
        
        if(module_is_active('CollaborativeServices', $service->created_by) && $request['service_type'] == 'collaborative'){
        $service = Service::find($event->service->id);
            if($service && $request['service_type'] == 'collaborative'){
                $services = array_column($request['services'], 'services');
                $servicesString = implode(',', $services);

                $service->service_type = $request['service_type'];
                $service->collaborative_service_id = $servicesString;
                $service->save();
            }
        }
    }
}
