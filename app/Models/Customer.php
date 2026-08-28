<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The salon-specific half of a customer.
 *
 * A customer is two rows: a `users` row holding identity and contact details,
 * and this one holding the profile. Note the id trap that runs through the
 * whole platform — `appointments.customer_id` holds a **user** id, while
 * invoices and everything in this model's own tables hold a **customers.id**.
 * They are different numbers for the same person, and mixing them produces
 * plausible-looking wrong data rather than an error. Use userId()/id
 * deliberately, never interchangeably.
 */
class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'gender',
        'dob',
        'description',
        'business_id',
        'created_by',
        'wallet_balance',
        'loyalty_enabled',
        'loyalty_points',
        'is_high_risk',
        'is_walkin',
        'notification_preference',
        'communication_sms',
        'communication_email',
    ];

    protected $casts = [
        'loyalty_enabled' => 'boolean',
        'is_high_risk' => 'boolean',
        'is_walkin' => 'boolean',
        'communication_sms' => 'boolean',
        'communication_email' => 'boolean',
    ];

    public function customer()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /** Alias with an unambiguous name — `customer->customer` reads badly. */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * This customer's appointments. Keyed on `user_id`, not `id`.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'customer_id', 'user_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'customer_id', 'id')->latest('id');
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class, 'customer_id', 'id')->latest('id');
    }

    public function notes()
    {
        return $this->hasMany(CustomerNote::class, 'customer_id', 'id')->latest('id');
    }

    public function serviceVisits()
    {
        return $this->hasMany(CustomerServiceVisit::class, 'customer_id', 'id');
    }

    /**
     * Recompute `wallet_balance` from the ledger and store it.
     *
     * The ledger is the truth and the column is a cache, so anything that
     * doubts the number should reconcile rather than trust it.
     */
    public function reconcileWallet(): string
    {
        $credits = $this->walletTransactions()->where('type', 'credit')->sum('amount');
        $debits = $this->walletTransactions()->where('type', 'debit')->sum('amount');

        $balance = round((float) $credits - (float) $debits, 2);

        $this->wallet_balance = $balance;
        $this->save();

        return number_format($balance, 2, '.', '');
    }

    /**
     * Which channels this customer accepts, intersected with what we can
     * actually reach them on.
     *
     * Re-checked live against the linked user's current mobile/email rather
     * than trusted from the stored communication_sms/communication_email bit
     * alone — a number edited to a landline after the toggle was saved must
     * stop being sent to immediately, not just after someone revisits the
     * toggle.
     *
     * @return array{sms:bool,email:bool}
     */
    public function reachableChannels(): array
    {
        $preference = $this->notification_preference ?: 'both';
        $user = $this->customer;

        return [
            'sms' => in_array($preference, ['sms', 'both'], true)
                && (bool) $this->communication_sms
                && self::isValidAustralianMobile($user->mobile_no ?? null),
            'email' => in_array($preference, ['email', 'both'], true)
                && (bool) $this->communication_email
                && self::isValidEmail($user->email ?? null),
        ];
    }

    /**
     * Australian mobile: 04xxxxxxxx or +614xxxxxxxx (the format
     * CustomerController::formatAustralianPhone() normalizes numbers to
     * before they're ever saved to users.mobile_no).
     */
    public static function isValidAustralianMobile(?string $mobile): bool
    {
        return $mobile !== null && preg_match('/^(?:\+61|0)4\d{8}$/', trim($mobile)) === 1;
    }

    public static function isValidEmail(?string $email): bool
    {
        return $email !== null && preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', trim($email)) === 1;
    }

    /**
     * The Communication Group toggles. A toggle can only be turned on if the
     * matching contact detail is actually valid — an opted-in toggle pointing
     * at an invalid number/address reads as "this will be delivered" and
     * silently won't be, which is worse than no toggle at all.
     *
     * @return array{sms:bool,email:bool}
     */
    public function setCommunicationPreferences(bool $wantsSms, bool $wantsEmail): array
    {
        $user = $this->customer;

        $this->communication_sms = $wantsSms && self::isValidAustralianMobile($user->mobile_no ?? null);
        $this->communication_email = $wantsEmail && self::isValidEmail($user->email ?? null);
        $this->save();

        return ['sms' => $this->communication_sms, 'email' => $this->communication_email];
    }
}
