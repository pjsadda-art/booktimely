<?php

namespace Workdo\Api\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentPayment;
use App\Models\CustomStatus;
use App\Models\Service;
use App\Traits\ApiResponser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentApiController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $active_business = $user->active_business;

        // service-list
        $serviceList = [];
        $serviceData = Service::where('business_id', $active_business)->get()->toArray();
        foreach ($serviceData as $key => $service) {
            $serviceList[$key]['id'] = $service['id'];
            $serviceList[$key]['name'] = $service['name'];
        }
        array_unshift($serviceList, ['id' => 0, 'name' => 'All']);

        if(!empty($request->service_id) && $request->service_id != '0')
        {
            $appointmentData = Appointment::where('service_id',$request->service_id)->where('business_id', $active_business)->paginate(10);
        }else{
            $appointmentData = Appointment::where('business_id', $active_business)->paginate(10);
        }

        if ($appointmentData->isNotEmpty()) {
            $appointmentList = $appointmentData->map(function ($value) {
                $appointmentNumber = Appointment::appointmentNumberFormat($value->id, $value->created_by, $value->business_id);

                return [
                    'id' => $value->id,
                    'appointment_number' => $appointmentNumber,
                    'date' => $value->date,
                    'duration' => $value->time,
                    'customer' => !empty($value->CustomerData) ? $value->CustomerData->name : 'Guest',
                    'email' => !empty($value->CustomerData) ? $value->CustomerData->customer->email : $value->email,
                    'contact' => !empty($value->CustomerData) ? $value->CustomerData->customer->mobile_no : $value->contact,
                    'staff' => !empty($value->StaffData) ? $value->StaffData->name : '-',
                    'service' => !empty($value->ServiceData) ? $value->ServiceData->name : '-',
                    'location' => !empty($value->LocationData) ? $value->LocationData->name : '-',
                    'payment' => !empty($value->payment_type) ? $value->payment_type : '-',
                    'status' => !empty($value->StatusData) ? $value->StatusData->title : 'Pending',
                    'status_color' => !empty($value->StatusData) ? $value->StatusData->status_color : '5bc0de'
                ];
            });

            return $this->success([
                'service_list' => $serviceList,
                'appointment_list' => $appointmentList,
                'total' => $appointmentData->total(),
                'per_page' => $appointmentData->perPage(),
                'current_page' => $appointmentData->currentPage(),
                'last_page' => $appointmentData->lastPage(),
                'next_page_url' => $appointmentData->nextPageUrl(),
                'prev_page_url' => $appointmentData->previousPageUrl(),
        ]);


        } else {
            return $this->error(['message' => 'Record not found!']);
        }
    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('api::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'customer' => 'required',
                'location' => 'required',
                'service' => 'required',
                'staff' => 'required',
                'appointment_date' => 'required',
                'duration' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }
        $default_status = company_setting('default_status', creatorId(), getActiveBusiness());
        $service = Service::find($request->service);

        $appointment                   = new Appointment();
        $appointment->customer_id      = $request->customer;
        $appointment->location_id      = $request->location;
        $appointment->service_id       = $request->service;
        $appointment->staff_id         = $request->staff;
        $appointment->date             = !empty($request->appointment_date) ? $request->appointment_date : '';
        $appointment->time             = !empty($request->duration) ? $request->duration : '';
        $appointment->notes            = !empty($request->notes) ? $request->notes : '';
        $appointment->appointment_status = !empty($default_status) ? $default_status : 'Pending';
        $appointment->payment_type   = !empty($request->payment_type) ? $request->payment_type : 'Manually';
        $appointment->business_id      = getActiveBusiness();
        $appointment->created_by       = creatorId();
        $appointment->save();

        $payment = AppointmentPayment::create([
            'appointment_id' => $appointment->id,
            'payment_type' => $appointment->payment_type,
            'amount' => $service->price,
            'payment_date' => now(),
            'business_id' => $appointment->business_id,
            'created_by' => $appointment->created_by,
        ]);

        return $this->success(['message'=> 'Appointment successfully created']);


    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('api::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('api::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request`
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'customer' => 'required',
                'location' => 'required',
                'service' => 'required',
                'staff' => 'required',
                'appointment_date' => 'required',
                'duration' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }
        $service = Service::find($request->service);

        $appointment                   = Appointment::find($id);
        $appointment->customer_id      = $request->customer;
        $appointment->location_id      = $request->location;
        $appointment->service_id       = $request->service;
        $appointment->staff_id         = $request->staff;
        $appointment->date             = !empty($request->appointment_date) ? $request->appointment_date : '';
        $appointment->time             = !empty($request->duration) ? $request->duration : '';
        $appointment->notes            = !empty($request->notes) ? $request->notes : '';
        $appointment->save();

        $payment = AppointmentPayment::create([
            'appointment_id' => $appointment->id,
            'payment_type' => $appointment->payment_type,
            'amount' => $service->price,
            'payment_date' => now(),
            'business_id' => $appointment->business_id,
            'created_by' => $appointment->created_by,
        ]);
        return $this->success(['message'=> 'Appointment successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $appointment = Appointment::find($id);
        $appointment->delete();

        return $this->success(['message'=> 'Appointment deleted successfully']);
    }

    public function appoitmentDetail($id)
    {
        $appointment = Appointment::find($id);

        return $this->success($appointment);
    }


    public function AppointmentStatusChange(Request $request)
    {
        $rules = [
            'appointment_id' => 'required',
            'status' => 'required',
        ];

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }

        $appointment = Appointment::find($request->appointment_id);
        if($appointment)
        {
            $appointment->appointment_status =  $request->status;
            $appointment->save();
            return $this->success(['message'=> 'Appointment Status updated successfully']);
        }
        else
        {
            return $this->error(['message' => 'Appointment not found']);
        }
    }

    public function AppointmentStatusList(Request $request)
    {
        $user = Auth::user();
        $active_business = $user->active_business;

        $appointmentStatus = CustomStatus::where('business_id',$active_business)->get();

        $statusList =[];
        if($appointmentStatus->isNotEmpty())
        {
            foreach ($appointmentStatus as $key => $value)
            {
                $statusList[$key]['id'] = $value->id;
                $statusList[$key]['title'] = $value->title;
            }
            return $this->success($statusList);
        }
        else{
            return $this->error(['message' => 'Record not found']);
        }
    }

}
