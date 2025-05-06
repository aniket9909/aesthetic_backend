<?php

namespace App\Events;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\Channel;
class ExampleEvent extends Event implements ShouldBroadcast
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $id;
    public function __construct($id)
{
    $this->id = $id;
}
    public function broadcastOn()
    {
        return new Channel('videocall');
    }   
}
