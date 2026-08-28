<?php

namespace Workdo\CollaborativeServices\Entities;

use App\Events\AppointmentPaymentData;
use App\Events\CreateAppoinment;
use App\Models\Appointment;
use App\Models\AppointmentPayment;
use App\Models\Business;
use App\Models\BusinessHours;
use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\Role;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class CollaborativeServiceUtility extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function collaborativeTimeSlote($serviceId = null, $date = null, $flexible_data = null){
        $service = Service::find($serviceId);
        $selectedDate = Carbon::createFromFormat('d-m-Y', $date);
        $company_settings = getCompanyAllSetting($service->created_by, $service->business_id);
        $maximum_slot = isset($company_settings['maximum_slot']) ? $company_settings['maximum_slot'] : '1';
        $dayName = $selectedDate->format('l');  //get dayname using date

        if ($date && !empty($service)) {
            $serviceIds = explode(',', $service->collaborative_service_id);
            $firstServiceId = $serviceIds[0];
            $duration_service = Service::find($firstServiceId);

            $booked_appointment = Appointment::where('service_id', $service->id)->where('date', $date)->where('business_id', $service->business_id)->where('created_by', $service->created_by)->select('time')->get()->toArray();

            $businessday = BusinessHours::where('created_by',$service->created_by)->where('business_id',$service->business_id)->where('day_name',$dayName)->first();

            $duration = $duration_service->duration;

            $start_time = Carbon::createFromFormat('H:i:s', isset($businessday->start_time) ? $businessday->start_time : '09:30:00');
            $end_time = Carbon::createFromFormat('H:i:s', isset($businessday->end_time) ? $businessday->end_time : '18:00:00');
            $break_times = isset($businessday->break_hours) ? json_decode($businessday->break_hours, true) : '';

            $timeSlots = [];
            $currentSlot = clone $start_time;
            // $now = Carbon::now($company_settings['defult_timezone'])->format('H:i');    //get current time
            $now = Carbon::now($company_settings['defult_timezone']);
            $isToday = $selectedDate->isToday();

            // If the selected date is today, use current time as the cutoff
            if ($isToday) {
                $now = $now->format('H:i');
            } else {
                // If the date is tomorrow or later, ignore the current time and start from business start time
                $now = $start_time->format('H:i');
            }

            if (is_array($break_times)) {
                foreach ($break_times as $break) {
                    $breakStart = Carbon::createFromFormat('H:i', $break['start']);
                    $breakEnd = Carbon::createFromFormat('H:i', $break['end']);

                    // Add time slots before the break, excluding booked slots
                    while ($currentSlot->addMinutes((int) $duration)->lt($breakStart)) {
                        $slot = [
                            'start' => $currentSlot->copy()->subMinutes((int) $duration)->format('H:i'),
                            'end' => $currentSlot->format('H:i'),
                            'service_id' => $firstServiceId
                        ];
                    }

                    // Skip slots before the current time
                    if ($currentSlot->lt($now)) {
                        continue;
                    }

                    // Skip time slots during the break
                    if ($currentSlot->lte($breakEnd)) {
                        $currentSlot = $breakEnd->copy();
                    }
                }
            }

            // Add remaining time slots after the last break, excluding booked slots
            while ($currentSlot->addMinutes((int) $duration)->lte($end_time)) {
                $slot = [
                    'start' => $currentSlot->copy()->subMinutes((int) $duration)->format('H:i'),
                    'end' => $currentSlot->format('H:i'),
                    'service_id' => $firstServiceId
                ];

                // Skip slots before the current time
                if ($currentSlot->lt($now)) {
                    continue;
                }

                $bookedCount = isSlotBooked($slot, $booked_appointment);
                if ($bookedCount < $maximum_slot) {
                    $timeSlots[] = $slot;
                }
            }
            return $timeSlots;
        }
    }

    public static function storeCollaborativeService($request){
        $collaborative_service = Service::find($request['service']);
        $business = Business::find($collaborative_service->business_id);
        $serviceIdsString = $collaborative_service['collaborative_service_id'];
        $serviceIds = explode(',', $serviceIdsString);

        $default_status = company_setting('default_status',$business->created_by,$business->id);

        list($startTime, $endTime) = explode('-', $request['duration']);
        $initialStartTime = Carbon::createFromFormat('H:i', $startTime);


        $serviceDurations = [];
        foreach ($serviceIds as $serviceId) {
            $service = Service::find($serviceId);
            if ($service) {
                $currentEndTime = $initialStartTime->copy()->addMinutes((int) $service->duration);
                $serviceDurations[$serviceId] =  $initialStartTime->format('H:i') . '-' . $currentEndTime->format('H:i');
            }
        }

        if (isset($request['attachment'])) {
            $filenameWithExt = $request['attachment']->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request['attachment']->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $upload = upload_file($request, 'attachment', $fileNameToStore, 'Appointment');
            if ($upload['flag'] == 1) {
                $url = $upload['url'];
            } else {
                // return response()->json(['msg' => 'error', 'error' => $upload['msg']]);
                return (object)['status' => 'failed', 'message' => $upload['msg'], 'data' => 'failed'];
            }
        }

        if ($request['type'] == 'new-user') {
            $roles = Role::where('name', 'customer')->where('created_by', $business->created_by)->first();
            if ($roles) {
                $user = User::create(
                    [
                        'name' => !empty($request['name']) ? $request['name'] : null,
                        'email' => !empty($request['email']) ? $request['email'] : null,
                        'mobile_no' => !empty($request['contact']) ? $request['contact'] : null,
                        'email_verified_at' => date('Y-m-d h:i:s'),
                        'password' => !empty($request['password']) ? Hash::make($request['password']) : null,
                        'avatar' => 'uploads/users-avatar/avatar.png',
                        'type' => 'customer',
                        'lang' => 'en',
                        'business_id' => $business->id,
                        'created_by' => $business->created_by,
                    ]
                );
                $user->addRole($roles);

                $customer = new Customer();
                $customer->name = $request['name'];
                $customer->user_id = $user->id;
                $customer->gender = !empty($request['gender']) ? $request['gender'] : '';
                $customer->dob = !empty($request['dob']) ? $request['dob'] : '';
                $customer->description = !empty($request['description']) ? $request['description'] : '';
                $customer->business_id = $user->business_id;
                $customer->created_by = $user->created_by;
                $customer->save();
            }
        }

        if ($request['type'] == 'existing-user') {
            $email = $request['email'];
            $user = User::where('email', $email)->where('type', 'customer')->first();
            if (!empty($request['password']) && !empty($user)) {
                $check_password = Hash::check($request['password'], $user->password);
                if ($check_password) {
                    $customer = Customer::where('user_id', $user->id)->first();
                } else {
                    // return response()->json(['msg' => 'error', 'error' => 'Enter correct password']);
                    return (object)['status' => 'failed', 'message' => 'Enter correct password', 'data' => 'failed'];
                }
            } else {
                // return response()->json(['msg' => 'error', 'error' => 'Please enter valid email']);
                return (object)['status' => 'failed', 'message' => 'Please enter valid email', 'data' => 'failed'];
            }
        }

        $app_id = [];
        foreach ($serviceIds as $index => $serviceId) {
            $service = Service::find($serviceId);
            $Appointment = new Appointment();

            if ($request['type'] == 'new-user' || $request['type'] == 'existing-user') {
                $Appointment->customer_id = !empty($customer) ? $customer->user_id : null;
            } else {
                $Appointment->customer_id = !empty($request['customer']) ? $request['customer'] : null;
            }
            $staff = Staff::whereIn('service_id', $service)->first();
            $Appointment->location_id = $staff ? $staff->location_id[0] : null;
            $Appointment->service_id = $request['service'];
            $Appointment->staff_id = $staff ? $staff->user_id : null;
            if ($request['type'] == 'guest-user') {
                $Appointment->name = $request['name'];
                $Appointment->email = $request['email'];
                $Appointment->contact = $request['contact'];
            }
            $Appointment->date = !empty($request['appointment_date']) ? $request['appointment_date'] : '';
            $Appointment->time = !empty($serviceDurations) ? $serviceDurations[$serviceId] : '';
            $Appointment->notes = !empty($request['notes']) ? $request['notes'] : '';
            $Appointment->payment_type = !empty($request['payment']) ? $request['payment'] : 'Manually';
            $Appointment->appointment_status = !empty($default_status) ? $default_status : 'Pending';
            $Appointment->attachment = !empty($request['attachment']) ? $url : null;
            $Appointment->custom_field = !empty($request['values']) ? json_encode($request['values']) : null;
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

        event(new AppointmentPaymentData($request, $payment, $collaborative_service));

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

        event(new CreateAppoinment($Appointment, $request));

        return (object)['status' => 'success', 'message' => 'The Payment has been added successfully.', 'data' => $Appointment->id];
        // return $Appointment->id;
    }
}
