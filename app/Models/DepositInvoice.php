<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The invoice a deposit is collected against.
 *
 * Created eagerly when a deposit is raised, so the customer-facing checkout page
 * only ever looks an invoice up and never creates one. Both the gateway path and
 * the on-the-spot path settle this same row, which is what keeps a deposit
 * visible to financial reporting however the money arrived.
 */
class DepositInvoice extends Model
{
    use HasFactory;

    public const UNPAID = 'unpaid';
    public const PARTIAL = 'partial';
    public const PAID = 'paid';
    public const CANCELLED = 'cancelled';

    /** The deposit taken up front. */
    public const TYPE_DEPOSIT = 'deposit';

    /** The booking's own bill. The deposit lockout scopes strictly to this. */
    public const TYPE_APPOINTMENT = 'appointment';

    protected $fillable = [
        'invoice_number',
        'common_number',
        'appointment_id',
        'synced_invoice_id',
        'customer_id',
        'invoice_type',
        'status',
        'total',
        'paid_total',
        'token',
        'issue_date',
        'due_date',
        'paid_at',
        'business_id',
        'created_by',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(DepositInvoiceItem::class, 'deposit_invoice_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(DepositInvoicePayment::class, 'deposit_invoice_id', 'id');
    }

    /** The users.id of the customer — the same key appointments use. */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    /**
     * An unguessable token for the customer-facing pay link.
     */
    public static function newToken(): string
    {
        return bin2hex(random_bytes(24));
    }

    public function isPaid(): bool
    {
        return $this->status === self::PAID;
    }

    public function outstanding(): float
    {
        return max(0, round((float) $this->total - (float) $this->paid_total, 2));
    }

    /**
     * Recompute paid_total and status from the payment rows.
     *
     * Returns true when this call is the one that moved the invoice into `paid`,
     * so the caller can run the post-payment side effects exactly once. Gateways
     * retry, and an idempotent settle is the difference between one confirmation
     * SMS and five.
     */
    public function settle(): bool
    {
        $wasPaid = $this->status === self::PAID;

        $paid = round((float) $this->payments()->sum('amount'), 2);
        $total = round((float) $this->total, 2);

        $this->paid_total = $paid;

        if ($paid >= $total && $total > 0) {
            $this->status = self::PAID;
            $this->paid_at = $this->paid_at ?: now();
        } elseif ($paid > 0) {
            $this->status = self::PARTIAL;
        } else {
            $this->status = self::UNPAID;
        }

        $this->save();

        return !$wasPaid && $this->status === self::PAID;
    }

    /**
     * The customer-facing payment link. Always rebuilt from the invoice, never
     * replayed from storage, so a stale URL is impossible.
     */
    public function payUrl(): string
    {
        return route('deposit.pay', ['token' => $this->token]);
    }
}
