<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id', 'location_id', 'weekday', 'shift_type',
        'start_time', 'end_time', 'effective_from', 'effective_to',
        'specific_date', 'status', 'replaces_shift_id',
        'business_id', 'created_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'specific_date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForWeekday($query, int $weekday)
    {
        return $query->where('weekday', $weekday);
    }

    public function scopeOnDate($query, $date)
    {
        $date = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);

        return $query->where(function ($q) use ($date) {
            $q->where(function ($q2) use ($date) {
                // continuous / end_dated: weekday match + within effective range
                $q2->whereIn('shift_type', ['continuous', 'end_dated'])
                    ->where('weekday', $date->dayOfWeek)
                    ->where('effective_from', '<=', $date->toDateString())
                    ->where(function ($q3) use ($date) {
                        $q3->whereNull('effective_to')
                            ->orWhere('effective_to', '>=', $date->toDateString());
                    });
            })->orWhere(function ($q2) use ($date) {
                // casual: exact date match
                $q2->where('shift_type', 'casual')
                    ->where('specific_date', $date->toDateString());
            });
        });
    }
}
