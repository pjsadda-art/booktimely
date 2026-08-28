<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if(Auth::user()->isAbleTo('location create'))
        {
            $business = Business::find($request->business_id);
            return view('location.create',compact('business'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(Auth::user()->isAbleTo('location create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                        'name' => 'required',
                        'address' => 'required',
                        'phone' => 'required',
                        'description' => 'required',
                        'location_image' => 'required',
                    ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $business = Business::find($request->business_id);

            $location                   = new Location();
            $location->name             = $request->name;
            $location->phone            = !empty($request->phone) ? $request->phone : '';
            $location->address          = $request->address;
            $location->description      = !empty($request->description) ? $request->description : '';
            $location->business_id      = !empty($business) ? $business->id : 0;
            $location->created_by       = creatorId();
            if ($request->hasFile('location_image'))
            {
                $filenameWithExt = $request->file('location_image')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('location_image')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($request,'location_image',$fileNameToStore,'Location');
                if($uplaod['flag'] == 1)
                {
                    $url = $uplaod['url'];
                }
                else
                {
                    return redirect()->back()->with('error',$uplaod['msg']);
                }
            }
            $location->image  = !empty($request->location_image) ? $url : '';
            $location->save();
            $tab = 1;
            return redirect()->back()->with('success', __('Location successfully created.'))->with('tab', $tab);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
        if(Auth::user()->isAbleTo('location edit'))
        {
            return view('location.edit',compact('location'));
        }
        else
        {
           return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        if(Auth::user()->isAbleTo('location edit'))
        {

            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'address' => 'required',
                    'phone' => 'required',
                    'description' => 'required',
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $location->name = $request->name;
            $location->address = $request->address;
            $location->phone = $request->phone;
            $location->description = $request->description;

            if ($request->hasFile('location_image'))
            {
                $filenameWithExt = $request->file('location_image')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('location_image')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $uplaod = upload_file($request,'location_image',$fileNameToStore,'Location');

                if($uplaod['flag'] == 1)
                {
                    if (!empty($location->image)) {
                        delete_file($location->image);
                    }
                    $url = $uplaod['url'];
                }
                else
                {
                    $tab = 1;
                    return redirect()->back()->with('error',$uplaod['msg'])->with('tab', $tab);
                }
                $location->image  = !empty($request->location_image) ? $url : '';
            }

            $location->save();
            $tab = 1;

            return redirect()->back()->with('success', __('Location updated successfully!'))->with('tab', $tab);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        if(Auth::user()->isAbleTo('location delete'))
        {
            if(!empty($location->image))
            {
                delete_file($location->image);
            }
            $location->delete();
            $tab = 1;
            return redirect()->back()->with('error', __('Location successfully delete.'))->with('tab', $tab);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }
}
