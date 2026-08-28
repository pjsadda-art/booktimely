<?php

namespace Workdo\FlexibleDays\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlexibleStaffHours extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static $weekdays = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];



    public static function dayWiseData($day,$id,$staff_id)
    {
        return FlexibleStaffHours::where('created_by',creatorId())->where('business_id',$id)->where('staff_id',$staff_id)->where('day_name',$day)->first();
    }
}
