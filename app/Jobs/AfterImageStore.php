<?php

namespace App\Jobs;

use App\Http\Controllers\SkinAnalysisController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;

class AfterImageStore implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        $skinController = new SkinAnalysisController();
        $skinController->afterImageAnalysis(new Request(['mediaId' => $this->payload['mediaId']]));
    }
}
