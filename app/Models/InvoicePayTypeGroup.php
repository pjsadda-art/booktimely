<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Workdo\Invoice\Entities\InvoicePayType;

class InvoicePayTypeGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_system_default',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system_default' => 'boolean',
    ];

    public function payTypes()
    {
        return $this->hasMany(InvoicePayType::class, 'pay_type_group_id');
    }
}
