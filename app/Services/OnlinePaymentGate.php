<?php

namespace App\Services;

/**
 * Is this business able to take a payment online right now?
 *
 * This is the single most load-bearing check in the deposit module, because a
 * deposit request *is* nothing but a payment link. If no gateway is configured
 * and switched on, that link renders a page with no pay button and the customer
 * is stuck with a bill they cannot settle and a booking that will not confirm.
 *
 * Consult it in exactly two places:
 *   - refuse to raise a deposit request;
 *   - refuse to resend one.
 *
 * Do NOT gate the on-the-spot payment path on it. That is precisely the path a
 * business without a gateway depends on, and gating it would leave such a
 * business unable to record a deposit at all.
 */
class OnlinePaymentGate
{
    /**
     * Gateways this platform can collect a deposit through.
     *
     * A gateway counts as ready when its module is active, its own on/off
     * setting is 'on', and every credential it needs is non-empty. All three,
     * because two out of three still produces a checkout page that cannot
     * charge anybody.
     *
     * @var array<string,array{module:string,enable_key:string,credentials:array<int,string>}>
     */
    public const GATEWAYS = [
        'stripe' => [
            'module' => 'Stripe',
            'enable_key' => 'stripe_is_on',
            'credentials' => ['stripe_key', 'stripe_secret'],
        ],
        'paypal' => [
            'module' => 'Paypal',
            'enable_key' => 'paypal_payment_is_on',
            'credentials' => ['company_paypal_client_id', 'company_paypal_secret_key'],
        ],
    ];

    /**
     * Whether at least one gateway can take money for this tenant.
     */
    public function isOnlinePaymentReady($businessId = null, $createdBy = null): bool
    {
        return count($this->availableGateways($businessId, $createdBy)) > 0;
    }

    /**
     * The gateways that are actually usable, keyed by slug.
     *
     * The checkout page renders a button per entry; an empty array is what the
     * gate above refuses on.
     *
     * @return array<string,array{slug:string,label:string}>
     */
    public function availableGateways($businessId = null, $createdBy = null): array
    {
        $businessId = $businessId ?: getActiveBusiness();
        $createdBy = $createdBy ?: creatorId();

        // Memoised per tenant for the life of the request. Appointment listings
        // ask this once per row to decide whether to show the Request button,
        // and the answer cannot change mid-request.
        static $memo = [];
        $key = $businessId . ':' . $createdBy;

        if (array_key_exists($key, $memo)) {
            return $memo[$key];
        }

        $settings = getCompanyAllSetting($createdBy, $businessId);

        $available = [];

        foreach (self::GATEWAYS as $slug => $gateway) {
            if (!module_is_active($gateway['module'], $createdBy)) {
                continue;
            }

            if (($settings[$gateway['enable_key']] ?? 'off') !== 'on') {
                continue;
            }

            $missingCredential = false;

            foreach ($gateway['credentials'] as $credential) {
                if (empty($settings[$credential])) {
                    $missingCredential = true;
                    break;
                }
            }

            if ($missingCredential) {
                continue;
            }

            $available[$slug] = [
                'slug' => $slug,
                'label' => $gateway['module'],
            ];
        }

        $memo[$key] = $available;

        return $available;
    }

    /**
     * Why the gate is closed, phrased for a staff member rather than a log file.
     */
    public function unavailableReason(): string
    {
        return __('No online payment gateway is switched on for this business, so a deposit link would have no way to be paid. Configure a gateway in Settings, or take the deposit on the spot instead.');
    }
}
