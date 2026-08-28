<?php

namespace Workdo\Paypal\Trait;

use Srmklive\PayPal\Services\PayPal as PayPalClient;

trait Paypal
{
    // Make paypal payment
    public function payPalPayment($request)
    {
        try {
            $provider   = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $response = $provider->createOrder([
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => $request['return_url'],
                    "cancel_url" => $request['cancel_url'],
                ],
                "purchase_units" => [[
                    "amount" => [
                        "currency_code" =>  $request['currency_code'],
                        "value" => $request['value'],
                    ]
                ]]
            ]);


            if (isset($response['id'])) {
                $approvalLink = collect($response['links'])->firstWhere('rel', 'approve');
                if ($approvalLink) {
                    return (object) ['status' => 'success', 'message' => __('Your plan is in progress.'), 'url' => $approvalLink['href']];
                }
            }
            return (object) ['url' => null, 'status' => 'error', 'message' => __('Something went wrong')];
        } catch (\Exception $e) {
            return (object) ['url' => null, 'status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Paypal Payment Status
    public function payPalPaymentStatus($token)
    {
        try {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            $response = $provider->capturePaymentOrder($token);
            return (object) ['status' => isset($response['status']) && $response['status'] == 'COMPLETED' ? 'true' : 'false', 'message' => __('Your plan is in progress.'),];
        } catch (\Exception $e) {
            return (object) ['status' => 'false', 'message' => $e->getMessage()];
        }
    }

    // Check Paypal Detail config
    public function customerConfig($data)
    {
        if ($data['company_paypal_mode'] == 'live') {
            config([
                'paypal.live.client_id'     => isset($data['company_paypal_client_id']) ? $data['company_paypal_client_id'] : '',
                'paypal.live.client_secret' => isset($data['company_paypal_secret_key']) ? $data['company_paypal_secret_key'] : '',
                'paypal.mode'               => isset($data['company_paypal_mode']) ? $data['company_paypal_mode'] : '',
            ]);
        } else {
            config([
                'paypal.sandbox.client_id'      => isset($data['company_paypal_client_id']) ? $data['company_paypal_client_id'] : '',
                'paypal.sandbox.client_secret'  => isset($data['company_paypal_secret_key']) ? $data['company_paypal_secret_key'] : '',
                'paypal.mode'                   => isset($data['company_paypal_mode']) ? $data['company_paypal_mode'] : '',
            ]);
        }
    }
}
