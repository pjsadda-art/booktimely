<?php

namespace Workdo\LandingPage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\LandingPage\Entities\LandingPageSetting;

class ReviewController extends Controller
{
  
    public function index()
    {
        return view('landing-page::index');
    }


    public function review_create(){
        $settings = LandingPageSetting::settings();
        return view('landing-page::landingpage.details.reviews.create');
    }

   
    public function review_store(Request $request){

        $settings = LandingPageSetting::settings();
        $data = json_decode($settings['reviews'], true);

        $datas['review_header_tag']= $request->review_header_tag;
        $datas['review_heading']= $request->review_heading;
        $datas['review_description']= $request->review_description;
        $datas['review_live_demo_link']= $request->review_live_demo_link;
        $datas['review_live_demo_button_text']= $request->review_live_demo_button_text;

        $data[] = $datas;
        $data = json_encode($data);
        LandingPageSetting::updateOrCreate(['name' =>  'reviews'],['value' => $data]);

        return redirect()->back()->with(['success'=> 'Review add successfully']);
    }
    
    public function show($id)
    {
        return view('landing-page::show');
    }

  
    public function review_edit($key)
    {
        $settings = LandingPageSetting::settings();
        $reviews = json_decode($settings['reviews'], true);
        $review = $reviews[$key];
        return view('landing-page::landingpage.details.reviews.edit', compact('review', 'key'));
    }
   
    public function review_update(Request $request, $key)
    {

        $settings = LandingPageSetting::settings();
        $data = json_decode($settings['reviews'], true);

        $data[$key]['review_header_tag'] = $request->review_header_tag;
        $data[$key]['review_heading'] = $request->review_heading;
        $data[$key]['review_description'] = $request->review_description;
        $data[$key]['review_live_demo_link'] = $request->review_live_demo_link;
        $data[$key]['review_live_demo_button_text'] = $request->review_live_demo_button_text;

        $data = json_encode($data);
        LandingPageSetting::updateOrCreate(['name' =>  'reviews'], ['value' => $data]);

        return redirect()->back()->with(['success' => 'Review update successfully']);
    }

  
    public function review_delete($key)
    {
        $settings = LandingPageSetting::settings();
        $pages = json_decode($settings['reviews'], true);
        unset($pages[$key]);
        LandingPageSetting::updateOrCreate(['name' =>  'reviews'], ['value' => $pages]);
        return redirect()->back()->with(['success' => 'review delete successfully']);
    }
}
