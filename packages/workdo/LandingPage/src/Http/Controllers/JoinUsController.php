<?php

namespace Workdo\LandingPage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\LandingPage\Entities\JoinUs;
use Workdo\LandingPage\Entities\LandingPageSetting;

class JoinUsController extends Controller
{

    public function index()
    {
        if(Auth::user()->isAbleTo('landingpage manage')){

            $join_us = JoinUs::get();
            $settings = LandingPageSetting::settings();

            return view('landing-page::landingpage.newsletter.index', compact('join_us','settings'));
        }else{

            return redirect()->back()->with('error',__('Permission Denied!'));
        }
    }


    public function create()
    {
        return view('landing-page::create');
    }


    public function store(Request $request)
    {
        $data['joinus_status']  = $request->joinus_status;
        $data['joinus_heading']         = $request->joinus_heading;
        $data['joinus_description']     = $request->joinus_description;

        foreach($data as $key => $value){
            LandingPageSetting::updateOrCreate(['name' =>  $key],['value' => $value]);
        }

        return redirect()->back()->with(['success'=> 'Setting update successfully']);


    }

    public function show($id)
    {
        $join_us = JoinUs::get();
        return view('landing-page::landingpage.joinus' ,compact('join_us'));;
    }

    public function edit($id)
    {
        return view('landing-page::landingpage.joinus');
    }

    public function update(Request $request, $id)
    {
        //
    }




    //  join user store
    public function joinUsUserStore(Request $request){

        $validator = \Validator::make(
            $request->all(),
            [
                'email' => 'required|email|unique:join_us',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $join = new JoinUs;
        $join->email = $request->email;
        $join->save();

        return redirect()->back()->with(['success'=> 'You are joined with our community']);
    }

    public function destroy($id)
    {
        $join = JoinUs::find($id);
        $join->delete();

        return redirect()->back()->with(['success'=> 'Join Us email deleted successfully']);
    }

}
