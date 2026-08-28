<?php

namespace Workdo\Api\Http\Controllers;

use App\Models\Business;
use App\Models\Subscribe;
use App\Traits\ApiResponser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SubscriberApiController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business = Business::find(getActiveBusiness());
        $subscribe = Subscribe::where('business_id', $business->id)->get();
        if (!empty($subscribe)) {
            return $this->success($subscribe);
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
                'email' => 'required',
                'theme' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }

        $business = Business::find($request->business_id);
        $Subscribe                    = new Subscribe();
        $Subscribe->email             = $request->email;
        $Subscribe->theme             = $request->theme;
        $Subscribe->business_id      = !empty($business) ? $business->id : 0;
        $Subscribe->save();

        return $this->success(['message' => 'Subscribe successfully created.']);
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
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $Subscribe = Subscribe::find($id);
        $Subscribe->delete();
        return $this->success(['message' => 'Subscribe successfully delete.']);
    }
}
