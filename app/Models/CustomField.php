<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'value',
        'type',
        'option',
        'show_in_appointment',
        'show_in_quotation',
        'show_in_invoice',
        'show_on_online_widget',
        'business_id',
        'created_by',

    ];

    protected $casts = [
        'show_in_appointment' => 'boolean',
        'show_in_quotation' => 'boolean',
        'show_in_invoice' => 'boolean',
        'show_on_online_widget' => 'boolean',
    ];

}
