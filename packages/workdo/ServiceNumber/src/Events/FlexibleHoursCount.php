<?php

namespace Workdo\PromoCodes\Events;

use Illuminate\Queue\SerializesModels;

class FlexibleHoursCount
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    //noy use
    public $service;
    public $staffSelect;
    public $selectedDate;
    public $selectedSloteTime;

    public function __construct($service, $staffSelect, $selectedDate, $selectedSloteTime)
    {
        $this->service = $service;
        $this->staffSelect = $staffSelect;
        $this->selectedDate = $selectedDate;
        $this->selectedSloteTime = $selectedSloteTime;
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
