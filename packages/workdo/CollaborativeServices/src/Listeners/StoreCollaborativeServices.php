<?php

namespace Workdo\CollaborativeServices\Listeners;

use App\Events\AppointmentPaymentData;
use App\Events\CreateAppoinment;
use App\Models\Appointment;
use App\Models\AppointmentPayment;
use App\Models\Business;
use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\Role;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Hash;

class StoreCollaborativeServices
{
    public function __construct()
    {
        //
    }


    public function handle(\Workdo\CollaborativeServices\Events\StoreCollaborativeServices $event)
    {
        $data = $event->data;
        $collaborative_service = Service::find($data['service']);
        $business = Business::find($data['business']);

        $default_status = company_setting('default_status',$business->created_by,$business->id);

        if (module_is_active('CollaborativeServices', $collaborative_service->created_by)) {
            $serviceIdsString = $collaborative_service['collaborative_service_id'];
            $serviceIds = explode(',', $serviceIdsString);

            list($startTime, $endTime) = explode('-', $data['duration']);
            $initialStartTime = Carbon::createFromFormat('H:i', $startTime);

            $serviceDurations = [];
            foreach ($serviceIds as $serviceId) {
                $service = Service::find($serviceId);
                if ($service) {
                    $currentEndTime = $initialStartTime->copy()->addMinutes((int) $service->duration);
                    $serviceDurations[$serviceId] = $initialStartTime->format('H:i') . '-' . $currentEndTime->format('H:i');
                }
            }

            if (isset($request['attachment'])) {
                $filenameWithExt = $data->file('attachment')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $data->file('attachment')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($data, 'attachment', $fileNameToStore, 'Appointment');
                if ($uplaod['flag'] == 1) {
                    $url = $uplaod['url'];
                } else {
                    return response()->json(['msg' => 'error', 'error' => $uplaod['msg']]);
                }
            }

            if ($data['type'] == 'new-user') {
                $roles = Role::where('name', 'customer')->where('created_by', $business->created_by)->first();
                if ($roles) {
                    $user = User::create(
                        [
                            'name' => !empty($data['name']) ? $data['name'] : null,
                            'email' => !empty($data['email']) ? $data['email'] : null,
                            'mobile_no' => !empty($data['contact']) ? $data['contact'] : null,
                            'email_verified_at' => date('Y-m-d h:i:s'),
                            'password' => !empty($data['password']) ? Hash::make($data['password']) : null,
                            'avatar' => 'uploads/users-avatar/avatar.png',
                            'type' => 'customer',
                            'lang' => 'en',
                            'business_id' => $business->id,
                            'created_by' => $business->created_by,
                        ]
                    );
                    $user->addRole($roles);

                    $customer = new Customer();
                    $customer->name = $data['name'];
                    $customer->user_id = $user->id;
                    $customer->gender = !empty($data['gender']) ? $data['gender'] : '';
                    $customer->dob = !empty($data['dob']) ? $data['dob'] : '';
                    $customer->description = !empty($data['description']) ? $data['description'] : '';
                    $customer->business_id = $user->business_id;
                    $customer->created_by = $user->created_by;
                    $customer->save();
                }
            }

            if ($data['type'] == 'existing-user') {
                $email = $data['email'];
                $user = User::where('email', $email)->where('type', 'customer')->first();
                if (!empty($data['password']) && !empty($user)) {
                    $check_password = Hash::check($data['password'], $user->password);
                    if ($check_password) {
                        $customer = Customer::where('user_id', $user->id)->first();
                    } else {
                        return response()->json(['msg' => 'error', 'error' => 'Enter correct password']);
                    }
                } else {
                    return response()->json(['msg' => 'error', 'error' => 'Please enter valid email']);
                }
            }

            $app_id = [];
            foreach ($serviceIds as $index => $serviceId) {
                $service = Service::find($serviceId);
                $Appointment = new Appointment();

                if ($data['type'] == 'new-user' || $data['type'] == 'existing-user') {
                    $Appointment->customer_id = !empty($customer) ? $customer->user_id : null;
                } else {
                    $Appointment->customer_id = !empty($data['customer']) ? $data['customer'] : null;
                }
                $staff = Staff::whereIn('service_id', $service)->first();
                $Appointment->location_id = $staff ? $staff->location_id[0] : null;
                $Appointment->service_id = $serviceId;
                $Appointment->staff_id = $staff ? $staff->user_id : null;
                if ($data['type'] == 'guest-user') {
                    $Appointment->name = $data['name'];
                    $Appointment->email = $data['email'];
                    $Appointment->contact = $data['contact'];
                }
                $Appointment->date = !empty($data['appointment_date']) ? $data['appointment_date'] : '';
                $Appointment->time = !empty($serviceDurations) ? $serviceDurations[$serviceId] : '';
                $Appointment->notes = !empty($data['notes']) ? $data['notes'] : '';
                $Appointment->payment_type = !empty($data['payment_type']) ? $data['payment_type'] : 'Manually';
                $Appointment->appointment_status = !empty($default_status) ? $default_status : 'Pending';
                $Appointment->attachment = !empty($data['attachment']) ? $url : null;
                $Appointment->custom_field = !empty($data['values']) ? json_encode($data['values']) : null;
                $Appointment->business_id = $business->id;
                $Appointment->created_by = $business->created_by;
                $Appointment->save();
                $app_id[] = $Appointment->id;
            }

            $payment = AppointmentPayment::create([
                'appointment_id' => null,
                'payment_type' => $Appointment->payment_type,
                'amount' => $collaborative_service->price,
                'payment_date' => now(),
                'appointment_ids' => implode(',', $app_id),
                'business_id' => $business->id,
                'created_by' => $business->created_by,
            ]);

            event(new AppointmentPaymentData($data, $payment, $collaborative_service));

            $appointment_number = Appointment::appointmentNumberFormat($Appointment->id, $business->created_by, $business->id);

            $company_settings = getCompanyAllSetting($Appointment->created_by, $Appointment->business_id);

            //Email notification
            if ((!empty($company_settings['Create Appointment']) && $company_settings['Create Appointment'] == true)) {
                $trackingUrl = route('find.appointment', ['businessSlug' => $business->slug]);
                $uArr = [
                    'company_name' => $business->name ?? '',
                    'service' => $Appointment->ServiceData ? $Appointment->ServiceData->name : '-',
                    'location' => $Appointment->LocationData ? $Appointment->LocationData->name : '-',
                    'staff' => $Appointment->StaffData->user ? $Appointment->StaffData->user->name : '-',
                    'appointment_date' => $Appointment->date,
                    'appointment_time' => $Appointment->time,
                    'appointment_number' => $appointment_number,
                    'tracking_url' => $trackingUrl,
                ];
                $resp = EmailTemplate::sendEmailTemplate('Create Appointment', [$Appointment->CustomerData ? $Appointment->CustomerData->customer->email : $Appointment->email], $uArr, $Appointment->created_by, $business->id);
            }

            event(new CreateAppoinment($Appointment, $data));

            return $Appointment->id;
        }
    }
}
