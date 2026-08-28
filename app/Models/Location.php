<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'image','phone','address','description','business_id','created_by'
    ];

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'staff_locations', 'location_id', 'staff_id')
            ->withTimestamps();
    }

    public function shifts()
    {
        return $this->hasMany(StaffShift::class, 'location_id');
    }
}
