<?php

namespace App\Http\Controllers;

use App\Models\BusinessHours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessHoursController extends Controller
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $post = $request->all();
        $business_id =$post['business_id'];
        unset($post['_token'],$post['business_id']);

        foreach ($post as $key => $daydata) {
            $dayname = $key;
            $start = $daydata['start'] ?? "9:30";
            $end = $daydata['end'] ?? "18:00";
            $day_off = $daydata['day_off'] ?? 'off';
            $repeater = json_encode(isset($daydata['repeater']) ? $daydata['repeater'] :'') ;
            
            BusinessHours::updateOrCreate(['day_name' => $dayname,'business_id' => $business_id ,'created_by'=>creatorId()],['start_time'=>$start,'end_time'=>$end, 'break_hours'=>$repeater ,'day_off' =>$day_off]);

        }
        $tab = 4;
        return redirect()->back()->with('success', __('Business hours successfully created.'))->with('tab', $tab);
    }

    /**
     * Display the specified resource.
     */
    public function show(BusinessHours $businessHours)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusinessHours $businessHours)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusinessHours $businessHours)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusinessHours $businessHours)
    {
        //
    }
}
