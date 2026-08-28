<?php

namespace Workdo\CompoundService\Listeners;

use App\Models\Service;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompoundServiceStoreLis
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

        if(module_is_active('CompoundService', $service->created_by) && $request['service_type'] == 'compound'){
        $service = Service::find($event->service->id);
        
            if($service && $request['service_type'] == 'compound'){
                $services = array_column($request['services'], 'services');
                $servicesString = implode(',', $services);

                $service->service_type = $request['service_type'];
                $service->service_id = $servicesString;
                $service->save();
            }
        }
    }
}
