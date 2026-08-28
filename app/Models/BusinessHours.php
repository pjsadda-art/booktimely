<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHours extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_name','start_time','end_time','day_off','break_hours','business_id','created_by'
    ];

    public static $weekdays = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    

    public static function dayWiseData($day,$id)
    {
        return BusinessHours::where('created_by',creatorId())->where('business_id',$id)->where('day_name',$day)->first();
    }

}
