<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','user_id','location_id','service_id','description','business_id','created_by'
    ];

    public function getLocationIdAttribute($id)
    {
        return explode(',',$id);
    }

    public function getServiceIdAttribute($id)
    {
        return explode(',',$id);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }


    // In Staff.php
    // public function location()
    // {
    //     return $this->belongsTo(Location::class, 'location_id', 'id');
    // }

    // public function service()
    // {
    //     return $this->belongsTo(Service::class, 'service_id', 'id');
    // }

    public function Location()
    {
        return  Location::whereIn('id',$this->location_id);
    }

    /**
     * Normalized staff-to-location assignment (replaces the CSV location_id
     * column for new code; the CSV column is kept for backward compatibility).
     */
    public function locations()
    {
        return $this->belongsToMany(Location::class, 'staff_locations', 'staff_id', 'location_id')
            ->withTimestamps();
    }

    public function shifts()
    {
        return $this->hasMany(StaffShift::class, 'staff_id');
    }

    public function Service()
    {
        return  Service::whereIn('id',$this->service_id);
    }

    public static function ColorCode()
    {
        $staffWiseColor = [];
        $staffWiseColor = [
             '#CEEDC1',
             '#FFEDD2',
             '#B4E4CD',
             '#C1E6F9',
             '#FFF5C1',
             '#C3DEFB',
             '#F9D2FF',
             '#B6EDEF',
             '#FFCDB2',
             '#C1CBFF',
             '#FFD8D8',
             '#C9D6DE',
             '#D6C9F2',
             '#FFEDD2',
             '#B4E4CD',
             '#FFF5C1',
             '#B6EDEF',
             '#C1CBFF',
             '#D6C9F2',
             '#DAD4B5',
             '#CDE8E5',
             '#DDDAFC',
             '#C1CBFF',
             '#B4E4CD',
             '#FFDBB5',
             '#CCE0AC',
             '#E7FBE6',
             '#FEFAE0',
             '#F4D9D0',
             '#F8EDED'
        ];

        return $staffWiseColor;
    }
}
