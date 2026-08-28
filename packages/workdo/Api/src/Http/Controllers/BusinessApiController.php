<?php

namespace Workdo\Api\Http\Controllers;

use App\Models\Business;
use App\Traits\ApiResponser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BusinessApiController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business = getBusiness();
        $businessList =[];
        if(!empty($business))
        {
            foreach ($business as $key => $value)
            {
                $business_url = (route('appointments.form', $value->slug));

                $businessList[$key]['id'] = $value->id;
                $businessList[$key]['name'] = $value->name;
                $businessList[$key]['url'] = $business_url;
            }
        }
        return $this->success($businessList);
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
        //
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
        $business = Business::find($id);
        if(!empty($business)){
            return $this->success($business);
        }
        else{
            return $this->error(['message' => 'No data available']);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',

            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }

        $user = Auth::user();
        $active_business = $user->active_business;
        $business = Business::find($active_business);
        if($business)
        {
            $business->name = $request->name;

            if($request->slug)
            {
                $business->slug = $request->slug;
            }
            $business->save();

            return $this->success(['message' => 'Business successfully updated.']);
        }
        else
        {
            return $this->error(['message' => 'Business not found!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy()
    {
        $user = Auth::user();
        $active_business = $user->active_business;
        $business = Business::find($active_business);

        if($business) {
            $other_business = Business::where('created_by', $user->id)
                ->where('is_disable', 1)
                ->where('id', '!=', $business->id)
                ->first();

            if($other_business) {
                $user->active_business = $other_business->id;
                $user->save();
                $business->delete();

                return $this->success(['business_id'=>$user->active_business,'message' => 'Business deleted successfully!']);
            }

            return $this->error(['message' => 'You cannot delete this Business because your other businesses are disabled.']);

        } else {
            return $this->error(['message' => 'Business not found!']);
        }
    }

    public function BusinessChange($business_id)
    {
        $business = Business::find($business_id);
        if($business)
        {
            $user = Auth::user();
            $user->active_business = $business_id;
            $user->save();

            $user_array['active_business'] = $user->active_business;

            return $this->success([$user_array, 'message' => 'Business Change successfully!']);

        }
        else
        {
            return $this->error(['message' => 'Business not found!']);
        }
    }

}
