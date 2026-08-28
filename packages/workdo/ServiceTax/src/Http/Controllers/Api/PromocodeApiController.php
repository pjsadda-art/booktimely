<?php

namespace Workdo\PromoCodes\Http\Controllers\Api;

use App\Models\AppointmentPayment;
use App\Traits\ApiResponser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\PromoCodes\Entities\PromoCode;
use Illuminate\Support\Facades\Validator;

class PromocodeApiController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        try {
            $promocodes = PromoCode::where('business_id', getActiveBusiness())->where('created_by', creatorId())->get();
            return $this->success($promocodes);

        } catch (\Exception $e) {
            return $this->error(['message' => 'Something Went Wrong!']);
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('promo-codes::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        try{
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
                $messages = $validator->getMessageBag();
                return $this->error([
                    'message' => $messages->first()
                ]);
            }

            $promoCode = new PromoCode();
            $promoCode->code = $request->promo_code;
            $promoCode->discount_percentage = $request->discount;
            $promoCode->flat_rate = $request->flat_rate;
            $promoCode->discount_type = $request->discount_type == 'on' ? 1 : 0;
            $promoCode->once_per_customer = $request->once_per_customer == 'on' ? 1 : 0;
            $promoCode->services = $request->services;
            $promoCode->use_limit = $request->use_limit;
            $promoCode->start_date = $request->start_date;
            $promoCode->end_date = $request->end_date;
            $promoCode->customers = $request->customers;
            $promoCode->business_id = getActiveBusiness();
            $promoCode->created_by = creatorId();
            $promoCode->save();

            return $this->success(['message' => 'PromoCode Created Successfully!']);
        } catch (\Exception $e) {
            return $this->error(['message' => 'Something Went Wrong!']);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('promo-codes::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
       //
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        try{
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
                $messages = $validator->getMessageBag();
                return $this->error([
                    'message' => $messages->first()
                ]);
            }

            $promocode = PromoCode::find($id);
            $promocode->code = $request->promo_code;
            $promocode->discount_percentage = $request->discount;
            $promocode->flat_rate = $request->flat_rate;
            $promocode->discount_type = $request->discount_type == 'on' ? 1 : 0;
            $promocode->once_per_customer = $request->once_per_customer == 'on' ? 1 : 0;
            $promocode->services = $request->services;
            $promocode->use_limit = $request->use_limit;
            $promocode->start_date = $request->start_date;
            $promocode->end_date = $request->end_date;
            $promocode->customers = $request->customers;
            $promocode->business_id = getActiveBusiness();
            $promocode->created_by = creatorId();
            $promocode->save();

            return $this->success(['message' => 'PromoCode Updated Successfully!']);
        } catch (\Exception $e) {
            return $this->error(['message' => 'Something Went Wrong!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        try{    
            $promocode = PromoCode::find($id);
            $promocode->delete();
            return $this->success(['message' => 'PromoCode Deleted Successfully!']);
        } catch (\Exception $e) {
            return $this->error(['message' => 'Something Went Wrong!']);
        }
    }

    public function promocodeDetail($id)
    {
        try{

            $promocodes = AppointmentPayment::where('promo_code_id', $id)->get();

            if($promocodes){

                return $this->success($promocodes);
            }
            else{
                return $this->error(['message' => 'PromoCode Not Found']);
            }

        } catch (\Exception $e) {
            return $this->error(['message' => 'Something Went Wrong!']);
        }
        
    }
}
