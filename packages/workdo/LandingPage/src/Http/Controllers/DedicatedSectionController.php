<?php

namespace Workdo\LandingPage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\LandingPage\Entities\LandingPageSetting;


class DedicatedSectionController extends Controller
{

    public function index()
    {
        if(\Auth::user()->isAbleTo('landingpage manage')){

            $settings = LandingPageSetting::settings();
            $dedicated_card_details = json_decode($settings['dedicated_card_details'], true) ?? [];
            $modules = getshowModuleList();
            return view('landing-page::landingpage.dedicated.index', compact('settings','dedicated_card_details'.'modules'));

        }else{
            return redirect()->back()->with('error',__('Permission Denied!'));

        }
    }


    public function create()
    {
        return view('landing-page::create');
    }


    public function dedicated_store(Request $request)
    {
        $data['dedicated_section_status']= isset($request->dedicated_section_status) ? 'on' : 'off';
        $data['dedicated_heading']= $request->dedicated_heading;
        $data['dedicated_description']= $request->dedicated_description;
        $data['dedicated_live_demo_link']= $request->dedicated_live_demo_link;
        $data['dedicated_link_button_text']= $request->dedicated_link_button_text;


        foreach($data as $key => $value){

            LandingPageSetting::updateOrCreate(['name' =>  $key],['value' => $value]);
        }

        return redirect()->back()->with(['success'=> 'Dedicated Section update successfully']);
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

    public function dedicated_card_create(){
        $settings = LandingPageSetting::settings();
        return view('landing-page::landingpage.details.dedicated.card_create');
    }



    public function dedicated_card_store(Request $request)
    {

        $settings = LandingPageSetting::settings();
        $data = json_decode($settings['dedicated_card_details'], true);

        if( $request->dedicated_card_logo){
            $dedicated_card_logo = time()."-dedicated_card_logo." . $request->dedicated_card_logo->getClientOriginalExtension();
            $path = upload_file($request,'dedicated_card_logo',$dedicated_card_logo,'landing_page_image',[]);
            if($path['flag']==0){
                return redirect()->back()->with('error', __($path['msg']));
            }
            $datas['dedicated_card_logo'] = $path['url'];
        }

        $datas['dedicated_card_heading']= $request->dedicated_card_heading;
        $datas['dedicated_card_description']= $request->dedicated_card_description;
        $datas['dedicated_card_more_details_link']= $request->dedicated_card_more_details_link;
        $datas['dedicated_card_more_details_button_text']= $request->dedicated_card_more_details_button_text;

        $data[] = $datas;
        $data = json_encode($data);
        LandingPageSetting::updateOrCreate(['name' =>  'dedicated_card_details'],['value' => $data]);

        return redirect()->back()->with(['success'=> 'Dedicated Card added successfully']);

    }



    public function dedicated_card_edit($key){

        $settings = LandingPageSetting::settings();
        $dedicated_cards = json_decode($settings['dedicated_card_details'], true);
        $dedicated_card = $dedicated_cards[$key];
        return view('landing-page::landingpage.details.dedicated.card_edit', compact('dedicated_card','key'));
    }



    public function dedicated_card_update(Request $request, $key)
    {

        $settings = LandingPageSetting::settings();
        $data = json_decode($settings['dedicated_card_details'], true);

        if( $request->dedicated_card_logo){
            $dedicated_card_logo = time()."-dedicated_card_logo." . $request->dedicated_card_logo->getClientOriginalExtension();
            $path = upload_file($request,'dedicated_card_logo',$dedicated_card_logo,'landing_page_image',[]);
            if($path['flag']==0){
                return redirect()->back()->with('error', __($path['msg']));
            }

            // old img delete
            if(!empty($data[$key]['dedicated_card_logo']) && strpos($data[$key]['dedicated_card_logo'],'avatar.png') == false && check_file($data[$key]['dedicated_card_logo']))
            {
                delete_file($data[$key]['dedicated_card_logo']);
            }

            $data[$key]['dedicated_card_logo'] = $path['url'];

        }

        $data[$key]['dedicated_card_heading'] = $request->dedicated_card_heading;
        $data[$key]['dedicated_card_description'] = $request->dedicated_card_description;
        $data[$key]['dedicated_card_more_details_link'] = $request->dedicated_card_more_details_link;
        $data[$key]['dedicated_card_more_details_button_text'] = $request->dedicated_card_more_details_button_text;

        $data = json_encode($data);
        LandingPageSetting::updateOrCreate(['name' =>  'dedicated_card_details'],['value' => $data]);

        return redirect()->back()->with(['success'=> 'Dedicated Card Updated successfully']);
    }



    public function dedicated_card_delete($key){

        $settings = LandingPageSetting::settings();
        $pages = json_decode($settings['dedicated_card_details'], true);
        unset($pages[$key]);
        LandingPageSetting::updateOrCreate(['name' =>  'dedicated_card_details'],['value' => $pages]);
        return redirect()->back()->with(['success'=> 'Dedicated Card delete successfully']);
    }
}
