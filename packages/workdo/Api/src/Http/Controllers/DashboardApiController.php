<?php

namespace Workdo\Api\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentPayment;
use App\Models\Business;
use App\Models\Service;
use App\Traits\ApiResponser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DashboardApiController extends Controller
{
    use ApiResponser;
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $active_business = $user->active_business;
        $business_data = Business::find($user->active_business);
        $dashboard_data =[];

        $totalAppointment = Appointment::where('business_id',$active_business)->count();
        $totalRevenue = AppointmentPayment::where('business_id',$active_business)->sum('amount');

        $dashboard_data['total_business'] = getBusiness()->count();
        $dashboard_data['total_appointment'] = $totalAppointment;
        $dashboard_data['total_revenue'] = currency_format_with_sym($totalRevenue,$user->id,$active_business);
        $dashboard_data['business_url'] = (route('appointments.form', $business_data->slug));

        // Appointment chart
        $arrDuration = [];
        $arrParam = ['duration' => 'week'];


        if ($arrParam['duration']) {
            if ($arrParam['duration'] == 'week') {
                $previous_week = strtotime("-1 week +1 day");

                for ($i = 0; $i < 7; $i++) {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('d-M', $previous_week);
                    $previous_week = strtotime(date('Y-m-d', $previous_week) . " +1 day");
                }
            }
        }
        $arrTask = [];
        $i = 0;
        $arrTask[$i]['date'] = [];
        $arrTask[$i]['appointment'] = [];
        foreach ($arrDuration as $date => $label) {
                $data = Appointment::select(\DB::raw('count(*) as total'))->where('business_id', $active_business)->whereDate('created_at', '=', $date)->first();

            $arrTask[$i]['date'] = $label;
            $arrTask[$i]['appointment'] = $data->total;
            $i++;
        }

        $dashboard_data['appointmentChart'] = $arrTask;

        //latest service
        $latest_service = [];
        $serviceData = Service::where('business_id', $active_business)->latest()->take(5)->get();
        if(!empty($serviceData))
        {
            foreach ($serviceData as $key => $value) {
                $latest_service[$key]['id']  = $value->id;
                $latest_service[$key]['name']  = $value->name;
                $latest_service[$key]['price']  = $value->price;
                $latest_service[$key]['image']  = get_file($value->image);
            }
            $dashboard_data['product'] = $latest_service;
        }else{
            $dashboard_data['product'] = 'Product Data not found!';
        }

        //latest Appointment
        $latest_Appointment = [];
        $appointmentData = Appointment::where('business_id', $active_business)->latest()->take(5)->get();
        if(!empty($appointmentData))
        {
            foreach ($appointmentData as $key => $value) {
                $appointmentNumber = Appointment::appointmentNumberFormat($value->id, $value->created_by, $value->business_id) ;

                $latest_Appointment[$key]['id']  = $value->id;
                $latest_Appointment[$key]['appointment_number']  = $appointmentNumber;
                $latest_Appointment[$key]['date']  = $value->date;
                $latest_Appointment[$key]['duration']  = $value->time;
                $latest_Appointment[$key]['customer']  = !empty($value->CustomerData) ? $value->CustomerData->name : 'Guest';
                $latest_Appointment[$key]['email']  = !empty($value->CustomerData) ? $value->CustomerData->customer->email : $value->email;
                $latest_Appointment[$key]['contact']  = !empty($value->CustomerData) ? $value->CustomerData->customer->mobile_no : $value->contact;
                $latest_Appointment[$key]['staff']  = !empty($value->StaffData) ? $value->StaffData->name : '-';
                $latest_Appointment[$key]['service']  = !empty($value->ServiceData) ? $value->ServiceData->name : '-';
                $latest_Appointment[$key]['location']  = !empty($value->LocationData) ? $value->LocationData->name : '-';
                $latest_Appointment[$key]['payment']  = !empty($value->payment_type) ? $value->payment_type : '-';
                $latest_Appointment[$key]['status']  = !empty($value->StatusData) ? $value->StatusData->title : 'Pending';
                $latest_Appointment[$key]['status_color']  = !empty($value->StatusData) ? $value->StatusData->status_color : '5bc0de';

            }
            $dashboard_data['appointment'] = $latest_Appointment;
        }else{
            $dashboard_data['appointment'] = 'Product Data not found!';
        }

        return $this->success($dashboard_data);

    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required|max:120',
                'email' => ['required',
                            Rule::unique('users')->where(function ($query)  use ($user) {
                            return $query->whereNotIn('id',[$user->id])->where('created_by', $user->created_by)->where('business_id',$user->business_id);
                        })
                ],
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }

        if ($request->hasFile('profile'))
        {

            $filenameWithExt = $request->file('profile')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('profile')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $path = upload_file($request,'profile',$fileNameToStore,'users-avatar');
            if($path['flag'] == 1){
                // old img delete
                if(!empty($user->avatar) && strpos($user->avatar,'avatar.png') == false && check_file($user->avatar))
                {
                    delete_file($user->avatar);
                }
            }
            else
            {
                return $this->error(['message' => $path['msg']]);
            }
        }

        if (!empty($request->profile) && isset($path['url']))
        {
            $user->avatar =  $path['url'];
        }

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->save();

        $user_array['name'] = $user->name;
        $user_array['email'] = $user->email;
        $user_array['profile'] = get_file($user->avatar);

        return $this->success($user_array);
    }
}

