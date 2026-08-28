<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use App\Models\Language;
use App\Services\CustomerNotifier;
use Illuminate\Database\Seeder;

/**
 * The two deposit-request email templates.
 *
 * Which one is used is selected by whether `deposit_requested_by` is null: the
 * automatic rule engine and a staff member say different things to a customer,
 * and that null is the only thing that distinguishes them.
 *
 * Seeded globally (created_by 0), matching how EmailTemplate::sendEmailTemplate
 * looks templates up — it queries by name with no tenant scope. Seeding these
 * per-tenant while the lookup stays global is exactly how mail ends up silently
 * dropped, so the two must agree; CustomerNotifier checks the row exists before
 * sending and reports loudly if it does not.
 */
class DepositEmailTemplates extends Seeder
{
    public function run(): void
    {
        $bodies = [
            'Deposit Request Auto' => '<p>Hi {customer_name},</p>
<p>To confirm your appointment on <strong>{appointment_date}</strong> at <strong>{appointment_time}</strong>, a deposit of <strong>{deposit_amount}</strong> is required.</p>
<p><a href="{payment_link}">Complete your payment here</a></p>
<p>This deposit will be applied to your final bill and may be forfeited in case of no-show or late cancellation.</p>
<p>Thanks,<br />{business_name}</p>',

            'Deposit Request Manual' => '<p>Hi {customer_name},</p>
<p>Your appointment on <strong>{appointment_date}</strong> at <strong>{appointment_time}</strong> requires a deposit of <strong>{deposit_amount}</strong> to secure your booking.</p>
<p><a href="{payment_link}">Pay securely here</a></p>
<p>This helps us reserve premium time slots just for you. The deposit is applied to your final bill.</p>
<p>Thanks,<br />{business_name}</p>',
        ];

        // Variables are documented for the template editor. They are *not* used
        // for substitution — that is derived from the data supplied at send time.
        $variables = json_encode([
            'Customer Name' => 'customer_name',
            'Appointment Date' => 'appointment_date',
            'Appointment Time' => 'appointment_time',
            'Deposit Amount' => 'deposit_amount',
            'Payment Link' => 'payment_link',
            'Business Name' => 'business_name',
        ]);

        $languages = Language::pluck('code')->all();

        if (empty($languages)) {
            $languages = ['en'];
        }

        foreach (CustomerNotifier::EVENTS as $event) {
            $name = $event['email_template'];

            $template = EmailTemplate::firstOrCreate(
                ['name' => $name],
                ['from' => env('APP_NAME'), 'module_name' => 'Base', 'created_by' => 0]
            );

            foreach ($languages as $language) {
                EmailTemplateLang::firstOrCreate(
                    ['parent_id' => $template->id, 'lang' => $language],
                    [
                        'subject' => $name === 'Deposit Request Auto'
                            ? 'Deposit required for your appointment'
                            : 'Your deposit request',
                        'variables' => $variables,
                        'content' => $bodies[$name],
                    ]
                );
            }
        }
    }
}
