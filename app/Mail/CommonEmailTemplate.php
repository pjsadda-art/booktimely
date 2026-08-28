<?php

namespace App\Mail;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommonEmailTemplate extends Mailable
{
    use Queueable, SerializesModels;
    public $template;
    public $user_id;
    public $business_id;
    public $pdfAttachment;
    public $pdfFilename;

    public function __construct($template, $user_id, $business_id, $pdfAttachment = null, $pdfFilename = null)
    {
        $this->template = $template;
        $this->user_id = $user_id;
        $this->business_id = $business_id;
        $this->pdfAttachment = $pdfAttachment;
        $this->pdfFilename = $pdfFilename;
    }

    public function build()
    {
        $business = Business::where('id', $this->business_id)->first();
        $mail = $this->from(company_setting('mail_from_address', $this->user_id, $this->business_id), company_setting('mail_from_name'))
            ->markdown('email.common_email_template')
            ->subject($this->template->subject)
            ->with([
                'content' => $this->template->content,
                'business' => $business,
            ]);

        if (!empty($this->pdfAttachment)) {
            $mail->attachData($this->pdfAttachment, $this->pdfFilename ?? 'invoice.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
