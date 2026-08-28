<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\CommonEmailTemplate;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'from',
        'module_name',
        'created_by',
        'business_id',
    ];

    public static function sendEmailTemplate($emailTemplate, $mailTo, $obj, $user_id = null, $business_id = null, $pdfAttachment = null, $pdfFilename = null)
    {

        if (!empty($user_id)) {
            $usr = User::where('id', $user_id)->first();
        } else {
            $usr = Auth::user();
        }

        if (empty($usr) || !$usr->email) {
            return [
                'is_success' => false,
                'error' => __('Mail not send, user email not found'),
            ];
        }
        // unset($mailTo[$usr->id]);
        //Remove Current Login user Email don't send mail to them

        $mailTo = array_values(array_filter($mailTo, function ($email) {
            return !empty($email);
        }));
        if (empty($mailTo)) {
            return [
                'is_success' => false,
                'error' => __('Mail not sent, recipient email address not found'),
            ];
        }

        // if($usr->type != 'super admin')
        // {

        // find template is exist or not in our record
        $template = EmailTemplate::where('name', $emailTemplate)->first();

        if (isset($template) && !empty($template)) {
            // get email content language base
            $content = EmailTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', $usr->lang)->first();

            // The business's user may be set to a language this template was
            // never translated into (e.g. a language added after the template
            // was seeded) — fall back to English rather than mailing nothing.
            if (empty($content)) {
                $content = EmailTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', 'en')->first();
            }

            if (empty($content)) {
                return [
                    'is_success' => false,
                    'error' => __('Mail not send, email template content not found'),
                ];
            }

            $content->from = $template->from;

            if (!empty($content->content)) {
                $content->content = self::replaceVariable($content->content, $obj);
                // send email
                if (!empty(company_setting('mail_from_address', $user_id, $business_id))) {

                    if (!empty($user_id) && empty($business_id)) {
                        $setconfing =  SetConfigEmail($user_id);
                    } elseif (!empty($user_id) && !empty($business_id)) {
                        $setconfing =  SetConfigEmail($user_id, $business_id);
                    } else {
                        $setconfing =  SetConfigEmail();
                    }
                    if ($setconfing ==  true) {
                    //     echo "mail send to " . implode(', ', $mailTo) . "<br>";
                    //    echo "<pre>"; print_r($content);
                    //    echo "user_id: " . $user_id;
                    //    echo "business_id: " . $business_id;
                        //try {
                            Mail::to($mailTo)->send(new CommonEmailTemplate($content, $user_id, $business_id, $pdfAttachment, $pdfFilename));
                        // } catch (\Exception $e) {
                        //     $error = $e->getMessage();
                        // }
                    } else {
                        $error = __('Something went wrong please try again ');
                    }
                } else {
                    $error = __('E-Mail has been not sent due to SMTP configuration');
                }

                if (isset($error)) {
                    $arReturn = [
                        'is_success' => false,
                        'error' => $error,
                    ];
                } else {
                    $arReturn = [
                        'is_success' => true,
                        'error' => false,
                    ];
                }
            } else {
                $arReturn = [
                    'is_success' => false,
                    'error' => __('Mail not send, email is empty'),
                ];
            }
            return $arReturn;
        } else {
            return [
                'is_success' => false,
                'error' => __('Mail not send, email not found'),
            ];
        }
        // }
    }

    /**
     * Substitute {token} placeholders in a template body.
     *
     * Tokens are derived from the data supplied by the caller, merged over the
     * defaults below. There is deliberately no separate list of token *names*:
     * this used to be two parallel arrays paired by index, so any token a caller
     * supplied that nobody had remembered to add to the name list was left in
     * the message and mailed to the customer as literal "{payment_link}" text —
     * and adding a token in one place but not the other silently shifted every
     * later substitution onto the wrong value.
     *
     * The array below is now only a set of *defaults*, so an unsupplied known
     * token still renders as "-" rather than as its own placeholder.
     */
    public static function replaceVariable($content, $obj)
    {
        $arrValue    = [
            'app_name' => '-',
            'app_url' => '-',
            'company_name' => '-',
            'email' => '-',
            'password' => '-',

            'staff' => '-',
            'service' => '-',
            'location' => '-',
            'appointment_date' => '-',
            'appointment_time' => '-',
            'appointment_number' => '-',
            'customer' => '-',
            'appointment_review' => '-',
            'review_url' => '-',
            'staff_name' => '-',
            'business_name' => '-',
            'service_name' => '-',
            'zoom_meeting_link' => '-',

            'ticket_name' => '-',
            'ticket_id' => '-',
            'reply_description' => '-',
            'ticket_url' => '-',

            'google_meet_link' => '-',
            'tracking_url' => '-',
            'proposal_name'=>'-',
            'proposal_number'=>'',
            'proposal_url'=>'-',
            'invoice_name' => '-',
            'invoice_number' => '-',
            'invoice_url' => '-',
            'pay_invoice_url' => '-',
            'status' => '-',
            'client_name' => '-',
        ];
        foreach ($obj as $key => $val) {
            $arrValue[$key] = $val;
        }
        $arrValue['app_name']     = env('APP_NAME');
        // $arrValue['company_name'] = '--';
        $arrValue['app_url']      = '<a href="' . env('APP_URL') . '" target="_blank">' . env('APP_URL') . '</a>';

        // Names and values come from the same array, so they cannot drift apart.
        $search = [];
        $replace = [];

        foreach ($arrValue as $token => $value) {
            $search[] = '{' . $token . '}';
            $replace[] = is_scalar($value) || $value === null ? (string) $value : '-';
        }

        return str_replace($search, $replace, $content);
    }
}
