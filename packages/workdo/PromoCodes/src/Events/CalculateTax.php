<?php

namespace Workdo\PromoCodes\Events;

use Illuminate\Queue\SerializesModels;

class CalculateTax
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $service;
    public $discount;

    public function __construct($service, $discount)
    {
        $this->service = $service;
        $this->discount = $discount;
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
