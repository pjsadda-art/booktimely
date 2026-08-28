<?php

namespace Workdo\CarService\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\DefaultData;
use Workdo\CarService\Entities\CarServiceUtility;

class CarServiceDataLis
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

    public function handle(DefaultData $event)
    {
        $company_id = $event->company_id;
        $business_id = $event->business_id;
        $user_module = $event->user_module;
        if (!empty($user_module)) {
            if (in_array("CarService", $user_module)) {
                CarServiceUtility::defaultdata($company_id, $business_id);
            }
        }
    }
}
