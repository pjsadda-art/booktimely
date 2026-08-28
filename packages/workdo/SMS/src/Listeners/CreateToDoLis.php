<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Illuminate\Support\Facades\Auth;
use Workdo\ToDo\Events\CreateToDo;

class CreateToDoLis
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
    public function handle(CreateToDo $event)
    {
        $toDo = $event->toDo;

        if (module_is_active('SMS')  && company_setting('discord_notification_is')=='on' && !empty(company_setting('SMS New To Do')) && company_setting('SMS New To Do')  == true) {

            $uArr = [
                'name' => $toDo->title,
                'module' => $toDo->sub_module
            ];
            $to = $mobile_no ?? Auth::user()->mobile_no;
            SendMsg::SendMsgs($to , $uArr , 'New To Do');
        }
    }
}
