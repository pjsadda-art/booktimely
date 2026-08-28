<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'customer_id',
        'business_id',
        'created_by',
        'notes',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function files()
    {
        return $this->hasMany(JobCardFile::class);
    }
}
