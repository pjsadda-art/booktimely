<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Workdo\ToDo\Events\CompleteToDo;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CompleteToDoLis
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CompleteToDo $event)
    {
        $toDo = $event->toDo;
        $user = User::whereIn('id', explode(',', $toDo->assigned_to))->get()->pluck('name');
        $user_detail = [];
            if (count($user) > 0) {
                foreach ($user as $datasand) {
                    $user_detail[] = $datasand;
                }
            }
        $user = implode(',', $user_detail);

        if (module_is_active('SMS')  && company_setting('discord_notification_is')=='on' && !empty(company_setting('SMS Complete To Do')) && company_setting('SMS Complete To Do')  == true) {

            $uArr = [
                'user_name' => $user
            ];
            $to = $mobile_no ?? Auth::user()->mobile_no;
            SendMsg::SendMsgs($to , $uArr , 'Complete To Do');
        }
    }
}
