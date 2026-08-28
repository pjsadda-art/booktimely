<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A real payment record against a deposit invoice.
 *
 * The on-the-spot path writes one of these exactly as the gateway path does.
 * Anything less — flipping a status flag without a payment row — leaves the
 * deposit invisible to every financial report.
 */
class DepositInvoicePayment extends Model
{
    use HasFactory;

    /** Methods staff can take at reception. */
    public const MANUAL_METHODS = [
        'cash' => 'Cash',
        'payid' => 'PayID',
        'bank_transfer' => 'Bank Transfer',
        'eftpos' => 'EFTPOS',
        'manual_card' => 'Manual Card',
    ];

    protected $fillable = [
        'deposit_invoice_id',
        'amount',
        'method',
        'reference',
        'notes',
        'payment_date',
        'received_by',
        'business_id',
        'created_by',
    ];

    public function invoice()
    {
        return $this->belongsTo(DepositInvoice::class, 'deposit_invoice_id', 'id');
    }

    public function methodLabel(): string
    {
        return self::MANUAL_METHODS[$this->method] ?? ucfirst(str_replace('_', ' ', (string) $this->method));
    }
}
