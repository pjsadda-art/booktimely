<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\SmsLog;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * Outbound customer messaging for deposits and anything else that needs a
 * synchronous, auditable send.
 *
 * Three things this deliberately does differently from the queued notification
 * pipeline:
 *
 *  1. Settings keys are *derived* from the event definition, so the switch a
 *     settings screen writes and the switch this reads cannot drift apart into
 *     a feature that can never fire.
 *  2. Template tokens come from the supplied data; there is no parallel list of
 *     token names to forget to update.
 *  3. Sending is synchronous and every attempt writes an SmsLog row, so a
 *     caller can read back whether the message actually left rather than
 *     reporting an optimistic success.
 */
class CustomerNotifier
{
    /**
     * The event catalogue. Everything about an event — its templates, its
     * settings keys, its log tag — is derived from this one definition.
     *
     * @var array<string,array{label:string,email_template:string,sms_body:string}>
     */
    public const EVENTS = [
        'deposit_request_auto' => [
            'label' => 'Deposit Request (Auto)',
            'email_template' => 'Deposit Request Auto',
            'sms_body' => 'Hi {customer_name}, To confirm your appointment on {appointment_date} at {appointment_time}, a deposit of {deposit_amount} is required. Please complete payment here: {payment_link}. This deposit will be applied to your final bill and may be forfeited in case of no-show or late cancellation.',
        ],
        'deposit_request_manual' => [
            'label' => 'Deposit Request (Manual)',
            'email_template' => 'Deposit Request Manual',
            'sms_body' => 'Hi {customer_name}, Your appointment on {appointment_date} at {appointment_time} requires a deposit of {deposit_amount} to secure your booking. Pay securely here: {payment_link}. This helps us reserve premium time slots just for you.',
        ],
    ];

    /**
     * How long an identical send is suppressed for, in minutes.
     *
     * A deliberate resend must bypass this. The source system's queued listener
     * suppressed identical sends inside a ten-minute window — exactly the
     * behaviour that makes a Resend button silently do nothing — so the resend
     * path passes $bypassDebounce and sends directly.
     */
    public const DEBOUNCE_MINUTES = 10;

    /**
     * The settings key for one channel of one event.
     *
     * Writer and reader both call this, which is the whole point: a per-event
     * switch stored under a key name nothing ever writes is a feature that can
     * never fire, and it fails silently.
     */
    public static function settingsKey(string $event, string $channel): string
    {
        return 'notify_' . $event . '_' . $channel;
    }

    /**
     * Whether a channel is switched on for this business. Defaults to on: a
     * business that has never visited the settings screen should still get its
     * deposit requests delivered.
     */
    public function channelEnabled(string $event, string $channel, $businessId): bool
    {
        $value = company_setting(self::settingsKey($event, $channel), null, $businessId);

        return $value === null || $value === '' || $value === 'on';
    }

    /**
     * Send one event to a customer on every channel they accept.
     *
     * @param  array<string,string>  $data  template tokens
     * @return array{sms:?array,email:?array}
     */
    public function send(
        Customer $customer,
        string $event,
        array $data,
        $businessId,
        $createdBy,
        bool $bypassDebounce = false,
        $appointmentId = null
    ): array {
        $channels = $customer->reachableChannels();
        $user = $customer->customer;

        $result = ['sms' => null, 'email' => null];

        if ($channels['sms'] && $this->channelEnabled($event, 'sms', $businessId)) {
            $result['sms'] = $this->sendSms(
                $user->mobile_no,
                $event,
                $data,
                $businessId,
                $createdBy,
                $bypassDebounce,
                $user->id ?? null,
                $appointmentId
            );
        }

        if ($channels['email'] && $this->channelEnabled($event, 'email', $businessId)) {
            $result['email'] = $this->sendEmail($user->email, $event, $data, $businessId, $createdBy, $user->id ?? null);
        }

        return $result;
    }

