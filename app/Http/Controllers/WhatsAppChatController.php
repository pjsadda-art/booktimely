<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentMessage;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The WhatsApp Chat tab on the appointment side panel: send a message, read
 * the thread back. Everything here is session-authenticated staff traffic,
 * same as the rest of booking-v2 — the Meta webhook is a separate,
 * unauthenticated endpoint (see Api\WhatsAppWebhookController).
 */
class WhatsAppChatController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * POST /bookings-v2/appointments/{id}/whatsapp/send
     */
    public function send(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('use_whatsapp_chat')) {
            return response()->json(['success' => false, 'error' => __('Permission denied.')], 403);
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return response()->json(['success' => false, 'error' => __('Appointment not found.')], 404);
        }

        $validator = \Validator::make($request->all(), [
            'message' => 'required|string|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        $result = $this->whatsapp->sendTextMessageToCustomer(
            $appointment,
            $request->input('message'),
            Auth::user()
        );

        return response()->json([
            'success' => $result['success'],
            'error' => $result['error'],
            'message' => $result['message'] ? $this->formatMessage($result['message']) : null,
        ], $result['success'] ? 200 : 422);
    }

    /**
     * GET /bookings-v2/appointments/{id}/whatsapp/messages
     */
    public function messages($id)
    {
        if (!Auth::user()->isAbleTo('use_whatsapp_chat')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $appointment = $this->findAppointment($id);

        if (empty($appointment)) {
            return response()->json(['error' => __('Appointment not found.')], 404);
        }

        $messages = AppointmentMessage::where('appointment_id', $appointment->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->map(fn ($message) => $this->formatMessage($message))
            ->all();

        return response()->json(['messages' => $messages]);
    }

    protected function formatMessage(AppointmentMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender_type' => $message->sender_type,
            'direction' => $message->direction,
            'message_type' => $message->message_type,
            'message_content' => $message->message_content,
            'status' => $message->status,
            'created_at' => optional($message->created_at)->toIso8601String(),
        ];
    }

    /**
     * Tenancy-scoped lookup, matching DepositController::findAppointment().
     */
    protected function findAppointment($id): ?Appointment
    {
        return Appointment::with(['CustomerData'])
            ->where('business_id', getActiveBusiness())
            ->where('created_by', creatorId())
            ->find($id);
    }
}
