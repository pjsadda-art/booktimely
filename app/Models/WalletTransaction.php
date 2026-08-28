<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Append-only wallet ledger, scoped to customer and business.
 *
 * `customers.wallet_balance` caches the running total; this table is the truth.
 * Rows are never edited or deleted — a correction is another row.
 */
class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'type',
        'amount',
        'balance_after',
        'reference',
        'description',
        'created_by_user',
        'business_id',
        'created_by',
    ];

    public function customerRecord()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Post a movement and refresh the cached balance in one transaction.
     *
     * @param  string  $type  credit | debit
     */
    public static function post(Customer $customer, string $type, $amount, ?string $description = null, ?string $reference = null): WalletTransaction
    {
        return \DB::transaction(function () use ($customer, $type, $amount, $description, $reference) {
            $transaction = self::create([
                'customer_id' => $customer->id,
                'type' => $type === 'debit' ? 'debit' : 'credit',
                'amount' => round((float) $amount, 2),
                'balance_after' => 0,
                'reference' => $reference,
                'description' => $description,
                'created_by_user' => \Auth::check() ? \Auth::user()->id : null,
                'business_id' => $customer->business_id,
                'created_by' => $customer->created_by,
            ]);

            // Written after the row exists so the stamp reflects this movement
            // rather than the balance before it.
            $transaction->balance_after = $customer->reconcileWallet();
            $transaction->save();

            return $transaction;
        });
    }
}
