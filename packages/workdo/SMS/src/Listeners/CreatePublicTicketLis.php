<?php

namespace Workdo\SMS\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\SMS\Entities\SendMsg;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Workdo\SupportTicket\Events\CreatePublicTicket;

class CreatePublicTicketLis
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
    public function handle(CreatePublicTicket $event)
    {

        $ticket = $event->ticket;

        $isSMSModuleActive = module_is_active('SMS', $ticket->created_by, $ticket->business_id);
        $discordNotificationEnabled = company_setting('discord_notification_is', $ticket->created_by, $ticket->business_id) == 'on';
        $discordNewTicketSetting = company_setting('SMS New Ticket Reply', $ticket->created_by, $ticket->business_id);
        $newTicketSettingEnabled = company_setting('SMS New Ticket Reply', $ticket->created_by, $ticket->business_id) == true;
       
        if (
            $isSMSModuleActive &&
            $discordNotificationEnabled &&
            !empty($discordNewTicketSetting) &&
            $newTicketSettingEnabled
        ) {
            $usr = Auth::user();
            $company_id = $ticket->created_by;
            $business_id = $ticket->business_id;

            if(empty($usr))
            {
                $usr = User::find($company_id);
            }

            $msg = [
                'ticket_name' => $ticket->name,
                'name' => $ticket->name,
                'date' => company_date_formate($ticket->created_at, $ticket->created_by, $ticket->business_id),
                'user_name' => $usr ? $usr->name : '-',
            ];
            $to = $mobile_no ?? Auth::user()->mobile_no;
            SendMsg::SendMsgs($to , $msg , 'New Ticket Reply', $company_id, $business_id);            
        }
    }
}
