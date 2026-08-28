<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'payment_type',
        'amount',
        'coupon_amount',
        'final_amount',
        'tax_amount',
        'promo_code_id',
        'payment_date',
        'appointment_ids',
        'discount',
        'business_id',
        'created_by',
        'tax_type',
    ];

    public function appointment()
    {
        return $this->belongsTo('App\Models\Appointment', 'appointment_id', 'id');
    }
}
