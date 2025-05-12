<?php

namespace App\Jobs;

use App\Models\Chats;
use App\Models\WebhookInputJson;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;

class StoreWebhookJson implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        WebhookInputJson::create([
            'whatsapp_business_account' => null,
            'json_identification_id' => null,
            'images_url' => null,
            'long_json' => $this->payload,
        ]);
    }
}
