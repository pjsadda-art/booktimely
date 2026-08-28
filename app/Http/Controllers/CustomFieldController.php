<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\Business;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomFieldController extends Controller
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
    }



    public function CustomFieldSetting($id, Request $request)
    {
        if (Auth::user()->isAbleTo('business update')) {
            $business = Business::find($id);

            $validator = \Validator::make(
                $request->all(), [
                    'labels' => 'required|array',
                    'types' => 'required',
                    'ids' => 'nullable|array',
                    'ids.*' => 'nullable|exists:custom_fields,id',
                    'options' => 'nullable|array',                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $options = [];

            // Merge customoptions into options array
            if(!empty($request->customoptions)){
                foreach ($request->customoptions as $customoption) {
                    $options[] = json_decode($customoption, true);
                }
            }
            // Merge options into options array
            if(!empty($request->options)){

            foreach ($request->options as $option) {
                $options[] = $option;
            }
            }

            foreach ($request->labels as $key => $label) {
                $customFieldId = $request->ids[$key] ?? null;

                CustomField::UpdateOrCreate(
                    ['id' => $customFieldId],
                    [
                        'label' => $label,
                        'value' => $request->values[$key] ?? null,
                        'type' => $request->types[$key],
                        'business_id' => $business->id,
                        'created_by' => $business->created_by,
                        'option' => isset($options[$key]) ? json_encode($options[$key]) : NULL, // Store merged options as JSON string
                    ]
                );
            }


            $data = [
                'key' => 'custom_field_enable',
                'business' => $business->id,
                'created_by' => $business->created_by,
            ];

            $customFieldEnableValue = $request->custom_field_enable ? $request->custom_field_enable : 'off';
            Setting::updateOrInsert($data, ['value' => $customFieldEnableValue]);

            // Settings Cache forget
            comapnySettingCacheForget();

            $tab = 10;
            return redirect()->back()->with('success', __('Custom field setting successfully created.'))->with('tab', $tab);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(CustomField $customField)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomField $customField)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomField $customField)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $customField  = CustomField::find($request->id);
        if ($customField) {
            $customField->delete();
            return response()->json(['success' => true, 'message' => 'Custom field deleted successfully.']);
        }
        else{

            return response()->json(['error' => false, 'message' => 'Custom field not found.'])   ;
        }
    }
}
