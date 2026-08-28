<?php

namespace App\Http\Controllers;

use App\Models\BusinessHoliday;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessHolidayController extends Controller
{

    public function index()
    {
        //
    }

    public function create(Request $request)
    {
        if(Auth::user()->isAbleTo('holiday create'))
        {
            $business = Business::find($request->business_id);
            return view('holiday.create',compact('business'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if(Auth::user()->isAbleTo('holiday create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                    'date' => 'required',
                    ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $business = Business::find($request->business_id);

            $businessholiday                   = new BusinessHoliday();
            $businessholiday->title             = $request->title;
            $businessholiday->date             = $request->date;
            $businessholiday->business_id      = !empty($business) ? $business->id : 0;
            $businessholiday->created_by       = creatorId();
            $businessholiday->save();
            $tab = 6;

            return redirect()->back()->with('success', __('Holiday successfully created.'))->with('tab', $tab);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(BusinessHoliday $businessHoliday)
    {
        if (Auth::user()->isAbleTo('holiday edit')) {
            return view('holiday.edit', compact('businessHoliday'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, BusinessHoliday $businessHoliday)
    {
        if(Auth::user()->isAbleTo('holiday edit')){
            $validator = \Validator::make($request->all(), [
                'title' => 'required',
                'date' => 'required',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->first());
            }

            $businessHoliday->title = $request->title;
            $businessHoliday->date = $request->date;
            $businessHoliday->save();
            $tab = 6;

            return redirect()->back()->with('success', __('Holiday successfully updated.'))->with('tab', $tab);
        }
        else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(BusinessHoliday $businessHoliday)
    {
        if(Auth::user()->isAbleTo('holiday delete')){
            $businessHoliday->delete();
            $tab = 6;
            return redirect()->back()->with('success', __('Holiday successfully deleted.'))->with('tab', $tab);
        }
        else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
