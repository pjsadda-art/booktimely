<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Inbound Meta webhook — server-to-server, no session, no Sanctum token.
 * Authorised entirely by Meta's own verification mechanisms (NFR3):
 * the shared verify token on setup, and the X-Hub-Signature-256 HMAC on
 * every subsequent delivery.
 *
 * One endpoint for the whole platform (see the plan's webhook design
 * decision) — each event's business is resolved from its own
 * metadata.phone_number_id, not from anything in the URL.
 */
class WhatsAppWebhookController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * GET /api/whatsapp/webhook
     *
     * Meta's one-time subscription challenge: echo hub.challenge back only
     * when hub.verify_token matches what we told Meta to send.
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        $expected = (string) config('services.whatsapp.webhook_verify_token');

        if ($mode === 'subscribe' && !empty($expected) && hash_equals($expected, (string) $token)) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /**
     * POST /api/whatsapp/webhook
     *
     * Always answers 200 once the signature is valid — even for events we
     * skip (unknown number, non-text message) — because a non-200 makes
     * Meta retry the same delivery repeatedly (NFR6: this must degrade
     * gracefully, not compound into a retry storm).
     */
    public function receive(Request $request)
    {
        if (!$this->signatureValid($request)) {
            Log::warning('WhatsApp webhook: signature verification failed.');

            return response('Invalid signature', 401);
        }

        $payload = $request->json()->all();

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $this->processChange($change['value'] ?? []);
            }
        }

        return response('EVENT_RECEIVED', 200);
    }

    protected function processChange(array $value): void
    {
        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;
        $business = $this->whatsapp->resolveBusinessForPhoneNumberId($phoneNumberId);

        if (empty($business)) {
            // No business has claimed this number — nothing to file the
            // message against. Logged, not stored: the spec defers an
            // "unassigned pool" to a future phase.
            Log::info('WhatsApp webhook: no business configured for phone_number_id ' . $phoneNumberId);

            return;
        }

        foreach ($value['messages'] ?? [] as $message) {
            if (($message['type'] ?? null) !== 'text') {
                // Media types are out of scope for this phase (FR5) — logged
                // rather than silently dropped.
                Log::info('WhatsApp webhook: skipped non-text message type ' . ($message['type'] ?? 'unknown'));

                continue;
            }

            try {
                $this->whatsapp->handleInboundMessage($message, $business['business_id'], $business['created_by']);
            } catch (\Exception $e) {
                report($e);
            }
        }

        foreach ($value['statuses'] ?? [] as $status) {
            if (empty($status['id']) || empty($status['status'])) {
                continue;
            }

            try {
                $this->whatsapp->updateMessageStatus($status['id'], $status['status']);
            } catch (\Exception $e) {
                report($e);
            }
        }
    }

    /**
     * hash_hmac over the *raw* request body — must run before anything
     * touches $request as parsed JSON, since the signature covers the exact
     * bytes Meta sent.
     */
    protected function signatureValid(Request $request): bool
    {
        $secret = (string) config('services.whatsapp.app_secret');
        $header = (string) $request->header('X-Hub-Signature-256');

        if (empty($secret) || empty($header)) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $header);
    }
}
