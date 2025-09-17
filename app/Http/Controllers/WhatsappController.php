<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class WhatsappController extends Controller
{
    private const WHATSAPP_API_URL = 'https://api.dovesoft.io/REST/directApi/message';
  private $WHATSAPP_HEADERS = [
    'key' => "a2608dfcbeXX",
    'Accept' => 'application/json',
    'wabaNumber' => '919321962947',
    'Content-Type' => 'application/json',
  ];

  private $DOCTOR_NUMBER = '9321962947';

    public function sendDocumentToWhatsApp(Request $request)
    {
        $to = $request->input('to');
        Log::info($request->all());
        $from = $request->input('from');
        if (empty($to) || empty($from)) {
            return response()->json(['error' => 'Both "to" and "from" fields are required.'], 400);
        }
        if (preg_match('/^\d{10}$/', $from)) {
            $from = '91' . $from;
        }
        if (preg_match('/^\d{10}$/', $to)) {
            $to = '91' . $to;
        }
        $body = [
            "messaging_product" => "whatsapp",
            "to" => $to,
            "type" => "document",
            "document" => [
                "caption" => $request->input('caption'),
                "link" => $request->input('link'),
                "filename" => $request->input('filename'),
            ]
        ];

        if (substr($to, 0, 2) === '91' && strlen($request->input('to')) > 10) {
            $to = substr($to, 2);
        }
        $header = [
            'wabaNumber' => $from,
            'Key' => 'a2608dfcbeXX'
        ];

        $response = Http::withHeaders($header)->post(self::WHATSAPP_API_URL, $body);

        if ($response->successful()) {
            Log::info($response->json());
            Log::info('WhatsApp document sent successfully.');
            return response()->json(['success' => true, 'reponse' => $response, 'message' => 'WhatsApp document sent successfully.'], 200);
        } else {
            Log::error('WhatsApp document send error:', $response->json());
            return response()->json(['success' => false, 'error' => 'Failed to send WhatsApp document.', 'details' => $response->json()], 500);
        }
    }

    public function sendTextToWhatsApp(Request $request)
    {
        $to = $request->input('to');
        $from = $request->input('from');
        $message = $request->input('message');
        Log::info($request->all());

        if (empty($to) || empty($from) || empty($message)) {
            return response()->json(['error' => 'Fields "to", "from", and "message" are required.'], 400);
        }
        if (preg_match('/^\d{10}$/', $from)) {
            $from = '91' . $from;
        }
        if (preg_match('/^\d{10}$/', $to)) {
            $to = '91' . $to;
        }

        $body = [
            "messaging_product" => "whatsapp",
            "to" => $to,
            "type" => "text",
            "text" => [
                "body" => $message,
            ]
        ];

        $header = [
            'wabaNumber' => $from,
            'Key' => 'a2608dfcbeXX'
        ];

        $response = Http::withHeaders($header)->post(self::WHATSAPP_API_URL, $body);

        if ($response->successful()) {
            Log::info($response->json());
            Log::info('WhatsApp text sent successfully.');
            return response()->json(['success' => true, 'response' => $response->json(), 'message' => 'WhatsApp text sent successfully.'], 200);
        } else {
            Log::error('WhatsApp text send error:', $response->json());
            return response()->json(['success' => false, 'error' => 'Failed to send WhatsApp text.', 'details' => $response->json()], 500);
        }
    }

    

    public function sendTemplateToWhatsApp(Request $request)
    {
        $to = $request->input('to');
        $value1 = $request->input('value1');

        if (empty($to) || empty($value1)) {
            return response()->json(['error' => 'Fields "to" and "value1" are required.'], 400);
        }

        $body = [
            "messaging_product" => "whatsapp",
            "to" => "91".$to,
            "type" => "template",
            "template" => [
                "name" => "patientconsent",
                "language" => [
                    "code" => "en",
                    "policy" => "deterministic"
                ],
                "components" => [
                    [
                        "type" => "BODY",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $value1
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // $header = [
        //     'wabaNumber' => $this->DOCTOR_NUMBER,
        //     'Key' => 'a2608dfcbeXX'
        // ];

        $response = Http::withHeaders($this->WHATSAPP_HEADERS)->post(self::WHATSAPP_API_URL, $body);

        if ($response->successful()) {
            Log::info('WhatsApp template sent successfully.');
            return response()->json(['success' => true, 'message' => 'Template sent.', 'response' => $response->json()], 200);
        } else {
            Log::error('WhatsApp template send error:', $response->json());
            return response()->json(['success' => false, 'error' => 'Failed to send template.', 'details' => $response->json()], 500);
        }
    }
}
