<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\AppointmentStatus as EventsAppointmentStatus;
use App\Models\CustomStatus;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Workdo\SMS\Entities\SendMsg;
use App\Models\Appointment;

class AppointmentStatusLis
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

    public function handle(EventsAppointmentStatus $event)
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
        $status         = CustomStatus::find($event->request->status ?? $event->request->new_status);
        if (
            $status &&
            module_is_active('SMS', $appointment->created_by) && company_setting('sms_notification_is', $appointment->created_by, $appointment->business_id) == 'on'  &&
            !empty(company_setting('SMS Appointment Status Change', $appointment->created_by)) && company_setting('SMS Appointment Status Change', $appointment->created_by) == true && !empty($mobile_no) && $status->send_sms == 1
        ) {

            $uArr = [
                'appointment_name'  => $custName,
                'status'            => $status->title ?? 'Pending',
                'booking_reference' => Appointment::appointmentNumberFormat($appointment->id, $appointment->created_by, $appointment->business_id),
                'date'              => $appointment->date,
                'time'              => $appointment->time,
                'location_contact'  => $appointment->LocationData->phone ?? '',
                'location'          => $appointment->LocationData->name ?? '',
                'company_name'      => $appointment->business->name ?? '',
            ];
            $to = $mobile_no ?? Auth::user()->mobile_no;

            SendMsg::SendMsgs($to, $uArr, 'Appointment Status Change', $appointment->created_by, $appointment->business_id);
        }
    }
}
