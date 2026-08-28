<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_invoice_id',
        'description',
        'quantity',
        'price',
    ];

    public function invoice()
    {
        return $this->belongsTo(DepositInvoice::class, 'deposit_invoice_id', 'id');
    }
}
