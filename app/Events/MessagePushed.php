<?php

namespace App\Events;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
class MessagePushed extends Event implements ShouldBroadcast
{
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    public function broadcastOn()
    {
        return 'test';
    }   
}
