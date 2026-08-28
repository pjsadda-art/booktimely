<?php

namespace Workdo\Photography\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\DefaultData;
use Workdo\Photography\Entities\PhotographyUtility;

class PhotographyDefaultDataLis
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
        if(!empty($user_module))
        {
            if (in_array("Photography", $user_module))
            {
                PhotographyUtility::defaultdata($company_id,$business_id);
            }
        }
    }
}
