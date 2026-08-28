<?php

namespace App\Http\Controllers;

use App\Models\Subscribe;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DataTables\SubscribesDataTable;

class SubscribeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, SubscribesDataTable $dataTable)
    {
        if (Auth::user()) {
            if (Auth::user()->isAbleTo('subscriber manage')) {
                $business       = Business::find(($request->business) ? $request->business : getActiveBusiness());
                if (!empty($business)) {
                    return $dataTable->render('Subscribe.index', compact('business'));
                } else {
                    return abort(404);
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
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
        $validator = \Validator::make(
            $request->all(),
            [
                'email' => 'required',
                'theme' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $Subscribe                    = new Subscribe();
        $Subscribe->email             = $request->email;
        $Subscribe->theme             = $request->theme;
        $Subscribe->business_id       = $request->business;
        $Subscribe->save();

        return redirect()->back()->with('success', __('Subscribe successfully submit.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscribe $subscribe)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscribe $subscribe)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscribe $subscribe)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscribe $subscribe)
    {
        if (Auth::user()->isAbleTo('subscriber delete')) {
            $subscribe->delete();
            return redirect()->back()->with('error', __('Subscriber successfully delete.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
