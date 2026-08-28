<?php

namespace Workdo\LandingPage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\LandingPage\Entities\LandingPageSetting;

class FaqController extends Controller
{
    public function index()
    {
        $settings = LandingPageSetting::settings();
        $faqs = json_decode($settings['faqs'], true) ?? [];


        return view('landing-page::landingpage.faq.index', compact('settings','faqs'));
    }


    public function create()
    {
        return view('landing-page::create');
    }


    public function store(Request $request)
    {
        if($request->faq_status){
            $faq_status = 'on';
        }else{
            $faq_status = 'off';
        }


        $data['faq_status']= $faq_status;
        $data['faq_title']= $request->faq_title;
        $data['faq_heading']= $request->faq_heading;
        $data['faq_description']= $request->faq_description;

        foreach($data as $key => $value){
            LandingPageSetting::updateOrCreate(['name' =>  $key],['value' => $value]);
        }

        return redirect()->back()->with(['success'=> 'Setting update successfully']);
    }


    public function show($id)
    {
        return view('landing-page::show');
    }


    public function edit($id)
    {
        return view('landing-page::edit');
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }
    public function faq_create(){
        $settings = LandingPageSetting::settings();
        return view('landing-page::landingpage.details.faq.create');
    }



    public function faq_store(Request $request){

        $settings = LandingPageSetting::settings();
        $data = json_decode($settings['faqs'], true);

        $datas['faq_questions']= $request->faq_questions;
        $datas['faq_answer']= $request->faq_answer;

        $data[] = $datas;
        $data = json_encode($data);
        LandingPageSetting::updateOrCreate(['name' =>  'faqs'],['value' => $data]);

        return redirect()->back()->with(['success'=> 'Faq add successfully']);
    }



    public function faq_edit($key){
        $settings = LandingPageSetting::settings();
        $faqs = json_decode($settings['faqs'], true);
        $faq = $faqs[$key];
        return view('landing-page::landingpage.details.faq.edit', compact('faq','key'));
    }



    public function faq_update(Request $request, $key){

        $settings = LandingPageSetting::settings();
        $data = json_decode($settings['faqs'], true);

        $data[$key]['faq_questions'] = $request->faq_questions;
        $data[$key]['faq_answer'] = $request->faq_answer;

        $data = json_encode($data);
        LandingPageSetting::updateOrCreate(['name' =>  'faqs'],['value' => $data]);

        return redirect()->back()->with(['success'=> 'Discover update successfully']);
    }



    public function faq_delete($key)
    {
        $settings = LandingPageSetting::settings();
        $pages = json_decode($settings['faqs'], true);
        unset($pages[$key]);
        LandingPageSetting::updateOrCreate(['name' =>  'faqs'],['value' => $pages]);
        return redirect()->back()->with(['success'=> 'Discover delete successfully']);
    }
}
