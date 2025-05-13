<?php

namespace App\Jobs;

use App\Models\Chats;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;

class StoreChatMessage implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        // Chats::create($this->payload);
    }
}
