<?php

namespace App\Http\Controllers;

use App\Models\CustomStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(Auth::user()->isAbleTo('status manage'))
        {
            $statuses = CustomStatus::where('created_by',creatorId())->where('business_id',getActiveBusiness())->get();
            return view('custom_status.index',compact('statuses'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if(Auth::user()->isAbleTo('status create'))
        {
            return view('custom_status.create');
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
        if(Auth::user()->isAbleTo('status create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                    'status_color' => 'required',
                    ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }


            $customstatus                   = new CustomStatus();
            $customstatus->title            = $request->title;
            $customstatus->status_color     = $request->status_color;
            $customstatus->business_id      = getActiveBusiness();
            $customstatus->send_sms         = $request->send_sms ? 1 : 0;
            $customstatus->send_email       = $request->send_email ? 1 : 0;
            $customstatus->created_by       = creatorId();
            $customstatus->show_in_kanban    = $request->show_in_kanban ? 1 : 0;
            $customstatus->save();

            return redirect()->back()->with('success', __('Status successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomStatus $customStatus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomStatus $customStatus)
    {
        if(Auth::user()->isAbleTo('status update'))
        {
            return view('custom_status.edit',compact('customStatus'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomStatus $customStatus)
    {
        if(Auth::user()->isAbleTo('status update'))
        {

            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                    'status_color' => 'required',
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $customStatus->title        = $request->title;
            $customStatus->status_color = $request->status_color;
            $customStatus->send_sms     = $request->send_sms ? 1 : 0;
            $customStatus->send_email   = $request->send_email ? 1 : 0;
            $customStatus->show_in_kanban = $request->show_in_kanban ? 1 : 0;
            $customStatus->save();

            return redirect()->back()->with('success', __('Status updated successfully!'));

        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomStatus $customStatus)
    {
        if(Auth::user()->isAbleTo('status delete'))
        {
            $customStatus->delete();
            return redirect()->back()->with('error', __('Status successfully delete.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
