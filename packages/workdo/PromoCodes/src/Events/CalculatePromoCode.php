<?php

namespace Workdo\PromoCodes\Events;

use Illuminate\Queue\SerializesModels;

class CalculatePromoCode
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $service;
    public $promocode;

    public $request;

    public function __construct($service, $promocode, $request)
    {
        $this->service = $service;
        $this->promocode = $promocode;
        $this->request = $request;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
