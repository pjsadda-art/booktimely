<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id', 'location_id', 'business_id', 'created_by',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
