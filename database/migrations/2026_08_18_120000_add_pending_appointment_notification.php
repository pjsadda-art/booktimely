<?php

use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use App\Models\Notification;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Clone "Create Appointment" into a "Pending Appointment" template/notification
     * so pending bookings (online, calendar, or manual) get their own email —
     * same lookup convention as the other global templates (EmailTemplate::sendEmailTemplate
     * matches by name only, no per-business scoping).
     */
    public function up(): void
    {
        $exists = EmailTemplate::where('name', 'Pending Appointment')->where('module_name', 'general')->exists();

        if (!$exists) {
            $source = EmailTemplate::where('name', 'Create Appointment')->where('module_name', 'general')->first();

            $template = EmailTemplate::create([
                'name' => 'Pending Appointment',
                'from' => 'BookingGo',
                'module_name' => 'general',
                'created_by' => 1,
                'business_id' => 0,
            ]);

            $sourceLangs = $source
                ? EmailTemplateLang::where('parent_id', $source->id)->get()
                : collect();

            if ($sourceLangs->isNotEmpty()) {
                foreach ($sourceLangs as $lang) {
                    EmailTemplateLang::create([
                        'parent_id' => $template->id,
                        'lang' => $lang->lang,
                        'subject' => 'Appointment Pending Confirmation',
                        'variables' => $lang->variables,
                        'content' => $lang->content,
                    ]);
                }
            } else {
                EmailTemplateLang::create([
                    'parent_id' => $template->id,
                    'lang' => 'en',
                    'subject' => 'Appointment Pending Confirmation',
                    'variables' => '{
                        "App Name": "app_name",
                        "Company Name": "company_name",
                        "App Url": "app_url",
                        "Appointment Number": "appointment_number",
                        "Appointment Date": "appointment_date",
                        "Appointment Time": "appointment_time",
                        "Service ": "service",
                        "Location ": "location",
                        "Staff": "staff",
                        "Tracking URL": "tracking_url"
                      }',
                    'content' => '<p>Hello,</p><p>Thank you for booking an appointment with us at {app_name}. Your appointment is currently pending confirmation:</p><p>Appointment Number: {appointment_number}<br>Appointment Date: {appointment_date}<br>Appointment Time: {appointment_time}<br>Service: {service}<br>Location: {location}<br>Staff: {staff}</p><p><a href="{tracking_url}">Track Appointment</a></p><p>We will notify you once your appointment is confirmed. Thank you for choosing {app_name}.</p><p>Thanks,<br>{company_name}<br>{app_name}</p>',
                ]);
            }
        }

        $notificationExists = Notification::where('action', 'Pending Appointment')
            ->where('type', 'mail')
            ->where('module', 'general')
            ->exists();

        if (!$notificationExists) {
            $notification = new Notification();
            $notification->action = 'Pending Appointment';
            $notification->status = 'on';
            $notification->permissions = 'appointment manage';
            $notification->module = 'general';
            $notification->type = 'mail';
            $notification->save();
        }
    }

    public function down(): void
    {
        $template = EmailTemplate::where('name', 'Pending Appointment')->where('module_name', 'general')->first();

        if ($template) {
            EmailTemplateLang::where('parent_id', $template->id)->delete();
            $template->delete();
        }

        Notification::where('action', 'Pending Appointment')
            ->where('type', 'mail')
            ->where('module', 'general')
            ->delete();
    }
};
