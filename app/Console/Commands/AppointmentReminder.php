<?php

namespace App\Console\Commands;

use App\Events\AppointmentReminder as EventsAppointmentReminder;
use Illuminate\Console\Command;
use App\Models\Business;
use App\Models\EmailTemplate;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AppointmentReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:appointment-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //Log::channel('reminder_log')->info("cron run successfully at " . Carbon::now());
        $businesses = Business::all();
        foreach ($businesses as $business) {

            $company_settings = getCompanyAllSetting($business->created_by, $business->id);

            $timezone = $company_settings['defult_timezone'];
            $reminder = $company_settings['reminder_interval'] ?? null;
            // echo "Timezone: " . $timezone . " - Reminder Interval: " . $reminder . "\n";
            if (!empty($reminder)) {
                //$date = Carbon::now()->timezone($timezone)->format('d-m-Y');
                //$time = Carbon::now()->timezone($timezone)->format('H:i');
                //echo "Current Date: " . $date . " - Current Time: " . $time . "\n";
                $appointment_status = company_setting('appointment_reminder_trigger', $business->created_by, $business->id);
                $appointments = Appointment::with('CustomerData', 'ServiceData', 'LocationData')->where('business_id', $business->id)
                    ->where('appointment_status', $appointment_status)
                    ->orderBy('date', 'ASC')
                    ->where('is_reminder', 0) // Only get appointments that haven't been reminded yet
                    ->whereRaw("STR_TO_DATE(date, '%d-%m-%Y') BETWEEN DATE(NOW()) AND DATE(DATE_ADD(NOW(), INTERVAL $reminder MINUTE))") // Get appointments for today or later
                    ->get();

                foreach ($appointments as $appointment) {
                    $startTime = explode('-', $appointment->time)[0];
                    //echo $startTime . "\n";
                    $appointment_date = $appointment->date;
                    //echo $appointment_date . "\n";
                    $appointmentDateTime = Carbon::createFromFormat('d-m-Y H:i', $appointment_date . ' ' . $startTime);

                    $reminderDateTime = $appointmentDateTime->subMinutes($reminder);
                    //  echo "$reminderDateTime\n"; print_r($reminderDateTime);
                   // $reminderdate = $reminderDateTime->format('d-m-Y');
                    $remindertime = $reminderDateTime->format('H:i');
                    // echo $appointment->id."\n";
                    // echo $remindertime."\n";
                    // echo $time."\n";
                      //$now = Carbon::now()->timezone($timezone);
                        // $nextMinute = $now->copy()->addMinute();
                  
                 

                 //   if ($reminderDateTime <= $now && $reminderDateTime > $nextMinute) {
                        // ✅ SEND SMS HERE
                       // echo "Sending reminder for appointment ID: " . $appointment->id . "\n";
                        $appointment_number = Appointment::appointmentNumberFormat($appointment->id, $appointment->created_by, $appointment->business_id);
                        // echo $appointment_number . "\n";
                        // echo $company_settings['Appointment Reminder'] . "\n";
                        if ((!empty($company_settings['Appointment Reminder']) && $company_settings['Appointment Reminder'] == true)) {
                            $uArr = [
                                'company_name' => $appointment->business->name ?? '',
                                'customer' => $appointment->CustomerData ? $appointment->CustomerData->name : $appointment->name,
                                'service' => $appointment->ServiceData ? $appointment->ServiceData->name : '-',
                                'location' => $appointment->LocationData ? $appointment->LocationData->name : '-',
                                'staff' => $appointment->StaffData->user ? $appointment->StaffData->user->name : '-',
                                'appointment_date' => $appointment->date,
                                'appointment_time' => $appointment->time,
                                'appointment_number' => $appointment_number,
                            ];
                            //echo $appointment->CustomerData->customer->email . "\n";
                            //echo $appointment->email . "\n";
                            $resp = EmailTemplate::sendEmailTemplate('Appointment Reminder', [$appointment->CustomerData ? $appointment->CustomerData->customer->email : $appointment->email], $uArr, $appointment->created_by, $appointment->business_id);
                            // echo "<pre>";
                            //print_r($resp);
                            //echo "</pre>";
                            if (!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) {
                                Log::channel('reminder_log')->info($resp['error']);
                            } else {
                                Log::channel('reminder_log')->info("success");
                            }

                        }
                        event(new EventsAppointmentReminder($appointment));

                        // Mark as reminded so it is not sent again on every cron tick
                        $appointment->is_reminder = 1;
                        $appointment->save();

                    //}

                }

            } else {
                Log::channel('reminder_log')->info("Reminder time not set!");
            }
        }
    }
}
