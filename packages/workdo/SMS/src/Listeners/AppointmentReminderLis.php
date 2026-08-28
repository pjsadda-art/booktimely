<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\AppointmentReminder as EventsAppointmentReminder;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Workdo\SMS\Entities\SendMsg;

class AppointmentReminderLis
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

    public function handle(EventsAppointmentReminder $event)
    {
        // echo "here sms";    
        $appointment = $event->appoinment;

        if($event->appoinment->customer_id == null){
            $mobile_no = $appointment->contact;
            $custName = $appointment->name;

        }else {
            $customer = User::find($event->appoinment->customer_id);
            $mobile_no = $customer->mobile_no;
            $custName = $customer->name;
        }

        // echo "Mobile No: ".$mobile_no."\n";
        // echo company_setting('SMS Appointment Reminder')."\n";
        if (module_is_active('SMS') && company_setting('sms_notification_is' , $appointment->created_by, $appointment->business_id) == 'on'  &&
        !empty(company_setting('SMS Appointment Reminder', $appointment->created_by, $appointment->business_id)) && company_setting('SMS Appointment Reminder', $appointment->created_by, $appointment->business_id) == true && !empty($mobile_no)) {

            $uArr = [
                'appointment_name'  => $custName,
                'status'            => $appointment->StatusData->title ?? 'Pending',
                'appointment_id'     => $appointment->id,
                'booking_reference' => $appointment->appointmentNumberFormat($appointment->id, $appointment->created_by, $appointment->business_id),
                'date'              => $appointment->date,
                'time'              => $appointment->time,
                'location_contact'  => $appointment->LocationData->phone ?? '',
                'location'          => $appointment->LocationData->name ?? '',
                'company_name'      => $appointment->business->name ?? '',
            ];
            $to = $mobile_no ?? Auth::user()->mobile_no;

            SendMsg::SendMsgs($to , $uArr , 'Appointment Reminder', $appointment->created_by, $appointment->business_id);
        }
    }
}
