<?php

namespace Workdo\LandingPage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\LandingPage\Entities\LandingPageSetting;
use Workdo\LandingPage\Entities\Pixel;

class LandingPageController extends Controller
{

    public function index()
    {
        if (Auth::user()->isAbleTo('landingpage manage')) {

            $settings = LandingPageSetting::settings();
            $screenshots                = json_decode($settings['screenshots'], true) ?? [];
            $pages                      = json_decode($settings['menubar_page'], true);
            $reviews                    = json_decode($settings['reviews'], true) ?? [];
            $other_features             = json_decode($settings['other_features'], true) ?? [];
            $feature_of_features        = json_decode($settings['feature_of_features'], true) ?? [];
            $buildtech_card_details     = json_decode($settings['buildtech_card_details'], true) ?? [];
            $dedicated_card_details     = json_decode($settings['dedicated_card_details'], true) ?? [];
            $footer_sections_details    = json_decode($settings['footer_sections_details'], true) ?? [];
            $faqs = json_decode($settings['faqs'], true) ?? [];

            return view('landing-page::landingpage.details.index', compact('settings', 'faqs', 'other_features', 'feature_of_features', 'screenshots', 'pages', 'reviews', 'buildtech_card_details', 'dedicated_card_details', 'footer_sections_details'));
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
        $data = [
            "topbar_notification_msg" =>  $request->topbar_notification_msg,
        ];

        foreach ($data as $key => $value) {

            LandingPageSetting::updateOrCreate(['name' =>  $key], ['value' => $value]);
        }

        return redirect()->back()->with(['success' => 'Topbar setting update successfully']);
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
    public function getInfoImages(Request $request, $slug = null, $section = "")
    {
        return view('landing-page::layouts.infoimages', compact('slug', 'section'));
    }

    public function customJsCssSetting(Request $request)
    {

        $data['landingpage_custom_css']  = $request->landingpage_custom_css;
        $data['landingpage_custom_js']   = $request->landingpage_custom_js;

        $check = LandingPageSetting::saveSettings($data);

        if ($check) {
            return redirect()->back()->with(['success' => 'Custom js & css setting saved successfully']);
        } else {
            return redirect()->back()->with(['error' => 'Something went wrong']);
        }
    }

    public function changeBlocksStoreAjax(Request $request)
    {
        $data = $request->all();
        // Add other switch values as needed
        $check = LandingPageSetting::saveSettings($data);

        $stages = [
            'is_top_bar_active'                 => 'Top bar',
            'is_banner_section_active'          => 'Banner',
            'is_features_section_active'        => 'Features',
            'is_reviews_section_active'         => 'Reviews',
            'is_screenshots_section_active'     => 'Screenshots',
            'is_dedicated_section_active'       => 'Dedicated',
            'is_faq_section_active'             => 'FAQ',
            'is_buildtech_section_active'       => 'BuildTech',
            'is_package_details_section_active' => 'Package Details',
        ];

        $msg = $stages[array_key_first($data)] . ' section ' . (array_values($data)[0] == 'on' ? 'enable' : 'disable') . ' successfully.';

        if ($check) {
            return response()->json(['success' => $msg]);
        } else {
            return response()->json(['error' => 'Something went wrong']);
        }
    }


    // change block controller

    public function changeBlocks()
    {
        $stages = [
            'is_top_bar_active'             => 'Top bar',
            'is_banner_section_active'      => 'Banner',
            'is_features_section_active'    => 'Features',
            'is_reviews_section_active'     => 'Reviews',
            'is_screenshots_section_active' => 'Screenshots',
            'is_dedicated_section_active'   => 'Dedicated',
            'is_package_details_section_active'   => 'Package Details',
            'is_faq_section_active'         => 'FAQ',
            'is_buildtech_section_active'   => 'BuildTech',
            // 'is_pricing_plan_section_active' => 'Pricing Plan',
        ];

        $settings = LandingPageSetting::settings();

        return view('landing-page::landingpage.change_blocks.index', compact('settings', 'stages'));
    }

    public function changeBlocksStore(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'is_banner_section_active' => 'required',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $data['landing_page_section_sequence']  = $request->landing_page_section_sequence;

        $data['is_top_bar_active']              = isset($request->is_top_bar_active) ? 'on' : 'off';
        $data['is_banner_section_active']       = isset($request->is_banner_section_active) ? 'on' : 'off';
        $data['is_features_section_active']     = isset($request->is_features_section_active) ? 'on' : 'off';
        $data['is_reviews_section_active']      = isset($request->is_reviews_section_active) ? 'on' : 'off';
        $data['is_screenshots_section_active']  = isset($request->is_screenshots_section_active) ? 'on' : 'off';
        $data['is_dedicated_section_active']    = isset($request->is_dedicated_section_active) ? 'on' : 'off';
        $data['is_faq_section_active']          = isset($request->is_faq_section_active) ? 'on' : 'off';
        $data['is_pricing_plan_section_active'] = isset($request->is_pricing_plan_section_active) ? 'on' : 'off';
        $data['is_buildtech_section_active']    = isset($request->is_buildtech_section_active) ? 'on' : 'off';
        $data['is_package_details_section_active']    = isset($request->is_package_details_section_active) ? 'on' : 'off';

        $check = LandingPageSetting::saveSettings($data);

        if ($check) {
            return redirect()->back()->with(['success' => 'Change Blocks setting saved successfully']);
        } else {
            return redirect()->back()->with(['error' => 'Something went wrong']);
        }
    }

    // end

    // seo controller
    public function seo()
    {
        $settings = LandingPageSetting::settings();
        // $settings = getAdminAllSetting();

        $admin_settings = getAdminAllSetting();
        $pixels = Pixel::all();
        $pixals_platforms = LandingPageSetting::pixel_plateforms();

        return view('landing-page::landingpage.seo.index', compact('settings', 'admin_settings', 'pixels', 'pixals_platforms'));
    }

    public function seoSetting(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                // 'meta_title'        => 'required|string',
                'meta_keywords'     => 'required|string',
                'meta_description'  => 'required|string',
                'google_analytics'  => 'required|string',
                'facebook_pixel'  => 'required|string',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        if ($request->hasFile('meta_image')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'meta_image'        => 'mimes:jpeg,jpg,png,gif',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $filenameWithExt = $request->file('meta_image')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('meta_image')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $uplaod = upload_file($request, 'meta_image', $fileNameToStore, 'meta');

            if ($uplaod['flag'] == 1) {
                // old img delete
                $settings = getAdminAllSetting();
                if ((!empty($settings['meta_image'])) && strpos($settings['meta_image'], 'meta_image.png') == false && check_file($settings['meta_image'])) {
                    delete_file($settings['meta_image']);
                }
            } else {
                return redirect()->back()->with('error', $uplaod['msg']);
            }
        }

        try {
            $post = $request->all();
            unset($post['_token'], $post['_method']);
            if ((isset($uplaod)) && ($uplaod['flag'] == 1) && (!empty($uplaod['url']))) {
                $post['meta_image'] = $uplaod['url'];
            }

            foreach ($post as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'name' => $key,
                ];

                // Check if the record exists, and update or insert accordingly
                LandingPageSetting::updateOrInsert($data, ['value' => $value]);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __('SEO setting successfully updated.'));
    }
    // end

    // Pwa
    public function pwa()
    {
        $settings = LandingPageSetting::settings();
        // $settings = getAdminAllSetting();
        $pwa = LandingPageSetting::pwa_store();
        $admin_settings = getAdminAllSetting();

        return view('landing-page::landingpage.pwa.index', compact('settings', 'admin_settings', 'pwa'));
    }

    public function pwaSetting(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'pwa_app_title'             => 'required',
                'pwa_app_name'              => 'required',
                'pwa_app_theme_color'       => 'required',
                'pwa_app_background_color'  => 'required',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $data['is_pwa_store_active']        = isset($request->is_pwa_store_active) ? 'on' : 'off';

        $company_favicon = (!empty(admin_setting('favicon')) && check_file(admin_setting('favicon'))) ? get_file(admin_setting('favicon')) : get_file('uploads/logo/favicon.png');

        $lang = getActiveLanguage();
        $start_url = env('APP_URL');

        $manifest = '{
                        "lang": "' . $lang . '",
                        "name": "' . $request->pwa_app_title . '",
                        "short_name": "' . $request->pwa_app_name . '",
                        "start_url": "' . $start_url . '",
                        "display": "standalone",
                        "background_color": "' . $request->pwa_app_background_color . '",
                        "theme_color": "' . $request->pwa_app_theme_color . '",
                        "orientation": "portrait",
                        "categories": [
                            "shopping"
                        ],
                        "icons": [
                            {
                                "src": "' . $company_favicon . '",
                                "sizes": "128x128",
                                "type": "image/png",
                                "purpose": "any"
                            },
                            {
                                "src": "' . $company_favicon . '",
                                "sizes": "144x144",
                                "type": "image/png",
                                "purpose": "any"
                            },
                            {
                                "src": "' . $company_favicon . '",
                                "sizes": "152x152",
                                "type": "image/png",
                                "purpose": "any"
                            },
                            {
                                "src": "' . $company_favicon . '",
                                "sizes": "192x192",
                                "type": "image/png",
                                "purpose": "any"
                            },
                            {
                                "src": "' . $company_favicon . '",
                                "sizes": "256x256",
                                "type": "image/png",
                                "purpose": "any"
                            },
                            {
                                "src": "' . $company_favicon . '",
                                "sizes": "512x512",
                                "type": "image/png",
                                "purpose": "any"
                            },
                            {
                                "src": "' . $company_favicon . '",
                                "sizes": "1024x1024",
                                "type": "image/png",
                                "purpose": "any"
                            }
                        ]
                    }';


        $dir = ("uploads/customer_app");
        $filePath = $dir . '/manifest.json';

        // Create the directory if it doesn't exist
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        // Write the manifest filec
        $x = \File::put($filePath, $manifest);

        // Set the permissions for the directory and file
        // chmod($dir, 0777);
        if (!is_writable($dir)) {
            error_log("Directory is not writable: $dir");
        }

        if (!is_writable($filePath)) {
            error_log("File is not writable: $filePath");
        }
        // chmod($filePath, 0666);
        $check = LandingPageSetting::saveSettings($data);

        if ($check) {
            return redirect()->back()->with(['success' => 'PWA setting saved successfully']);
        } else {
            return redirect()->back()->with(['error' => 'Something went wrong']);
        }
    }
    // end pwa

    // functions For Cookie
    public function cookie()
    {
        $settings = LandingPageSetting::settings();
        // $settings = getAdminAllSetting();
        $admin_settings = getAdminAllSetting();

        return view('landing-page::landingpage.cookie.index', compact('settings', 'admin_settings'));
    }

    public function CookieSetting(Request $request)
    {
        if ($request->has('enable_cookie')) {
            $validator = \Validator::make($request->all(), [
                'cookie_title' => 'required',
                'cookie_description' => 'required',
                'strictly_cookie_title' => 'required',
                'strictly_cookie_description' => 'required',
                'more_information_description' => 'required',
                'contactus_url' => 'required',
            ]);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
        }


        if ($request->has('enable_cookie')) {
            $post = $request->all();
            unset($post['_token'], $post['_method']);

            $post['cookie_logging'] = isset($request->cookie_logging) ? $request->cookie_logging : 'off';
            foreach ($post as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'name' => $key,
                ];

                // Check if the record exists, and update or insert accordingly
                LandingPageSetting::updateOrInsert($data, ['value' => $value]);
            }
        } else {
            // Define the data to be updated or inserted
            $data = [
                'name' => 'enable_cookie',
            ];

            // Check if the record exists, and update or insert accordingly
            LandingPageSetting::updateOrInsert($data, ['value' => 'off']);
        }

        return redirect()->back()->with('success', 'Cookie setting save successfully.');
    }
    //end



