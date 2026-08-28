<?php

namespace Workdo\FlexibleDays\Entities;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlexibleDayUtility extends Model
{
    use HasFactory;

    protected $fillable = [];


    public static function flexibleDayTimeSlot($serviceId = null, $date = null, $staffId = null, $flexibleData = null)
    {
        $service = Service::find($serviceId);
        $staff = Staff::whereRaw("FIND_IN_SET($serviceId, service_id)")->where('user_id',$staffId)->first();
        $selectedDate = Carbon::createFromFormat('d-m-Y', $date);
        $dayName = $selectedDate->format('l');  //get dayname using date

        $company_settings = getCompanyAllSetting($service->created_by, $service->business_id);
        $maximum_slot = isset($company_settings['maximum_slot']) ? $company_settings['maximum_slot'] : '1';
        if ($date && $service && $staff) {
            $booked_appointment = Appointment::where('service_id', $serviceId)->where('date', $date)->where('business_id', $service->business_id)
                                ->where('created_by', $service->created_by)
                                ->select('time')->get()->toArray();

            if (module_is_active('FlexibleDays', $service->created_by)) {
                $flexible_staff_hour = FlexibleStaffHours::where('created_by', $service->created_by)->where('business_id', $service->business_id)
                                        ->where('staff_id', $staff->user_id)
                                        ->where('day_name', $dayName)
                                        ->where('day_off', 'off')
                                        ->first();

                $flexible_day_hour = FlexibleDays::where('staff_id', $staff->user_id)->where('date', $selectedDate->format('Y-m-d'))->first();

                $decoded_break_hours = [];
                if ($flexible_day_hour) {
                    $flexible_day_break_hours = FlexibleDayBreaks::where('flexible_days_id', $flexible_day_hour->id)->get();
                    foreach ($flexible_day_break_hours as $flexible_day_break_hour) {
                        $decoded_break_hours[] = json_decode($flexible_day_break_hour->break_hours, true);
                    }
                }
                $duration = $service->duration;
                if ($flexible_day_hour) {
                    $start_time = Carbon::createFromFormat('H:i:s', $flexible_day_hour->start_time);
                    $end_time = Carbon::createFromFormat('H:i:s', $flexible_day_hour->end_time);
                    $break_times = isset($decoded_break_hours) ? $decoded_break_hours : '';
                } else {
                    $start_time = isset($flexible_staff_hour->start_time) ? Carbon::createFromFormat('H:i:s',$flexible_staff_hour->start_time) : '';
                    $end_time = isset($flexible_staff_hour->end_time) ? Carbon::createFromFormat('H:i:s', $flexible_staff_hour->end_time) : '';
                    $break_times = isset($flexible_staff_hour->break_hours) ? json_decode($flexible_staff_hour->break_hours, true) : '';
                }

                $timeSlots = [];
                if($start_time){
                    $currentSlot = clone $start_time;
                    // $now = Carbon::now($company_settings['defult_timezone'])->format('H:i');    //get current time
                    $now = Carbon::now($company_settings['defult_timezone']);
                    $isToday = $selectedDate->isToday();

                    // If the selected date is today, use current time as the cutoff
                    if ($isToday) {
                        $now = $now->format('H:i');
                    } else {
                        // If the date is tomorrow or later, ignore the current time and start from business start time
                        $now = $start_time->format('H:i');
                    }

                    if (is_array($break_times)) {
                        foreach ($break_times as $break) {
                            $breakStart = Carbon::createFromFormat('H:i', $break['start']);
                            $breakEnd = Carbon::createFromFormat('H:i', $break['end']);

                            // Add time slots before the break, excluding booked slots
                            while ($currentSlot->addMinutes( (int) $duration)->lt($breakStart)) {
                                $slot = [
                                    'start' => $currentSlot->copy()->subMinutes( (int) $duration)->format('H:i'),
                                    'end' => $currentSlot->format('H:i'),
                                    'service_id' => $service->id,
                                    'flexible_day' => true,
                                ];

                                // Skip slots before the current time
                                if ($currentSlot->lt($now)) {
                                    continue;
                                }

                                $bookedCount = isSlotBooked($slot, $booked_appointment);
                                if ($bookedCount < $maximum_slot) {
                                    $timeSlots[] = $slot;
                                }
                            }

                            // Skip time slots during the break
                            if ($currentSlot->lte($breakEnd)) {
                                $currentSlot = $breakEnd->copy();
                            }
                        }
                    }

                    // Add remaining time slots after the last break, excluding booked slots
                    while ($currentSlot->addMinutes( (int) $duration)->lte($end_time)) {
                        $slot = [
                            'start' => $currentSlot->copy()->subMinutes((int) $duration)->format('H:i'),
                            'end' => $currentSlot->format('H:i'),
                            'service_id' => $service->id,
                            'flexible_day' => true,
                        ];

                        // Skip slots before the current time
                        if ($currentSlot->lt($now)) {
                            continue;
                        }

                        $bookedCount = isSlotBooked($slot, $booked_appointment);
                        if ($bookedCount < $maximum_slot) {
                            $timeSlots[] = $slot;
                        }
                    }

                    // Flexible Hours
                    if (module_is_active('FlexibleHours',$service->created_by) && !is_null($flexibleData)) {
                        $selectedDate = Carbon::createFromFormat('d-m-Y', $date);
                        $dayName = $selectedDate->format('D');

                        $filtered_flexible_data = $flexibleData->filter(function ($flexible_day) use ($dayName) {
                            $flexible_data = json_decode($flexible_day->days, true);
                            return isset($flexible_data[$dayName]) && $flexible_data[$dayName] === 'on';
                        });
                        foreach($filtered_flexible_data as $data){
                            $startSpecial = Carbon::createFromFormat('H:i:s', $data->start_time);
                            $endSpecial = Carbon::createFromFormat('H:i:s', $data->end_time);
                            $timeSlots = removeSlotsBetweenSpecialHours($timeSlots,$startSpecial, $endSpecial,$data->id);
                        }
                    }
                }
                return $timeSlots;
            }
        }
    }
}
