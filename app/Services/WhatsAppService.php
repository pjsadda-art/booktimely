<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentMessage;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Cloud API (Meta), one app for the whole platform, one number per
 * business.
 *
 * Every send and every inbound receipt writes an `appointment_messages` row
 * regardless of outcome — that table is this feature's whole audit trail
 * (NFR4), so nothing here is allowed to fail silently without a row to show
 * for it.
 */
class WhatsAppService
{
    /**
     * Whether a business has switched WhatsApp on at all.
     */
    public function isEnabled($businessId): bool
    {
        return company_setting('whatsapp_enabled', null, $businessId) === 'on';
    }

    /**
     * Whether a business's WhatsApp settings are actually usable, not just
     * switched on. A business can tick "enabled" before finishing setup —
     * the tab needs to tell staff that apart from "not configured at all".
     */
    public function isConfigured($businessId): bool
    {
        return !empty(company_setting('whatsapp_phone_number_id', null, $businessId))
            && !empty(company_setting('whatsapp_meta_access_token', null, $businessId));
    }

    /**
     * Send one text message and record it, success or failure.
     *
     * @return array{success:bool,message:?AppointmentMessage,error:?string}
     */
    public function sendTextMessageToCustomer(Appointment $appointment, string $text, User $staffUser): array
    {
        $businessId = $appointment->business_id;
        $createdBy = $appointment->created_by;

        $customer = $this->resolveCustomer($appointment);
        $mobile = $customer->customer->mobile_no ?? null;

        if (!Customer::isValidAustralianMobile($mobile)) {
            return ['success' => false, 'message' => null, 'error' => __('Customer does not have a valid WhatsApp-compatible mobile number.')];
        }

        if (!$this->isConfigured($businessId)) {
            return ['success' => false, 'message' => null, 'error' => __('WhatsApp is not configured for this business.')];
        }

        $phoneNumberId = company_setting('whatsapp_phone_number_id', null, $businessId);
        $accessToken = $this->decryptToken(company_setting('whatsapp_meta_access_token', null, $businessId));

        if (empty($accessToken)) {
            return ['success' => false, 'message' => null, 'error' => __('WhatsApp is not configured for this business.')];
        }

        $to = ltrim((string) $mobile, '+');
        $outcome = $this->dispatchText($phoneNumberId, $accessToken, $to, $text);

        $record = AppointmentMessage::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $customer->id ?? null,
            'sender_type' => AppointmentMessage::SENDER_STAFF,
            'sender_id' => $staffUser->id,
            'message_type' => 'text',
            'message_content' => $text,
            'whatsapp_message_id' => $outcome['message_id'],
            'direction' => AppointmentMessage::DIRECTION_OUTBOUND,
            'status' => $outcome['success'] ? AppointmentMessage::SENT : AppointmentMessage::FAILED,
            'error' => $outcome['success'] ? null : $outcome['error'],
            'business_id' => $businessId,
            'created_by' => $createdBy,
        ]);

        return [
            'success' => $outcome['success'],
            'message' => $record,
            'error' => $outcome['success'] ? null : $outcome['error'],
        ];
    }

    /**
     * The actual Graph API call. Never throws — a WhatsApp outage must not
     * take the appointment panel down with it (NFR6).
     *
     * @return array{success:bool,message_id:?string,error:?string}
     */
    protected function dispatchText(string $phoneNumberId, string $accessToken, string $to, string $text): array
    {
        $version = config('services.whatsapp.graph_version', 'v20.0');

        try {
            $response = (new Client())->post("https://graph.facebook.com/{$version}/{$phoneNumberId}/messages", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'text',
                    'text' => ['body' => $text],
                ],
                'http_errors' => false,
                'timeout' => 15,
            ]);

            $decoded = json_decode((string) $response->getBody(), true);

            if (!empty($decoded['messages'][0]['id'])) {
                return ['success' => true, 'message_id' => $decoded['messages'][0]['id'], 'error' => null];
            }

            return [
                'success' => false,
                'message_id' => null,
                'error' => $decoded['error']['message'] ?? __('The WhatsApp API rejected the message.'),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsAppService send failed: ' . $e->getMessage());

            return ['success' => false, 'message_id' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Store one inbound message from Meta's webhook.
     *
     * $businessId/$createdBy are already resolved by the caller (via
     * resolveBusinessForPhoneNumberId()) from the payload's phone_number_id —
     * this method never has to guess which tenant a message belongs to.
     */
    public function handleInboundMessage(array $message, $businessId, $createdBy): void
    {
        $from = (string) ($message['from'] ?? '');
        $body = (string) ($message['text']['body'] ?? '');
        $wamid = $message['id'] ?? null;

        if (empty($from) || $body === '') {
            return;
        }

        $mobile = '+' . ltrim($from, '+');
        $user = User::where('mobile_no', $mobile)->where('created_by', $createdBy)->first();

        $customer = null;
        $appointment = null;

        if (!empty($user)) {
            $customer = Customer::where('business_id', $businessId)
                ->where('created_by', $createdBy)
                ->where('user_id', $user->id)
                ->first();

            // The most recently created booking for this customer — close
            // enough to "the conversation this reply belongs to" without
            // guessing at upcoming vs. past.
            $appointment = Appointment::where('business_id', $businessId)
                ->where('created_by', $createdBy)
                ->where('customer_id', $user->id)
                ->orderByDesc('id')
                ->first();
        }

        AppointmentMessage::create([
            'appointment_id' => $appointment->id ?? null,
            'customer_id' => $customer->id ?? null,
            'sender_type' => AppointmentMessage::SENDER_CUSTOMER,
            'sender_id' => null,
            'message_type' => 'text',
            'message_content' => $body,
            'whatsapp_message_id' => $wamid,
            'direction' => AppointmentMessage::DIRECTION_INBOUND,
            'status' => AppointmentMessage::RECEIVED,
            'business_id' => $businessId,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Apply a delivery/read status callback to the message it belongs to.
     */
    public function updateMessageStatus(string $whatsappMessageId, string $status): void
    {
        AppointmentMessage::where('whatsapp_message_id', $whatsappMessageId)->update(['status' => $status]);
    }

    /**
     * Which business owns a WhatsApp phone number id, from the webhook
     * payload's metadata — the only way a shared webhook endpoint can tell
     * tenants apart.
     *
     * @return array{business_id:int,created_by:int}|null
     */
    public function resolveBusinessForPhoneNumberId(?string $phoneNumberId): ?array
    {
        if (empty($phoneNumberId)) {
            return null;
        }

        $setting = Setting::where('key', 'whatsapp_phone_number_id')
            ->where('value', $phoneNumberId)
            ->first();

        if (empty($setting)) {
            return null;
        }

        return ['business_id' => (int) $setting->business, 'created_by' => (int) $setting->created_by];
    }

    protected function resolveCustomer(Appointment $appointment): ?Customer
    {
        if (empty($appointment->customer_id)) {
            return null;
        }

        return Customer::with('customer')
            ->where('business_id', $appointment->business_id)
            ->where('created_by', $appointment->created_by)
            ->where('user_id', $appointment->customer_id)
            ->first();
    }

    /**
     * Stored encrypted (see Company\SettingsController::whatsappSettingsStore)
     * — every other setting in this app is plaintext, but a long-lived Meta
     * access token is sensitive enough to warrant it (NFR2).
     */
    protected function decryptToken(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decrypt($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
