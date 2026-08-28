<?php
// This file use for handle company setting page

namespace Workdo\SMS\Http\Controllers\Company;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Notification;
use Workdo\SMS\Entities\SendMsg;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;
use App\Models\CustomStatus;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($settings)
    {
        // echo creatorId()."\n";
        // echo getActiveBusiness()."\n";
        // $settings = getCompanyAllSetting(creatorId(), getActiveBusiness());
        // echo "<pre>"; print_r($settings); echo "</pre>";die;
        $sms_setting = SendMsg::$sms_settings;
        $notification_modules = Notification::where('type', 'SMS')->groupBy('module')->pluck('module');
        $notify = Notification::where('type', 'SMS')->get();
        $custom_status = CustomStatus::where('business_id', getActiveBusiness())->where('created_by', creatorId())->get();
       // ech
        return view('sms::company.settings.index', compact('settings', 'sms_setting', 'notification_modules', 'notify', 'custom_status'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('sms manage')) {
            if ($request->has('sms_notification_is')) {
                if ($request->sms_setting == "sns") {
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'sns_access_key' => 'required|string|max:255',
                            'sns_secret_key' => 'required|string|max:255',
                            'sns_region' => 'required|string|max:255',
                            'sns_sender_id' => 'required|string|max:255',
                            'sns_type' => 'required|string|max:255',

                        ]
                    );
                } elseif ($request->sms_setting == "twilio") {
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'sms_twilio_sid' => 'required|string|max:255',
                            'sms_twilio_token' => 'required|string|max:255',
                            'sms_twilo_from_number' => 'required|string|max:255',

                        ]
                    );
                } elseif ($request->sms_setting == "clockwork") {
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'clockwork_api_key' => 'required|string|max:255',

                        ]
                    );
                } elseif ($request->sms_setting == "melipayamak") {
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'melipayamak_username' => 'required|string|max:255',
                            'melipayamak_password' => 'required|string|max:255',
                            'melipayamak_from_number' => 'required|string|max:255',

                        ]
                    );
                } elseif ($request->sms_setting == "kavenegar") {
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'kavenegar_apiKey' => 'required|string|max:255',
                            'kavenegar_from_number' => 'required|string|max:255',

                        ]
                    );
                } elseif ($request->sms_setting == "cellcast") {
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'cellcast_apiKey' => 'required|string|max:255',
                            'cellcast_from_number' => 'required|string|max:255',

                        ]
                    );
                } else {
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'smsgatewayme_apiToken' => 'required|string|max:255',
                            'Smsgatewayme_device_id' => 'required|string|max:255',
                        ]
                    );
                }
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $post = $request->all();


                unset($post['_token'], $post['sms']);
                //echo "<pre>";print_r($post);die;
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
                if ($request->has('sms')) {
                    foreach ($request->sms as $key => $notification) {
                        $data = [
                            'key' => $key,
                            'business' => getActiveBusiness(),
                            'created_by' => creatorId(),
                        ];
                        Setting::updateOrInsert($data, ['value' => $notification]);
                    }
                }
            } else {
                $data = [
                    'key' => 'sms_notification_is',
                    'business' => getActiveBusiness(),
                    'created_by' => creatorId(),
                ];
                // Check if the record exists, and update or insert accordingly
                Setting::updateOrInsert($data, ['value' => 'off']);
            }
            // Settings Cache forget
            AdminSettingCacheForget();
            comapnySettingCacheForget();
            return redirect()->back()->with('success', 'SMS Setting save sucessfully.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function get_smsfields(Request $request){
        $settings = getCompanyAllSetting();

        $sms_setting = $request->sms_setting;

       $returnHTML = view('sms::company.settings.input', compact('sms_setting','settings'))->render();
       $response = [
           'is_success' => true,
           'message' => '',
           'html' => $returnHTML,
       ];

       return response()->json($response);

    }
}
