<?php

namespace Workdo\PromoCodes\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentPayment;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Workdo\PromoCodes\Entities\PromoCode;
use Workdo\PromoCodes\Events\CalculatePromoCode;

use Carbon\Carbon;
use Workdo\PromoCodes\DataTables\PromoCodesDatatable;

class PromoCodesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(PromoCodesDatatable $dataTable)
    {
        if (Auth::user()->isAbleTo('promocode manage')) {
            return $dataTable->render('promo-codes::promocode.index');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('promocode create')) {
            $services = Service::where('business_id', getActiveBusiness())->where('created_by', creatorId())->get()->prepend(['id' => 0, 'name' => 'All Service'])->select('name', 'id')->pluck('name', 'id');

            $customers = Customer::where('business_id', getActiveBusiness())->where('created_by', creatorId())->get()->prepend(['id' => 0, 'name' => 'All Customers'])->select('name', 'id')->pluck('name', 'id');

            return view('promo-codes::promocode.create', compact('services', 'customers'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('promocode create')) {

            $validator = Validator::make(
                $request->all(),
                [
                    'promo_code' => 'required',
                    'discount' => 'required',
                    'flat_rate' => 'required',
                    'discount_type' => 'required',
                    'once_per_customer' => 'required',
                    'services' => 'required',
                    'use_limit' => 'required',
                    'customers' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required|after_or_equal:start_date'
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            $promo_code = new PromoCode();
            $promo_code->code = $request->promo_code;
            $promo_code->discount_percentage = $request->discount;
            $promo_code->flat_rate = $request->flat_rate;
            $promo_code->discount_type = $request->discount_type == 'on' ? 1 : 0;
            $promo_code->once_per_customer = $request->once_per_customer == 'on' ? 1 : 0;
            $promo_code->services = implode(',', $request->services);
            $promo_code->use_limit = $request->use_limit;
            $promo_code->start_date = $request->start_date;
            $promo_code->end_date = $request->end_date;
            $promo_code->customers = implode(',', $request->customers);
            $promo_code->business_id = getActiveBusiness();
            $promo_code->created_by = creatorId();
            $promo_code->save();

            return redirect()->back()->with('success', __('The promo code has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $promocodes = AppointmentPayment::where('promo_code_id', $id)->get();

        return view('promo-codes::promocode.view', compact('promocodes'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (Auth::user()->isAbleTo('promocode edit')) {
            $promocodes = PromoCode::find($id);

            $services = Service::where('business_id', getActiveBusiness())->where('created_by', creatorId())->get()->prepend(['id' => 0, 'name' => 'All Service'])->select('name', 'id')->pluck('name', 'id');

            $customers = Customer::where('business_id', getActiveBusiness())->where('created_by', creatorId())->get()->prepend(['id' => 0, 'name' => 'All Customers'])->select('name', 'id')->pluck('name', 'id');

            return view('promo-codes::promocode.edit', compact('promocodes', 'services', 'customers'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('promocode edit')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'promo_code' => 'required',
                    'discount' => 'required',
                    'flat_rate' => 'required',
                    'discount_type' => 'required',
                    'once_per_customer' => 'required',
                    'services' => 'required',
                    'use_limit' => 'required',
                    'customers' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required|after_or_equal:start_date'
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            $promocode = PromoCode::find($id);
            $promocode->code = $request->promo_code;
            $promocode->discount_percentage = $request->discount;
            $promocode->flat_rate = $request->flat_rate;
            $promocode->discount_type = $request->discount_type == 'on' ? 1 : 0;
            $promocode->once_per_customer = $request->once_per_customer == 'on' ? 1 : 0;
            $promocode->services = implode(',', $request->services);
            $promocode->use_limit = $request->use_limit;
            $promocode->start_date = $request->start_date;
            $promocode->end_date = $request->end_date;
            $promocode->customers = implode(',', $request->customers);
            $promocode->business_id = getActiveBusiness();
            $promocode->created_by = creatorId();
            $promocode->save();

            return redirect()->back()->with('success', __('The promo code details are updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (Auth::user()->isAbleTo('promocode delete')) {
            $promocode = PromoCode::find($id);
            $promocode->delete();

            return redirect()->back()->with('success', __('The promo code has been deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function applyPromoCode(Request $request)
    {
        $service = Service::find($request->service);
        $promocode = PromoCode::where('code', $request->promocode)->first();

        if ($promocode && $promocode != null) {

            $requestedDate = Carbon::createFromFormat('d-m-Y', $request->selectedDate)->format('Y-m-d');
            $start_date = Carbon::createFromFormat('Y-m-d', $promocode->start_date)->format('Y-m-d');
            $end_date = Carbon::createFromFormat('Y-m-d', $promocode->end_date)->format('Y-m-d');

            if (($requestedDate >= $start_date && $requestedDate <= $end_date) && $promocode->promo_used < $promocode->use_limit) {
                $service_data = explode(',', $promocode['services']);
                $user_data = explode(',', $promocode['customers']);
                $selected_service_data = explode(',', $request->cart_service_id);

                $discount = 0;
                if ($service != null && $promocode != null) {
                    // for check promo code used once per customer
                    if ($promocode->once_per_customer == 1) {
                        $check_user = AppointmentPayment::where('promo_code_id', $promocode->id)->get();
                        $appointmentIds = [];

                        foreach ($check_user as $record) {
                            $appointmentId = $record['appointment_id'];
                            if (!in_array($appointmentId, $appointmentIds)) {
                                $appointmentIds[] = $appointmentId;
                            }
                        }
                        $appointment_user = Appointment::whereIn('id', $appointmentIds)->get();
                        $customerIds = [];

                        foreach ($appointment_user as $customer_id) {
                            $customerId = $customer_id['customer_id'];
                            if (!in_array($customerIds, $customerIds)) {
                                $customerIds[] = $customerId;
                            }
                        }

                        if ($request->type == 'existing-user') {
                            $user = User::where('email', $request->email)->first();

                            if ($user != null) {
                                $customer = Customer::where('user_id', $user->id)->first();

                                if ($customer === null) {
                                    return response()->json([
                                        'error' => __('Customer Not in the List.'),
                                        'final_amount' => $service->price,
                                    ]);
                                }
                                if (in_array($customer->user_id, $customerIds)) {
                                    return response()->json([
                                        'error' => __('Customer Can only use Promo Code for Once.'),
                                        'final_amount' => $service->price,
                                        'promo_code_apply' => 0,
                                    ]);
                                }
                            }
                        }

                        if ($request->type == 'guest-user') {
                            $customer = Appointment::where('email', $request->email)->get();

                            if ($customer->isNotEmpty()) {
                                return response()->json([
                                    'error' => __('Customer Can only use Promo Code for Once.'),
                                    'final_amount' => $service->price,
                                    'promo_code_apply' => 0,
                                ]);
                            }

                        }
                    }                    
                    if ($request->type == 'existing-user') {
                        $user = User::where('email', $request->email)->first();
                        if ($user != null) {
                            $customer = Customer::where('user_id', $user->id)->first();
                            if ($customer === null) {
                                return response()->json([
                                    'error' => __('Customer can not Use this Promo Code.'),
                                    'final_amount' => $service->price,
                                ]);
                            }
                        }
                    }

                    if ($request->type == 'guest-user') {
                        if ($promocode->customers != 0) {
                            return response()->json([
                                'error' => __('Customer can not Use this Promo Code.'),
                                'final_amount' => $service->price,
                            ]);
                        }
                    }

                    if ($request->type == 'new-user') {
                        if ($promocode->customers != 0) {
                            return response()->json([
                                'error' => __('Customer can not Use this Promo Code.'),
                                'final_amount' => $service->price,
                            ]);
                        }
                    }

                    $service_price = $service->price;

                    if (in_array($request->service, $service_data) || array_intersect($selected_service_data, $service_data)) {// for check customer (0 for all)
                        if ($promocode->customers == 0) { // for All Customers
                            $calculatePromo = event(new CalculatePromoCode($service, $promocode, $request->all()));
                            $promo_data = $calculatePromo[0];

                            return response()->json($promo_data);
                        } elseif ($customer != null && in_array($customer->id, $user_data)) {
                            $calculatePromo = event(new CalculatePromoCode($service, $promocode, $request->all()));
                            $promo_data = $calculatePromo[0];

                            return response()->json($promo_data);
                        } else {
                            return response()->json([
                                'error' => __('User not found in the list.'),
                                'final_amount' => $service->price,
                            ]);
                        }
                    } elseif ($promocode->services == 0) { // for check service is all or not
                        if ($promocode->customers == 0 || $request->type == 'new-user') { // for All Customers
                            $calculatePromo = event(new CalculatePromoCode($service, $promocode, $request->all()));
                            $promo_data = $calculatePromo[0];
                            return response()->json($promo_data);
                        } elseif (!empty($customer) && in_array($customer->id, $user_data)) {
                            $calculatePromo = event(new CalculatePromoCode($service, $promocode, $request->all()));
                            $promo_data = $calculatePromo[0];
                            return response()->json($promo_data);
                        } else {
                            return response()->json([
                                'error' => __('User not found in the list.'),
                                'final_amount' => $service->price,
                            ]);
                        }
                    } else {
                        return response()->json([
                            'error' => __('Promo Code is not Applicable on this Service.'),
                            'final_amount' => $service->price,
                        ]);
                    }
                }
            } else {
                return response()->json([
                    'error' => __('Promo Code is Expired.'),
                    'final_amount' => $service->price,
                    'promo_code_apply' => 0,
                ]);
            }
        } else {
            return response()->json([
                'error' => __('Promo Code Not Valid.'),
                'final_amount' => $service->price ?? 0,
            ]);
        }
    }
}
