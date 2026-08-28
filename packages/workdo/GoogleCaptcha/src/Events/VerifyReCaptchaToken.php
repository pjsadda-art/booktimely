<?php

namespace Workdo\GoogleCaptcha\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;

class VerifyReCaptchaToken
{
    use Dispatchable, InteractsWithSockets,SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