    /**
     * Send one SMS synchronously and log the outcome.
     *
     * @return array{success:bool,message:string,log_id:?int}
     */
    public function sendSms(
        $mobile,
        string $event,
        array $data,
        $businessId,
        $createdBy,
        bool $bypassDebounce = false,
        $userId = null,
        $appointmentId = null
    ): array {
        if (empty($mobile)) {
            return ['success' => false, 'message' => __('No mobile number on file.'), 'log_id' => null];
        }

        if (!$bypassDebounce && $this->recentlySent($mobile, $event, $businessId)) {
            return [
                'success' => true,
                'message' => __('An identical message was sent moments ago; skipped.'),
                'log_id' => null,
            ];
        }

        $body = $this->render(self::EVENTS[$event]['sms_body'] ?? '', $data);

        $log = SmsLog::create([
            'user_id' => $userId,
            'mobile_no' => $mobile,
            'direction' => 'out',
            'message' => $body,
            'event' => $event,
            'status' => SmsLog::QUEUED,
            'appointment_id' => $appointmentId,
            'business_id' => $businessId,
            'created_by' => $createdBy,
        ]);

        try {
            $outcome = $this->dispatchSms($mobile, $body, $businessId, $createdBy);

            $log->status = $outcome['success'] ? SmsLog::SENT : SmsLog::FAILED;
            $log->provider = $outcome['provider'];
            $log->provider_message_id = $outcome['message_id'];
            $log->error = $outcome['success'] ? null : $outcome['error'];
            $log->save();

            return [
                'success' => $outcome['success'],
                'message' => $outcome['success'] ? __('Message sent.') : $outcome['error'],
                'log_id' => $log->id,
            ];
        } catch (\Exception $e) {
            $log->status = SmsLog::FAILED;
            $log->error = $e->getMessage();
            $log->save();

            // Loud, not silent: an SMS that never left should be visible in the
            // application log as well as in the customer's message history.
            Log::error('CustomerNotifier SMS failed: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage(), 'log_id' => $log->id];
        }
    }

    /**
     * Hand the message to whichever gateway the business has configured.
     *
     * @return array{success:bool,provider:?string,message_id:?string,error:?string}
     */
    protected function dispatchSms($mobile, string $body, $businessId, $createdBy): array
    {
        $settings = getCompanyAllSetting($createdBy, $businessId);
        $provider = $settings['sms_setting'] ?? null;

        if (($settings['sms_notification_is'] ?? 'off') !== 'on' || empty($provider)) {
            return [
                'success' => false,
                'provider' => $provider,
                'message_id' => null,
                'error' => __('SMS is not configured for this business.'),
            ];
        }

        if ($provider === 'cellcast') {
            return $this->dispatchCellcast($mobile, $body, $settings);
        }

        if (!class_exists(\Workdo\SMS\Entities\SendMsg::class) || !class_exists(\Tzsk\Sms\Facades\Sms::class)) {
            return [
                'success' => false,
                'provider' => $provider,
                'message_id' => null,
                'error' => __('The SMS module is not installed.'),
            ];
        }

        \Workdo\SMS\Entities\SendMsg::active_driver($createdBy, $businessId);

        \Tzsk\Sms\Facades\Sms::via($provider)->send($body, function ($sms) use ($mobile) {
            $sms->to($mobile);
        });

        return ['success' => true, 'provider' => $provider, 'message_id' => null, 'error' => null];
    }

    /**
     * Cellcast speaks plain HTTP rather than going through the SMS driver stack.
     */
    protected function dispatchCellcast($mobile, string $body, array $settings): array
    {
        $response = (new Client())->post('https://api.cellcast.com/api/v1/gateway', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . ($settings['cellcast_apiKey'] ?? ''),
            ],
            'json' => [
                'message' => $body,
                'contacts' => [$mobile],
            ],
            'http_errors' => false,
        ]);

        $decoded = json_decode((string) $response->getBody(), true);

        if (!empty($decoded['status'])) {
            return [
                'success' => true,
                'provider' => 'cellcast',
                'message_id' => $decoded['data']['queueResponse'][0]['MessageId'] ?? null,
                'error' => null,
            ];
        }

        return [
            'success' => false,
            'provider' => 'cellcast',
            'message_id' => null,
            'error' => $decoded['msg'] ?? __('The SMS gateway rejected the message.'),
        ];
    }

    /**
     * Send the matching email template.
     *
     * @return array{success:bool,message:string}
     */
    public function sendEmail($email, string $event, array $data, $businessId, $createdBy, $userId = null): array
    {
        if (empty($email)) {
            return ['success' => false, 'message' => __('No email address on file.')];
        }

        $templateName = self::EVENTS[$event]['email_template'] ?? null;

        if (empty($templateName)) {
            return ['success' => false, 'message' => __('No email template is defined for this event.')];
        }

        // Templates are seeded globally. Confirm the row exists before handing
        // off, so a missing template is reported rather than swallowed as a
        // quietly-dropped mail.
        if (!EmailTemplate::where('name', $templateName)->exists()) {
            Log::error("CustomerNotifier: email template '{$templateName}' is missing. Run db:seed --class=DepositEmailTemplates.");

            return [
                'success' => false,
                'message' => __('The :name email template is missing.', ['name' => $templateName]),
            ];
        }

        try {
            $response = EmailTemplate::sendEmailTemplate($templateName, [$email], $data, $userId, $businessId);

            return [
                'success' => !empty($response['is_success']),
                'message' => !empty($response['is_success']) ? __('Email sent.') : ($response['error'] ?: __('Email failed.')),
            ];
        } catch (\Exception $e) {
            Log::error('CustomerNotifier email failed: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Whether an identical event went to this number inside the debounce window.
     */
    protected function recentlySent($mobile, string $event, $businessId): bool
    {
        $last = SmsLog::latestFor($mobile, $event, $businessId);

        return !empty($last)
            && $last->isSuccess()
            && $last->created_at
            && $last->created_at->gt(now()->subMinutes(self::DEBOUNCE_MINUTES));
    }

    /**
     * Substitute {token} placeholders from the supplied data.
     *
     * Same principle as the email templates: the token names are the data's own
     * keys, so a token can never be missing from a list somebody forgot.
     */
    public function render(string $body, array $data): string
    {
        $search = [];
        $replace = [];

        foreach ($data as $token => $value) {
            $search[] = '{' . $token . '}';
            $replace[] = is_scalar($value) || $value === null ? (string) $value : '';
        }

        $rendered = str_replace($search, $replace, $body);

        // Any token the caller did not supply would otherwise be delivered to
        // the customer as literal braces. Strip them instead.
        return trim(preg_replace('/\{[a-z0-9_]+\}/i', '', $rendered));
    }

    /**
     * Business name, used in message bodies.
     */
    public function businessName($businessId): string
    {
        $business = Business::find($businessId);

        return $business->name ?? '';
    }
}
