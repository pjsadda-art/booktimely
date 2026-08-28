<?php

namespace Workdo\LandingPage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\LandingPage\Entities\LandingPageSetting;

class FeaturesController extends Controller
{

    public function index()
    {
        if (\Auth::user()->isAbleTo('landingpage manage')) {

            $settings = LandingPageSetting::settings();
            $feature_of_features = json_decode($settings['feature_of_features'], true) ?? [];
            $other_features = json_decode($settings['other_features'], true) ?? [];
            return view('landing-page::landingpage.features.index', compact('settings', 'feature_of_features', 'other_features'));
        } else {

            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function create()
    {
        return view('landing-page::create');
    }

    public function store(Request $request)
    {
        //
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


    // create feature modal popup
    public function feature_create(){
        $settings = LandingPageSetting::settings();
        return view('landing-page::landingpage.details.features.create');
    }

    // feature store
    public function feature_store(Request $request){

        $settings = LandingPageSetting::settings();
        $data = json_decode($settings['feature_of_features'], true);

        if( $request->feature_logo){
            $feature_logo = time()."-feature_logo." . $request->feature_logo->getClientOriginalExtension();
            $path = upload_file($request,'feature_logo',$feature_logo,'landing_page_image',[]);
            if($path['flag']==0){
                return redirect()->back()->with('error', __($path['msg']));
            }
            $datas['feature_logo'] = $path['url'];
        }

        $datas['feature_heading']= $request->feature_heading;
        $datas['feature_description']= $request->feature_description;
        $datas['feature_more_details_link']= $request->feature_more_details_link;
        $datas['feature_more_details_button_text']= $request->feature_more_details_button_text;

        $data[] = $datas;
        $data = json_encode($data);
        LandingPageSetting::updateOrCreate(['name' =>  'feature_of_features'],['value' => $data]);

        return redirect()->back()->with(['success'=> 'Feature add successfully']);
    }


    // feature edit
    public function feature_edit($key)
    {
        $settings = LandingPageSetting::settings();
        $features = json_decode($settings['feature_of_features'], true);
        $feature = $features[$key];
        return view('landing-page::landingpage.details.features.edit', compact('feature', 'key'));
    }

    // feature update
    public function feature_update(Request $request, $key)
    {

        $settings = LandingPageSetting::settings();
        $data = json_decode($settings['feature_of_features'], true);

        if ($request->feature_logo) {
            $feature_logo = time() . "-feature_logo." . $request->feature_logo->getClientOriginalExtension();
            $dir        = 'uploads/landing_page_image';
            $path = upload_file($request, 'feature_logo', $feature_logo, 'landing_page_image', []);
            if ($path['flag'] == 0) {
                return redirect()->back()->with('error', __($path['msg']));
            }
            // old img delete
            if (!empty($data[$key]['feature_logo']) && strpos($data[$key]['feature_logo'], 'avatar.png') == false && check_file($data[$key]['feature_logo'])) {
                delete_file($data[$key]['feature_logo']);
            }
            $data[$key]['feature_logo'] = $path['url'];
        }

        $data[$key]['feature_heading'] = $request->feature_heading;
        $data[$key]['feature_description'] = $request->feature_description;
        $data[$key]['feature_more_details_link'] = $request->feature_more_details_link;
        $data[$key]['feature_more_details_button_text'] = $request->feature_more_details_button_text;

        $data = json_encode($data);
        LandingPageSetting::updateOrCreate(['name' =>  'feature_of_features'], ['value' => $data]);

        return redirect()->back()->with(['success' => 'Feature update successfully']);
    }

    // feature delete
    public function feature_delete($key)
    {
        $settings = LandingPageSetting::settings();
        $pages = json_decode($settings['feature_of_features'], true);
        unset($pages[$key]);
        LandingPageSetting::updateOrCreate(['name' =>  'feature_of_features'], ['value' => $pages]);
        return redirect()->back()->with(['success' => 'Feature delete successfully']);
    }

    public function feature_highlight_store(Request $request){

        if( $request->highlight_feature_image){
            $highlight_feature_image = "highlight_feature_image." . $request->highlight_feature_image->getClientOriginalExtension();
            $dir        = 'uploads/landing_page_image';
            $path = upload_file($request,'highlight_feature_image',$highlight_feature_image,"landing_page_image",[]);
            if($path['flag']==0){
                return redirect()->back()->with('error', __($path['msg']));
            }
            $data['highlight_feature_image'] = $path['url'];
        }

        $data['highlight_feature_heading']= $request->highlight_feature_heading;
        $data['highlight_feature_description']= $request->highlight_feature_description;


        foreach($data as $key => $value){

            LandingPageSetting::updateOrCreate(['name' =>  $key],['value' => $value]);
        }

        return redirect()->back()->with(['success'=> 'Setting update successfully']);
    }


    // Feature Sections create
    public function features_create()
    {
        $settings = LandingPageSetting::settings();
        return view('landing-page::landingpage.details.features.features_create');
    }

    public function features_store(Request $request)
    {

        $settings = LandingPageSetting::settings();
        $data = json_decode($settings['other_features'], true);

        if ($request->other_features_image) {
            $other_features_image = time() . "-other_features_image." . $request->other_features_image->getClientOriginalExtension();
            $path = upload_file($request, 'other_features_image', $other_features_image, 'landing_page_image', []);
            if ($path['flag'] == 0) {
                return redirect()->back()->with('error', __($path['msg']));
            }
            $datas['other_features_image'] = $path['url'];
        } else {
        }

        $datas['other_features_tag'] = $request->other_features_tag;
        $datas['other_features_heading'] = $request->other_features_heading;
        $datas['other_featured_description'] = $request->other_featured_description;
        $datas['other_feature_buy_now_link'] = $request->other_feature_buy_now_link;
        $datas['cards'] = $request->cards;


        $data[] = $datas;
        $data = json_encode($data);
        LandingPageSetting::updateOrCreate(['name' =>  'other_features'], ['value' => $data]);

        return redirect()->back()->with(['success' => 'Feature add successfully']);
    }

    public function features_edit($key)
    {
        $settings = LandingPageSetting::settings();
        $other_features = json_decode($settings['other_features'], true);
        $other_features = $other_features[$key];
        return view('landing-page::landingpage.details.features.features_edit', compact('other_features', 'key'));
    }



    public function features_update(Request $request, $key)
    {


        $settings = LandingPageSetting::settings();
        $data = json_decode($settings['other_features'], true);

        if ($request->other_features_image) {
            $other_features_image = time() . "-other_features_image." . $request->other_features_image->getClientOriginalExtension();
            $dir        = 'uploads/landing_page_image';
            $path = upload_file($request, 'other_features_image', $other_features_image, 'landing_page_image', []);
            if ($path['flag'] == 0) {
                return redirect()->back()->with('error', __($path['msg']));
            }
            // old img delete
            if (!empty($data[$key]['other_features_image']) && strpos($data[$key]['other_features_image'], 'avatar.png') == false && check_file($data[$key]['other_features_image'])) {
                delete_file($data[$key]['other_features_image']);
            }

            $data[$key]['other_features_image'] = $path['url'];
        }

        $data[$key]['other_features_tag'] = $request->other_features_tag;
        $data[$key]['other_features_heading'] = $request->other_features_heading;
        $data[$key]['other_featured_description'] = $request->other_featured_description;
        $data[$key]['other_feature_buy_now_link'] = $request->other_feature_buy_now_link;
        $data[$key]['cards'] = $request->cards;

        $data = json_encode($data);
        LandingPageSetting::updateOrCreate(['name' =>  'other_features'], ['value' => $data]);

        return redirect()->back()->with(['success' => 'Feature update successfully']);
    }

    public function features_delete($key)
    {
        $settings = LandingPageSetting::settings();
        $pages = json_decode($settings['other_features'], true);
        unset($pages[$key]);
        LandingPageSetting::updateOrCreate(['name' =>  'other_features'], ['value' => $pages]);
        return redirect()->back()->with(['success' => 'Features delete successfully']);
    }
}
