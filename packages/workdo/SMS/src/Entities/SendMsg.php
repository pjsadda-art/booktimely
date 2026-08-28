<?php

namespace Workdo\SMS\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\NotificationTemplateLang;
use App\Models\Business;
use App\Models\User;
use Tzsk\Sms\Facades\Sms;
use GuzzleHttp\Client;
use App\Models\Appointment;
use GuzzleHttp\Exception\RequestException;

class SendMsg extends Model
{
    use HasFactory;

    protected $fillable = [];

    public static $sms_settings = [
        "sns" => "AWS",
        "twilio" => "Twilio",
        "clockwork" => "Clockwork",
        "melipayamak" => "Melipayamak",
        "kavenegar" => "Kavenegar",
        "smsgatewayme" => "SMS Gateway Me",
        "cellcast" => "Cellcast",
    ];

    public static function SendMsgs($mobile_no, $uArr, $action, $company_id = null, $business_id = null)
    {
        $usr = Auth::user();
        $template = Notification::where('action', $action)->where('type', 'SMS')->first();
        if (!empty($usr)) {
            $usr = User::find($company_id);
        }
        if (!empty($usr)) {
            $content = NotificationTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', $usr->lang)->first();
        } else {
            $content = NotificationTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', 'en')->first();
        }
        $msg = self::replaceVariable($content->content, $uArr, $company_id, $business_id);
        $company_settings = getCompanyAllSetting($company_id, $business_id);
        if ($company_settings['sms_notification_is'] == "on") {

            self::active_driver($company_id, $business_id);
        }
        try {
            if ($company_settings['sms_notification_is'] == "on") {
                if ($company_settings['sms_setting'] == "cellcast") {
                    $client = new Client();

                    $url = 'https://api.cellcast.com/api/v1/gateway';

                    $headers = [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $company_settings['cellcast_apiKey']
                    ];

                    $body = [
                        'message' => $msg,
                        'contacts' => [$mobile_no]
                    ];

                    try {
                        $response = $client->post($url, [
                            'headers' => $headers,
                            'json' => $body,
                        ]);

                        $responseBody = json_decode($response->getBody(), true);
                        //echo "<pre>"; print_r($responseBody); echo "</pre>";die;
                        if (isset($responseBody['status']) && $responseBody['status'] == true) {
                            $messageId = $responseBody['data']['queueResponse'][0]['MessageId'] ?? null;
                            //echo "Message sent successfully. Message ID: " . $messageId;die;
                            $appointment_id = $uArr['appointment_id'] ?? null;
                            Appointment::where('id', $appointment_id)->update(['sms_message_id' => $messageId,'is_reminder' => 1]);
                        }
                    } catch (RequestException $e) {
                        if ($e->hasResponse()) {
                            json_decode($e->getResponse()->getBody(), true);
                        } else {
                            echo $e->getMessage();
                        }
                    }
                } else {
                    $response = Sms::via($company_settings['sms_setting'])->send($msg, function ($sms) use ($mobile_no) {
                        $sms->to($mobile_no);
                    });
                }
            }
        } catch (\Exception $e) {
        }
    }

    public static function replaceVariable($content, $obj, $company_id = null, $business_id = null)
    {
        $arrVariable = [
            '{business_name}',
            '{company_name}',
            '{user_name}',

            '{appointment_name}',
            '{date}',
            '{time}',
            '{status}',
            '{ticket_name}',
            '{name}',
            '{tracking_url}',
            '{location}',
            '{location_contact}',
            '{booking_reference}'

        ];
        $arrValue = [
            'business_name' => '-',
            'company_name' => '-',
            'user_name' => '-',

            'appointment_name' => '-',
            'date' => '-',
            'time' => '-',
            'status' => '-',
            'ticket_name' => '-',
            'name' => '-',
            'tracking_url' => '-',
            'location' => '-',
            'location_contact' => '-',
            'booking_reference' => '-',


        ];

        foreach ($obj as $key => $val) {
            $arrValue[$key] = $val;
        }


        $business = Business::find(getActiveBusiness());
        if (!empty($business)) {
            $arrValue['company_name'] = Auth::user()->name;
            $arrValue['business_name'] = $business->name;
        } else {
            $user = User::find($company_id);
            $business = Business::find(getActiveBusiness($company_id));
            $arrValue['company_name'] = $user->name;
            $arrValue['business_name'] = $business->name;
        }
        return str_replace($arrVariable, array_values($arrValue), $content);
    }

