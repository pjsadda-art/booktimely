<?php

namespace Workdo\SMS\Listeners;

use App\Models\Business;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\CreateAppoinment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Workdo\SMS\Entities\SendMsg;

class CreateAppoinmentListener
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

    public function handle(CreateAppoinment $event)
    {
        $appointment = $event->appoinment;

        if ($event->appoinment->customer_id == null) {
            $mobile_no = $appointment->contact;
            $custName = $appointment->name;
        } else {
            $customer = User::find($event->appoinment->customer_id);
            $mobile_no = $customer->mobile_no;
            $custName = $customer->name;
        }

        if (
            module_is_active('SMS', $appointment->created_by) && company_setting('sms_notification_is', $appointment->created_by) == 'on'  &&
            !empty(company_setting('SMS Create Appointment', $appointment->created_by)) && company_setting('SMS Create Appointment', $appointment->created_by) == true && !empty($mobile_no)
        ) {

            $business = Business::where('id', $appointment->business_id)->first();
            $trackingUrl = $business ? route('find.appointment', ['businessSlug' => $business->slug]) : '';
            
            $uArr = [
                'appointment_name' => $custName,
                'date' => $appointment->date,
                'time' => $appointment->time,
                'tracking_url' => $trackingUrl,
            ];

            $to = $mobile_no ?? Auth::user()->mobile_no;
            SendMsg::SendMsgs($to, $uArr, 'Create Appointment', $appointment->created_by);
        }
    }
}