    // Functions for QR code
    public function qrCode()
    {
        $settings       = LandingPageSetting::settings();
        $qr_code        = LandingPageSetting::qr_code();
        $admin_settings = getAdminAllSetting();

        return view('landing-page::landingpage.qr_code.index', compact('settings', 'admin_settings', 'qr_code'));
    }

    public function qrCodeStore(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'foreground_color'  => 'required',
            'background_color'  => 'required',
            'qr_text_color'     => 'required',
            'radius'            => 'required',
            'qr_type'           => 'required',
            'size'              => 'required',
        ]);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        if ($request->hasFile('image')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'image'        => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $filenameWithExt = $request->file('image')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $uplaod = upload_file($request, 'image', $fileNameToStore, 'qrcode');

            if ($uplaod['flag'] == 1) {
                // old img delete
                $settings = getAdminAllSetting();
                if ((!empty($settings['image'])) && strpos($settings['image'], 'image.png') == false && check_file($settings['image'])) {
                    delete_file($settings['image']);
                }
            } else {
                return redirect()->back()->with('error', $uplaod['msg']);
            }
        }
        $data = [];

        if ((isset($uplaod)) && ($uplaod['flag'] == 1) && (!empty($uplaod['url']))) {
            $fileName = $uplaod['url'];
        }

        $data['foreground_color'] = $request->filled('foreground_color') ? $request->foreground_color : '#000000';
        $data['background_color'] = $request->filled('background_color') ? $request->background_color : '#ffffff';
        $data['radius']           = $request->filled('radius') ? $request->radius : 26;
        $data['qr_type']          = $request->filled('qr_type') ? $request->qr_type : 0;
        $data['qr_text']          = $request->filled('qr_text') ? $request->qr_text : 'BookingGo';
        $data['qr_text_color']    = $request->filled('qr_text_color') ? $request->qr_text_color : '#f50a0a';
        $data['size']             = $request->filled('size') ? $request->size : 9;
        $data['image']            = isset($fileName) ? $fileName : null;

        $check = LandingPageSetting::saveSettings($data);

        return redirect()->back()->with('success', 'QR Code setting save successfully.');
    }

    public function downloadqr(Request $request)
    {
        $view = view('landing-page::landingpage.qr_code.QR')->render();

        $data['success'] = true;
        $data['data'] = $view;
        return $data;
    }
    // End
}