    public static function active_driver($company_id = null, $business_id = null)
    {
        $company_settings = getCompanyAllSetting($company_id, $business_id);
        if ($company_settings['sms_setting'] == "twilio") {

            $twilio_sid = isset($company_settings['sms_twilio_sid']) ? $company_settings['sms_twilio_sid'] : '';
            $twilio_token = isset($company_settings['sms_twilio_token']) ? $company_settings['sms_twilio_token'] : '';
            $twilio_from = isset($company_settings['sms_twilo_from_number']) ? $company_settings['sms_twilo_from_number'] : '';

            config(
                [
                    'sms.drivers.twilio.sid' => $twilio_sid,
                    'sms.drivers.twilio.token' => $twilio_token,
                    'sms.drivers.twilio.from' => $twilio_from,
                ]
            );
        } elseif ($company_settings['sms_setting'] == "sns") {
            $sns_access_key = isset($company_settings['sns_access_key']) ? $company_settings['sns_access_key'] : '';
            $sns_secret_key = isset($company_settings['sns_secret_key']) ? $company_settings['sns_secret_key'] : '';
            $sns_region = isset($company_settings['sns_region']) ? $company_settings['sns_region'] : '';
            $sns_sender_id = isset($company_settings['sns_sender_id']) ? $company_settings['sns_sender_id'] : '';
            $sns_type = isset($company_settings['sns_type']) ? $company_settings['sns_type'] : '';

            config(
                [
                    'sms.drivers.sns.sid' => $sns_access_key,
                    'sms.drivers.sns.token' => $sns_secret_key,
                    'sms.drivers.sns.from' => $sns_region,
                    'sms.drivers.sns.from' => $sns_sender_id,
                    'sms.drivers.sns.from' => $sns_type,
                ]
            );
        } elseif ($company_settings['sms_setting'] == "clockwork") {
            $clockwork_api_key = isset($company_settings['clockwork_api_key']) ? $company_settings['clockwork_api_key'] : '';


            config(
                [
                    'sms.drivers.clockwork.key' => $clockwork_api_key,

                ]
            );
        } elseif ($company_settings['sms_setting'] == "melipayamak") {
            $melipayamak_username = isset($company_settings['melipayamak_username']) ? $company_settings['melipayamak_username'] : '';
            $melipayamak_password = isset($company_settings['melipayamak_password']) ? $company_settings['melipayamak_password'] : '';
            $melipayamak_from_number = isset($company_settings['melipayamak_from_number']) ? $company_settings['melipayamak_from_number'] : '';
            config(
                [
                    'sms.drivers.melipayamak.username' => $melipayamak_username,
                    'sms.drivers.melipayamak.password' => $melipayamak_password,
                    'sms.drivers.melipayamak.from' => $melipayamak_from_number,
                    'sms.drivers.melipayamak.flash' => false,

                ]
            );
        } elseif ($company_settings['sms_setting'] == "kavenegar") {
            $kavenegar_apiKey = isset($company_settings['kavenegar_apiKey']) ? $company_settings['kavenegar_apiKey'] : '';
            $kavenegar_from_number = isset($company_settings['kavenegar_from_number']) ? $company_settings['kavenegar_from_number'] : '';
            config(
                [
                    'sms.drivers.kavenegar.apiKey' => $kavenegar_apiKey,
                    'sms.drivers.kavenegar.from' => $kavenegar_from_number,

                ]
            );
        } else {
            $smsgatewayme_apiToken = isset($company_settings['smsgatewayme_apiToken']) ? $company_settings['smsgatewayme_apiToken'] : '';
            $Smsgatewayme_device_id = isset($company_settings['Smsgatewayme_device_id']) ? $company_settings['Smsgatewayme_device_id'] : '';
            config(
                [
                    'sms.drivers.smsgatewayme.apiToken' => $smsgatewayme_apiToken,
                    'sms.drivers.smsgatewayme.from' => $Smsgatewayme_device_id,

                ]
            );
        }
    }
}
