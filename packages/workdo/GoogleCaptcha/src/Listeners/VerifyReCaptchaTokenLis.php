<?php

namespace Workdo\GoogleCaptcha\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\GoogleCaptcha\Events\VerifyReCaptchaToken;

class VerifyReCaptchaTokenLis
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function handle(VerifyReCaptchaToken $event)
    {
        $request = $event->request->all();
        $token=isset($request['g-recaptcha-response']) ? $request['g-recaptcha-response'] : "";

        $secretKey = admin_setting('google_recaptcha_secret');

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array(
            'secret' => $secretKey,
            'response' => $token
        );

        $options = array(
            'http' => array(
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result);
        if ($response->success) {
            return ['status'=>true];
        } else {
            return ['status'=>false];
        }
    }
}
