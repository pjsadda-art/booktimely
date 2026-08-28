<?php

namespace Workdo\ServiceTax\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentPayment;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Workdo\ProductService\Entities\Tax;
use Workdo\ServiceTax\Events\CalculatePromoCode;

use Carbon\Carbon;
use Workdo\ServiceTax\DataTables\ServiceTaxDatatable;

class ServiceTaxController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(ServiceTaxDatatable $dataTable)
    {
        if (Auth::user()->isAbleTo('servicetax manage')) {
            return $dataTable->render('service-tax::servicetax.index');
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
        if (Auth::user()->isAbleTo('servicetax create')) {
           return view('service-tax::servicetax.create');
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
        if (Auth::user()->isAbleTo('servicetax create')) {

            $validator = Validator::make(
                $request->all(),
                [
                  'title' => 'required',
                  'rate' => 'required|numeric|min:0|max:100',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            $serviceTax = new Tax();
            $serviceTax->name = $request->title;
            $serviceTax->rate = $request->rate;
            $serviceTax->business_id = getActiveBusiness();
            $serviceTax->created_by = creatorId();
            $serviceTax->save();

            return redirect()->back()->with('success', __('The service tax has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->isAbleTo('servicetax edit')) {
            $serviceTax = Tax::find($id);

            return view('service-tax::servicetax.edit', compact('serviceTax'));
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
        if (Auth::user()->isAbleTo('servicetax edit')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'rate' => 'required|numeric|min:0|max:100',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            $serviceTax = Tax::find($id);
            $serviceTax->name = $request->title;
            $serviceTax->rate = $request->rate;
            $serviceTax->business_id = getActiveBusiness();
            $serviceTax->created_by = creatorId();
            $serviceTax->save();

            return redirect()->back()->with('success', __('The service tax details are updated successfully.'));
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
        if (Auth::user()->isAbleTo('servicetax delete')) {
            $serviceTax = Tax::find($id);
            $serviceTax->delete();

            return redirect()->back()->with('success', __('The service tax has been deleted.'));
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
