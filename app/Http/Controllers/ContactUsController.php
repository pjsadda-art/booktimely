<?php

namespace App\Http\Controllers;

use App\Models\ContactUs;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\DataTables\ContactsDataTable;

class ContactUsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ContactsDataTable $dataTable)
    {
        if (Auth::user()) {
            if (Auth::user()->isAbleTo('contact manage')) {
                $business = Business::find(($request->business) ? $request->business : getActiveBusiness());
                if (!empty($business)) {
                    return $dataTable->render('contact.index', compact('business'));
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
                'name' => 'required',
                'email' => 'required',
                'contact' => 'required',
                'subject' => 'required',
                'message' => 'required',
                'theme' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $contactus                    = new ContactUs();
        $contactus->name              = $request->name;
        $contactus->email             = $request->email;
        $contactus->contact           = $request->contact;
        $contactus->subject           = $request->subject;
        $contactus->description       = $request->message;
        $contactus->theme             = $request->theme;
        $contactus->business_id       = $request->business;
        $contactus->save();

        return redirect()->back()->with('success', __('Contact successfully submitted.'));
    }


    public function description($id)
    {
        $desc = ContactUs::find($id);
        return view('contact.description', compact('desc'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ContactUs $contactUs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContactUs $contactUs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContactUs $contactUs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactUs $contactUs, $id)
    {
        if (Auth::user()->isAbleTo('contact delete')) {
            $contactUs = ContactUs::find($id);
            $contactUs->delete();
            return redirect()->back()->with('error', __('Contact successfully delete.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
