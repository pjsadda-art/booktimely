<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Points-mode loyalty ledger. Unused in service mode, where the counter lives
 * on CustomerServiceVisit instead — the two modes are mutually exclusive.
 */
class LoyaltyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'type',
        'points',
        'balance_after',
        'description',
        'business_id',
        'created_by',
    ];
}
