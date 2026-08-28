<?php

namespace Workdo\FlexibleDays\Http\Controllers;

use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\FlexibleDays\Entities\FlexibleDayBreaks;
use Workdo\FlexibleDays\Entities\FlexibleDays;

class FlexileDayBreaksController extends Controller
{

    public function store(Request $request)
    {
        try {
            $rules = [
                'break.start' => 'required',
                'break.end' => 'required|after:break.start',
            ];
             // Custom validation rule to check if the break time already exists
            $rules['break.start'] .= '|unique:flexible_day_breaks,break_hours->start,NULL,id,flexible_days_id,' . $request->flexible_days_id;
            $rules['break.end'] .= '|unique:flexible_day_breaks,break_hours->end,NULL,id,flexible_days_id,' . $request->flexible_days_id;

            $messages = [
                'break.start.required' => 'Start time is required.',
                'break.end.required' => 'End time is required.',
                'break.end.after' => 'End time must be after start time.',
                'break.start.unique' => 'This break time already exists.',
                'break.end.unique' => 'This break time already exists.',
            ];
            $validator = \Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()]);
            }

            $flexible_break = FlexibleDayBreaks::updateOrCreate(
                [
                    'id' => $request->flexible_break_id
                ],
                [
                    'flexible_days_id' => $request->flexible_days_id,
                    'break_hours' => json_encode($request->break),
                    'business_id' => getActiveBusiness(),
                    'created_by' => creatorId(),
                ]
            );
            return response()->json(['success' => __('The break time has been added successfully.'), 'flexible_break' => $flexible_break]);
        } catch (Exception $e) {
            \Log::info([$e]);
            return response()->json(['error' => __("Something wen't wrong.")]);
        }
    }

    public function edit($id)
    {
        if ($id) {
            $flexible_break = FlexibleDayBreaks::find($id);
            return response()->json($flexible_break);
        } else {
            return response()->json(['error' => __('Not Found')]);
        }
    }

    public function destroy($id)
    {
        try{
            $flexible_break = FlexibleDayBreaks::where('id',$id)->delete();
            return response()->json(['success' => __('The break has been deleted.')]);
        }catch(Exception $e){
            return response()->json(['error' => __('Something went wrong')]);
        }
    }
}
