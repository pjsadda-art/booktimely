<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notifications = [
            'Create User',
            'Create Appointment',
            'Pending Appointment',
            'Appointment Status Change',
            'Appointment Reminder',
        ];

        $permissions = [
            'user manage',
            'appointment manage',
            'appointment manage',
            'appointment manage',
            'appointment manage',
        ];

        foreach($notifications as $key=>$n){
            $ntfy = Notification::where('action',$n)->where('type','mail')->where('module','general')->count();
            if($ntfy == 0){
                $new = new Notification();
                $new->action = $n;
                $new->status = 'on';
                $new->permissions = $permissions[$key];
                $new->module = 'general';
                $new->type = 'mail';
                $new->save();
            }
        }
    }
}
