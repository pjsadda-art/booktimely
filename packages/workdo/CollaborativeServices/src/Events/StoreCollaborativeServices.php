<?php

namespace Workdo\CollaborativeServices\Events;

use Illuminate\Queue\SerializesModels;

class StoreCollaborativeServices
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $data;

    public $type;

    public $slug;

    public function __construct($data, $type, $slug)
    {
        $this->data = $data;
        $this->type = $type;
        $this->slug = $slug;
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
