<?php

namespace Workdo\Invoice\Entities;

use App\Models\InvoicePayTypeGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePayType extends Model
{
    use HasFactory;

    protected $table = 'invoice_pay_types';

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'created_by',
        'business_id',
        'pay_type_group_id',
    ];

    public function group()
    {
        return $this->belongsTo(InvoicePayTypeGroup::class, 'pay_type_group_id');
    }
}
