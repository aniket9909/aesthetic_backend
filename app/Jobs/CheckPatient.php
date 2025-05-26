<?php

namespace App\Jobs;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\SkinAnalysisController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;

class CheckPatient implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        $apiController = new ApiController();
        $apiController = $apiController->checkPatient(
            $this->payload['patient_number'],
            $this->payload['doctor_number'],
            new Request(
                [
                    'patient_name' => $this->payload['patient_name'],
                    'visit_type' => $this->payload['visit_type']
                ]
            )
        );
    }
}
