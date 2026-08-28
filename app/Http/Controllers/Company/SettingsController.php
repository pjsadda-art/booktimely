<?php

namespace App\Http\Controllers\Company;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($settings)
    {

        $timezones = config('timezones');
        $activatedModules = ActivatedModule();
        return view('company.settings.index',compact('settings','timezones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(Auth::user()->isAbleTo('setting manage'))
        {
            $post = $request->all();
            $company_settings = getCompanyAllSetting();

            unset($post['_token']);
            unset($post['_method']);

            if(!isset($post['site_rtl'])){
                $post['site_rtl'] = 'off';
            }
            if(!isset($post['site_transparent'])){
                $post['site_transparent'] = 'off';
            }
            if(!isset($post['cust_darklayout'])){
                $post['cust_darklayout'] = 'off';
            }
            if (isset($request->color) && $request->color_flag == 'false') {
                $post['color'] = $request->color;
            } else {
                $post['color'] = $request->custom_color;
            }
            unset($post['custom_color']);


            if($request->hasFile('logo_dark'))
            {
                $logo_dark =  'logo_dark_'.time().'.png';
                $uplaod = upload_file($request,'logo_dark',$logo_dark,'logo');
                if($uplaod['flag'] == 1)
                {
                    $post['logo_dark'] = $uplaod['url'];

                    $old_logo_dark = isset($company_settings['logo_dark']) ? $company_settings['logo_dark'] : '';
                    if(!empty($old_logo_dark) && check_file($old_logo_dark))
                    {
                        delete_file($old_logo_dark);
                    }
                }else{
                    return redirect()->back()->with('error',$uplaod['msg']);
                }
            }
            if($request->hasFile('logo_light'))
            {
                $logo_light =  'logo_light_'.time().'.png';
                $uplaod = upload_file($request,'logo_light',$logo_light,'logo');
                if($uplaod['flag'] == 1)
                {
                    $post['logo_light'] = $uplaod['url'];

                    $old_logo_light = isset($company_settings['logo_light']) ? $company_settings['logo_light'] : '';
                    if(!empty($old_logo_light) && check_file($old_logo_light))
                    {
                        delete_file($old_logo_light);
                    }
                }else{
                    return redirect()->back()->with('error',$uplaod['msg']);
                }
            }
            if($request->hasFile('favicon'))
            {
                $favicon =  'favicon_'.time().'.png';
                $uplaod = upload_file($request,'favicon',$favicon,'logo');
                if($uplaod['flag'] == 1){
                    $post['favicon'] = $uplaod['url'];

                    $old_favicon = isset($company_settings['favicon']) ? $company_settings['favicon'] : '';
                    if(!empty($old_favicon) && check_file($old_favicon))
                    {
                        delete_file($old_favicon);
                    }
                }else{
                    return redirect()->back()->with('error',$uplaod['msg']);
                }
            }

            foreach ($post as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'key' => $key,
                    'business' => getActiveBusiness(),
                    'created_by' => creatorId(),
                ];

                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => $value]);
            }
            // Settings Cache forget
            comapnySettingCacheForget();
            return redirect()->back()->with('success', __('Setting save sucessfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function SystemStore(Request $request)
    {
        if (Auth::user()->isAbleTo('setting manage')) {
            $post = $request->all();
            unset($post['_token']);
            unset($post['_method']);

            foreach ($post as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'key' => $key,
                    'business' => getActiveBusiness(),
                    'created_by' => creatorId(),
                ];

                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => $value]);
            }

            // Settings Cache forget
            AdminSettingCacheForget();
            comapnySettingCacheForget();
            return redirect()->back()->with('success', 'Setting save sucessfully.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // currency setting store
    public function saveCompanyCurrencySettings(Request $request)
    {
        $post = $request->all();
        unset($post['_token']);
        unset($post['_method']);
        if (isset($post['defult_currancy'])) {
            $data = explode('-', $post['defult_currancy']);
            $post['defult_currancy_symbol'] = $data[0];
            $post['defult_currancy']        = $data[1];
        } else {
            $post['defult_currancy']        = 'USD';
            $post['defult_currancy_symbol'] = '$';
        }
        if (isset($post['site_currency_symbol_position'])) {
            $post['site_currency_symbol_position'] = !empty($request->site_currency_symbol_position) ? $request->site_currency_symbol_position : 'pre';
        }
        foreach ($post as $key => $value) {
            // Define the data to be updated or inserted
            $data = [
                'key' => $key,
                'business' => getActiveBusiness(),
                'created_by' => creatorId(),
            ];

            // Check if the record exists, and update or insert accordingly
            Setting::updateOrInsert($data, ['value' => $value]);
        }
        // Settings Cache forget
        AdminSettingCacheForget();
        comapnySettingCacheForget();
        return redirect()->back()->with('success', __('Currency Setting save successfully.'));
    }

    public function CompanySettingStore(Request $request)
    {
        $validator = \Validator::make($request->all(),
        [
            'company_name' => 'required',
            'company_address' => 'required',
            'company_city' => 'required',
            'company_state' => 'required',
            'company_zipcode' => 'required',
            'company_country' => 'required',
            'company_telephone' => 'required',
            'company_email' => 'required',
            'company_email_from_name' => 'required',
        ]);
        if($validator->fails()){
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        else
        {
            $post = $request->all();
            unset($post['_token']);
            unset($post['_method']);

            if(isset($request->vat_gst_number_switch) && $request->vat_gst_number_switch == 'on')
            {
                $post['vat_gst_number_switch'] = 'on';
                $post['tax_type'] =  !empty($request->tax_type) ? $request->tax_type : 'VAT';
                $post['vat_number'] =  !empty($request->vat_number) ? $request->vat_number : '';
            }
            else
            {
                $post['vat_gst_number_switch'] = 'off';
                $post['tax_type'] = '';
                $post['vat_number'] = '';
            }

            foreach ($post as $key => $value) {
                // Define the data to be updated or inserted
                $data = [
                    'key' => $key,
                    'business' => getActiveBusiness(),
                    'created_by' => creatorId(),
                ];

                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => $value]);
            }
            // Settings Cache forget
            comapnySettingCacheForget();
            return redirect()->back()->with('success','Company setting save sucessfully.');
        }

    }

    public function CustomJsStore(Request $request)
    {
        $validator = \Validator::make($request->all(),
        [
            'custom_js' => 'required',
        ]);

        if($validator->fails()){
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $customJs = $request->custom_js;

        // Remove existing <script> tags
        $customJs = preg_replace('/<script.*?>.*?<\/script>/si', '', $customJs);

        $customJs = htmlspecialchars($customJs);

        // Define the data to be updated or inserted
        $data = [
            'key' => 'custom_js',
            'business' => getActiveBusiness(),
            'created_by' => creatorId(),
        ];

        // Check if the record exists, and update or insert accordingly
        Setting::updateOrInsert($data, ['value' => $customJs]);

        // Settings Cache forget
        comapnySettingCacheForget();
        return redirect()->back()->with('success','Custom JS save sucessfully.');

    }

    public function CustomCssStore(Request $request)
    {
        $validator = \Validator::make($request->all(),
        [
            'custom_css' => 'required',
        ]);

        if($validator->fails()){
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $customCss = $request->custom_css;

        // Remove existing <style> tags
        $customCss = preg_replace('/<style.*?>.*?<\/style>/si', '', $customCss);

        $customCss = htmlspecialchars($customCss);

        // Define the data to be updated or inserted
        $data = [
            'key' => 'custom_css',
            'business' => getActiveBusiness(),
            'created_by' => creatorId(),
        ];

        // Check if the record exists, and update or insert accordingly
        Setting::updateOrInsert($data, ['value' => $customCss]);

        // Settings Cache forget
        comapnySettingCacheForget();
        return redirect()->back()->with('success','Custom CSS save sucessfully.');

    }

    /**
     * Save the deposit rules engine's configuration.
     *
     * Booleans are stored as the literal strings 'on'/'off', matching every
     * other setting in this table. Checkboxes that are unticked are absent from
     * the request entirely, so each one is written explicitly rather than only
     * when present — otherwise switching a rule off would leave the old 'on'
     * behind and the rule would keep firing.
     */
    public function depositSettingsStore(Request $request)
    {
        if (!Auth::user()->isAbleTo('setting manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'deposit_default_percentage' => 'required|numeric|min:0.01|max:100',
            'deposit_service_price_threshold' => 'nullable|numeric|min:0',
            'deposit_min_cancellations' => 'nullable|numeric|min:0',
            'deposit_min_cancellations_months' => 'nullable|numeric|min:1|max:120',
            'deposit_min_noshows' => 'nullable|numeric|min:0',
            'deposit_min_noshows_months' => 'nullable|numeric|min:1|max:120',
            'reliability_window_months' => 'nullable|numeric|min:0|max:120',
            'deposit_forfeit_window_hours' => 'nullable|numeric|min:0|max:720',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $values = [
            'deposit_auto_enabled' => $request->has('deposit_auto_enabled') ? 'on' : 'off',
            'deposit_default_percentage' => $request->input('deposit_default_percentage'),
            'deposit_service_price_threshold' => $request->input('deposit_service_price_threshold') ?: '0',
            'deposit_min_cancellations' => $request->input('deposit_min_cancellations') ?: '0',
            'deposit_min_cancellations_months' => $request->input('deposit_min_cancellations_months') ?: '6',
            'deposit_min_noshows' => $request->input('deposit_min_noshows') ?: '0',
            'deposit_min_noshows_months' => $request->input('deposit_min_noshows_months') ?: '6',
            // Not `?: '24'` — 0 is a meaningful choice here (never forfeit on a
            // cancellation, only on a no-show) and would be coerced away.
            'deposit_forfeit_window_hours' => $request->filled('deposit_forfeit_window_hours')
                ? $request->input('deposit_forfeit_window_hours')
                : '24',
            // Shared by the reliability badge and the rules engine, so the two
            // always give the same answer about the same customer.
            'reliability_window_months' => $request->input('reliability_window_months', '6'),
            'deposit_confirm_status' => $request->input('deposit_confirm_status') ?: '',
        ];

        // Notification toggles. The key comes from CustomerNotifier::settingsKey(),
        // the very same call the sender uses to read it back — a switch written
        // under one name and read under another is a feature that can never fire.
        foreach (array_keys(\App\Services\CustomerNotifier::EVENTS) as $event) {
            foreach (['sms', 'email'] as $channel) {
                $key = \App\Services\CustomerNotifier::settingsKey($event, $channel);
                $values[$key] = $request->has($key) ? 'on' : 'off';
            }
        }

        foreach ($values as $key => $value) {
            Setting::updateOrInsert(
                ['key' => $key, 'business' => getActiveBusiness(), 'created_by' => creatorId()],
                ['value' => $value]
            );
        }

        comapnySettingCacheForget();

        return redirect()->back()->with('success', __('Deposit settings saved.'));
    }

    /**
     * Save this business's WhatsApp Cloud API connection.
     *
     * The access token is the one setting value in this whole table that
     * isn't stored plaintext — see App\Services\WhatsAppService, which
     * decrypts it the same way. Left blank, the previously saved token is
     * kept rather than wiped, so re-saving the rest of this form (or simply
     * re-ticking the enabled switch) can never accidentally disconnect the
     * business by clearing a secret nobody meant to touch.
     */
    public function whatsappSettingsStore(Request $request)
    {
        if (!Auth::user()->isAbleTo('setting manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'whatsapp_business_number' => 'nullable|string|max:32',
            'whatsapp_phone_number_id' => 'nullable|string|max:64',
            'whatsapp_meta_access_token' => 'nullable|string|max:4096',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $values = [
            'whatsapp_enabled' => $request->has('whatsapp_enabled') ? 'on' : 'off',
            'whatsapp_business_number' => $request->input('whatsapp_business_number', ''),
            'whatsapp_phone_number_id' => $request->input('whatsapp_phone_number_id', ''),
        ];

        foreach ($values as $key => $value) {
            Setting::updateOrInsert(
                ['key' => $key, 'business' => getActiveBusiness(), 'created_by' => creatorId()],
                ['value' => $value]
            );
        }

        if ($request->filled('whatsapp_meta_access_token')) {
            Setting::updateOrInsert(
                ['key' => 'whatsapp_meta_access_token', 'business' => getActiveBusiness(), 'created_by' => creatorId()],
                ['value' => \Illuminate\Support\Facades\Crypt::encrypt($request->input('whatsapp_meta_access_token'))]
            );
        }

        comapnySettingCacheForget();

        return redirect()->back()->with('success', __('WhatsApp settings saved.'));
    }

    public function weekStore(Request $request)
    {
        $validator = \Validator::make($request->all(),
        [
            'week_start_day' => 'required',
        ]);

        if($validator->fails()){
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }


        // Define the data to be updated or inserted
        $data = [
            'key' => 'week_start_day',
            'business' => getActiveBusiness(),
            'created_by' => creatorId(),
        ];

        // Check if the record exists, and update or insert accordingly
        Setting::updateOrInsert($data, ['value' => $request->week_start_day]);

        // Settings Cache forget
        comapnySettingCacheForget();
        return redirect()->back()->with('success','Week setting save sucessfully.');
    }

    public function DefaultStatusStore(Request $request)
    {
        $validator = \Validator::make($request->all(),
        [
            'online_default_status' => 'required|in:pending,confirmed',
            'online_default_template_id' => 'required|integer|exists:email_templates,id',
            'calendar_default_status' => 'required|in:pending,confirmed',
            'calendar_default_template_id' => 'required|integer|exists:email_templates,id',
        ]);

        if($validator->fails()){
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $businessId = getActiveBusiness();
        $createdBy = creatorId();

        $keys = [
            'online_default_status',
            'online_default_template_id',
            'calendar_default_status',
            'calendar_default_template_id',
        ];

        foreach ($keys as $key) {
            $data = [
                'key' => $key,
                'business' => $businessId,
                'created_by' => $createdBy,
            ];

            Setting::updateOrInsert($data, ['value' => $request->input($key)]);
        }

        // Settings Cache forget
        comapnySettingCacheForget();
        return redirect()->back()->with('success','Default appointment status save sucessfully.');
    }

    public function bookingModeStore(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'booking_mode' => 'required|array', 
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $selectedModes = array_keys(array_filter($request->booking_mode, function ($value) {
            return $value != 0;
        }));

        if (empty($selectedModes)) {
            return redirect()->back()->with('error', __('Please select at least one booking mode.'));
        }

        $bookingModeValues = implode(',', $selectedModes);

        $data = [
            'key' => 'booking_mode',
            'business' => getActiveBusiness(),
            'created_by' => creatorId(),
        ];

        Setting::updateOrInsert($data, ['value' => $bookingModeValues]);

        comapnySettingCacheForget();

        return redirect()->back()->with('success', __('Booking Mode saved successfully.'));
    }


    public function companyupdateNoteValue(Request $request)
    {
        $symbol_position = 'pre';
        $symbol = '$';
        $format = '1';
        $price  = '10000';
        $number = explode('.', $price);
        $length = strlen(trim($number[0]));
        $currency_symbol = explode('-',$request->defult_currancy);

        if ($length > 3) {
            $decimal_separator  = isset($request->float_number) && $request->float_number == 'dot' ? '.' : ',';
            $thousand_separator = isset($request->thousand_separator) && $request->thousand_separator == 'dot' ? '.' : ',';
        } else {
            $decimal_separator  = isset($request->decimal_separator) && $request->decimal_separator === 'dot'  ? '.' : ',';
            $thousand_separator = isset($request->thousand_separator) && $request->thousand_separator === 'dot' ? '.' : ',';
        }
        if (isset($request->site_currency_symbol_position) && $request->site_currency_symbol_position == "post") {
            $symbol_position = 'post';
        }

        if (isset($request->defult_currancy)) {
            $symbol = $request->defult_currancy;
        }

        if (isset($request->currency_format)) {
            $format = $request->currency_format;
        }
        if (isset($request->currency_space)) {
            $currency_space = isset($request->currency_space) ? $request->currency_space : '';
        }
        if (isset($request->site_currency_symbol_name)) {
            $symbol = $request->site_currency_symbol_name == 'symbol' ? $currency_symbol[0] : $currency_symbol[1];
        }
        $formatted_price = (
            ($symbol_position == "pre")  ?  $symbol : '') . (isset($currency_space) && $currency_space == 'withspace' ? ' ' : '')
            . number_format($price, $format, $decimal_separator, $thousand_separator) . (isset($currency_space) && $currency_space == 'withspace' ? ' ' : '') .
            (($symbol_position == "post") ?  $symbol : '');
        return response()->json(['success' => true,'formatted_price' => $formatted_price]);
    }

}
