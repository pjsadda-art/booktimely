<?php

namespace Workdo\Api\Http\Controllers;

use App\Models\Business;
use App\Models\Location;
use App\Models\Staff;
use App\Traits\ApiResponser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LocationApiController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business = Business::find(getActiveBusiness());
        $locationData = Location::where('business_id', $business->id)->where('created_by', creatorId())->get();
        if ($locationData->isNotEmpty()) {
            $staffList = $locationData->map(function ($value) {
                return [
                    'id' => $value->id,
                    'name' => $value->name,
                    'image' => check_file($value->image) ? get_file($value->image) : get_file('uploads/default/avatar.png') ,
                    'address' => $value->address ? $value->address : '',
                    'phone' => $value->phone ? $value->phone : '',
                    'description' => $value->description ? $value->description : ''
                ];
            });

            return $this->success($staffList);
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
               'name' => 'required',
                'address' => 'required',
                'phone' => 'required',
                'description' => 'required',
                'location_image' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return $this->error([
                'message' => $messages->first()
            ]);
        }
        $business = Business::find($request->business_id);
        if ($request->hasFile('location_image')) {
            $filenameWithExt = $request->file('location_image')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('location_image')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $uplaod = upload_file($request, 'location_image', $fileNameToStore, 'Location');
            if ($uplaod['flag'] == 1) {
                $url = $uplaod['url'];
            } else {
                return $this->error(['message' => $uplaod['msg']]);
            }
        }

        $location                   = new Location();
        $location->name             = $request->name;
        $location->phone            = !empty($request->phone) ? $request->phone : '';
        $location->address          = $request->address;
        $location->description      = !empty($request->description) ? $request->description : '';
        $location->business_id      = !empty($business) ? $business->id : 0;
        $location->created_by       = creatorId();
        $location->image  = !empty($request->location_image) ? $url : '';
        $location->save();
        return $this->success(['message' => 'Location successfully created.']);
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
        $location = Location::find($id);

        if(!empty($location))
        {
            $validator = \Validator::make(
                $request->all(),
                [
                'name' => 'required',
                'address' => 'required',
                'phone' => 'required',
                'description' => 'required',
                'location_image' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return $this->error([
                    'message' => $messages->first()
                ]);
            }
            $location->name             = $request->name;
            $location->phone            = !empty($request->phone) ? $request->phone : '';
            $location->address          = $request->address;
            $location->description      = !empty($request->description) ? $request->description : '';

            if ($request->hasFile('location_image')) {
                $filenameWithExt = $request->file('location_image')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('location_image')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($request, 'location_image', $fileNameToStore, 'Location');
                if ($uplaod['flag'] == 1) {
                    if (!empty($location->image)) {
                        delete_file($location->image);
                    }
                    $url = $uplaod['url'];
                } else {
                    return $this->error(['message' => $uplaod['msg']]);
                }
                $location->image  = !empty($request->location_image) ? $url : '';
            }
            $location->save();
            return $this->success(['message' => 'Location updated successfully.']);

        }else{
            return $this->error(['message' => 'Location not found.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $location = Location::find($id);
        if(!empty($location))
        {
            if (!empty($location->image)) {
                delete_file($location->image);
            }
            $location->delete();
            return $this->success(['message' => 'Location successfully delete.']);
        }else{
            return $this->error(['message' => 'Location not found.']);
        }
    }
}
