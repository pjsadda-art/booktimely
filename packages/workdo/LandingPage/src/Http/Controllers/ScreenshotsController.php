<?php

namespace Workdo\LandingPage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\LandingPage\Entities\LandingPageSetting;


class ScreenshotsController extends Controller
{

    public function index()
    {
        if(\Auth::user()->isAbleTo('landingpage manage')){

            $settings = LandingPageSetting::settings();
            $screenshots = json_decode($settings['screenshots'], true) ?? [];
            return view('landing-page::landingpage.screenshots.index',compact('settings','screenshots'));

        }else{

            return redirect()->back()->with('error',__('Permission Denied!'));
        }
    }


    public function create()
    {
        return view('landing-page::landingpage.screenshots.create');
    }


    public function store(Request $request)
    {
        $data['screenshots_status']= 'on';
        $data['screenshots_heading']= $request->screenshots_heading;
        $data['screenshots_description']= $request->screenshots_description;

        foreach($data as $key => $value){
            LandingPageSetting::updateOrCreate(['name' =>  $key],['value' => $value]);
        }

        return redirect()->back()->with(['success'=> 'Setting update successfully']);
    }


    public function show($id)
    {
        return view('landing-page::landingpage.screenshots.show');
    }


    public function edit($id)
    {
        return view('landing-page::landingpage.screenshots.edit');
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }

    public function screenshots_create(){
        $settings = LandingPageSetting::settings();
        return view('landing-page::landingpage.details.screenshots.create');
    }



    public function screenshots_store(Request $request){

        $settings = LandingPageSetting::settings();
        $data = json_decode($settings['screenshots'], true);

        if( $request->screenshots){
            $screenshots = time()."-screenshots." . $request->screenshots->getClientOriginalExtension();
            $dir        = 'uploads/landing_page_image';
            $path = upload_file($request,'screenshots',$screenshots,'landing_page_image',[]);
            if($path['flag']==0){
                return redirect()->back()->with('error', __($path['msg']));
            }
            $datas['screenshots'] = $path['url'];
        }

        $datas['screenshots_heading']= $request->screenshots_heading;

        $data[] = $datas;
        $data = json_encode($data);
        LandingPageSetting::updateOrCreate(['name' =>  'screenshots'],['value' => $data]);

        return redirect()->back()->with(['success'=> 'screenshots add successfully']);
    }



    public function screenshots_edit($key){
        $settings = LandingPageSetting::settings();
        $screenshots = json_decode($settings['screenshots'], true);
        $screenshot = $screenshots[$key];
        return view('landing-page::landingpage.details.screenshots.edit', compact('screenshot','key'));
    }



    public function screenshots_update(Request $request, $key){

        $settings = LandingPageSetting::settings();
        $data = json_decode($settings['screenshots'], true);

        if( $request->screenshots){
            $screenshots = time()."-screenshots." . $request->screenshots->getClientOriginalExtension();
            $dir        = 'uploads/landing_page_image';
            $path = upload_file($request,'screenshots',$screenshots,'landing_page_image',[]);
            if($path['flag']==0){
                return redirect()->back()->with('error', __($path['msg']));
            }
            // old img delete
            if(!empty($data[$key]['screenshots']) && strpos($data[$key]['screenshots'],'avatar.png') == false && check_file($data[$key]['screenshots']))
            {
                delete_file($data[$key]['screenshots']);
            }
            $data[$key]['screenshots'] = $path['url'];
        }

        $data[$key]['screenshots_heading'] = $request->screenshots_heading;


        $data = json_encode($data);
        LandingPageSetting::updateOrCreate(['name' =>  'screenshots'],['value' => $data]);

        return redirect()->back()->with(['success'=> 'screenshots update successfully']);
    }



    public function screenshots_delete($key){

        $settings = LandingPageSetting::settings();
        $pages = json_decode($settings['screenshots'], true);
        unset($pages[$key]);
        LandingPageSetting::updateOrCreate(['name' =>  'screenshots'],['value' => $pages]);

        return redirect()->back()->with(['success'=> 'Screenshots delete successfully']);
    }
}
