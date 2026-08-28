<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Illuminate\Support\Facades\Auth;
use Workdo\SupportTicket\Events\CreateTicket;


class CreateTicketLis
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
    public function handle(CreateTicket $event)
    {
        $ticket = $event->ticket;

        if (module_is_active('SMS') && company_setting('discord_notification_is')=='on' && !empty(company_setting('SMS New Ticket')) && company_setting('SMS New Ticket')  == true) {

            $uArr = [
                'ticket_name' => $ticket->name,
                'date' => company_date_formate($ticket->created_at)
            ];
            $to = $mobile_no ?? Auth::user()->mobile_no;
            SendMsg::SendMsgs($to , $uArr , 'New Ticket');

        }
    }
}
