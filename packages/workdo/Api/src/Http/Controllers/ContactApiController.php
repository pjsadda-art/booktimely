<?php

namespace Workdo\Api\Http\Controllers;

use App\Models\Business;
use App\Models\ContactUs;
use App\Traits\ApiResponser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ContactApiController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $business = Business::find(getActiveBusiness());
        $contact = ContactUs::where('business_id', $business->id)->get();
        if (!empty($contact)) {
            return $this->success($contact);
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
                'email' => 'required',
                'contact' => 'required',
                'subject' => 'required',
                'message' => 'required',
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
        $contact                    = new ContactUs();
        $contact->name              = $request->name;
        $contact->email             = $request->email;
        $contact->contact           = $request->contact;
        $contact->subject           = $request->subject;
        $contact->description       = $request->message;
        $contact->theme             = $request->theme;
        $contact->business_id      = !empty($business) ? $business->id : 0;
        $contact->save();

        return $this->success(['message' => 'Contact successfully created.']);
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
        $contact = ContactUs::find($id);
        $contact->delete();
        return $this->success(['message' => 'Contact successfully delete.']);
    }
}
