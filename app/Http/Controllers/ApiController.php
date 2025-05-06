<?php

namespace App\Http\Controllers;

use App\Doctor;
use App\Models\Appointments;
use App\Models\AppointmentSlot;
use App\Models\Chats;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\WebhookInputJson;
use App\Models\WorkingHour;
use App\Patientmaster;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{

  private array $categories = [
    'greetings' => [
      'hi',
      'hello',
      'hey',
      'good morning',
      'good afternoon',
      'good evening',
      'namaste',
      'howdy',
      'hola',
      'yo',
      'sup',
      'what\'s up',
      'how are you',
      'how are you doing',
      'is anyone there',
      'can you help me',
      'i need help',
      'just wanted to say hi'
    ],
    'asking_name' => [
      'what is your name',
      'who are you',
      'your name please',
      'may i know your name',
      'tell me your name',
      'what do i call you',
      'are you a bot'
    ],
    'bot_check' => [
      'are you a robot',
      'are you real',
      'are you human',
      'is this automated',
      'bot or human'
    ],
    'appointment' => [
      'book appointment',
      'i want to book',
      'schedule consultation',
      'fix appointment',
      'how to book appointment',
      'i want to meet doctor',
      'consult with doctor',
      'appointment',
      'make appointment'
    ],
  ];
  private const WHATSAPP_API_URL = 'https://api.dovesoft.io/REST/directApi/message';
  private const WHATSAPP_HEADERS = [
    'key' => "a2608dfcbeXX",
    'Accept' => 'application/json',
    'wabaNumber' => '919321962947',
    'Content-Type' => 'application/json',
  ];

  private const DOCTOR_NUMBER = '8605254835';

  /**
   * Handle incoming JSON payload from webhook.
   */
  public function handleJsonInput(Request $request): JsonResponse
  {
    if (!$request->isJson()) {
      return response()->json(['error' => 'Invalid content type, JSON expected'], 415);
    }

    $jsonData = $request->all();

    // Handle message status updates
    if ($this->isStatusUpdate($jsonData)) {
      Log::info('Message status update received. Skipping processing.');
      return response()->json(['message' => 'Status update ignored.'], 200);
    } else {
      Log::info('Incoming JSON:', $jsonData);
    }

    // Extract patient details
    $patientName = $jsonData['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'] ?? null;
    $patientNo = $jsonData['entry'][0]['changes'][0]['value']['contacts'][0]['wa_id'] ?? null;

    if (!$patientName || !$patientNo) {
      return response()->json(['error' => 'Missing patient information'], 400);
    }

    Log::info("Patient Name: $patientName, Patient Number: $patientNo");

    // Process user message or interactive input
    $sendMessage = $this->processMessage($jsonData, $patientName, $patientNo);

    // Send response to WhatsApp API
    $this->sendResponseToWhatsApp($sendMessage, $patientNo);
    $this->sendResponseToWhatsApp(["message" => "Available Slots", 'isBooking' => true], $patientNo);
    $this->sendResponseToWhatsApp(["message" => "Please upload a photo if you would like to have your skin analyzed.", 'isBooking' => false], $patientNo);


    // Save the incoming JSON payload
    $this->saveWebhookData($jsonData);

    return response()->json(['message' => 'Full JSON received and stored successfully']);
  }

  /**
   * Check if the incoming JSON is a status update.
   */
  private function isStatusUpdate(array $jsonData): bool
  {
    return isset($jsonData['entry'][0]['changes'][0]['value']['statuses']) &&
      is_array($jsonData['entry'][0]['changes'][0]['value']['statuses']) &&
      count($jsonData['entry'][0]['changes'][0]['value']['statuses']) > 0;
  }

  /**
   * Process the incoming JSON message or interactive input.
   */
  private function processMessage(array $jsonData, string $patientName, string $patientNo): array
  {
    $messageData = $jsonData['entry'][0]['changes'][0]['value']['messages'][0] ?? null;
    $messageId = $jsonData['entry'][0]['changes'][0]['value']['messages'][0]['id'] ?? null;


    if ($messageData) {
      if (isset($messageData['type'])) {
        switch ($messageData['type']) {
          case 'interactive':
            return $this->handleInteractiveMessage($messageData, $patientName, $patientNo);
          case 'image':
            return $this->handleImageMessage($messageData, $patientNo);
        }
      }
    }

    $message = $jsonData['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'] ?? '';
    $this->storeChat([
      'sender_id' => $patientNo,
      'receiver_id' => self::DOCTOR_NUMBER,
      'message_type' => 'text',
      'message_text' => $message,
      'media_url' => null,
      'media_mime_type' => null,
      'media_sha256' => null,
      'media_id' => null,
      'whatsapp_message_id' => $messageId,
    ]);
    return $this->processUserMessage($message);
  }
  public function storeChat(array $data)
  {

    Chats::create($data);

    return response()->json(['message' => 'Chat stored successfully']);
  }
  private function handleImageMessage(array $messageData, string $patientNo): array
  {
    $this->sendResponseToWhatsApp(["message" => "Image analysis will take 20 to 30 seconds to process. Please wait...", 'isBooking' => false], $patientNo);

    $imageId = $messageData['image']['id'] ?? null;
    if (!$imageId) {
      return ['message' => 'Image processing failed. No image ID found.', 'isBooking' => false];
    }

    $chabotResponse = new SkinAnalysisController();
    $response = $chabotResponse->analyzeSkin(new Request(['mediaId' => $imageId]));
    $imageUrl = null;
    $responseData = json_decode($response->getContent(), true);
    if (isset($responseData['result'])) {
      $imageUrl = $responseData['media_url'] ?? null;
      $matchedResponses[] =  $responseData['result'];
    } else {
      $matchedResponses[] = "Let me forward this to our assistant. Please wait...";
    }

    $this->storeChat([
      'sender_id' => $patientNo,
      'receiver_id' => self::DOCTOR_NUMBER,
      'message_type' => 'image',
      'message_text' => "Image received",
      'media_url' => $imageUrl,
      'media_mime_type' => null,
      'media_sha256' => null,
      'media_id' => $imageId,
      'whatsapp_message_id' => null,
    ]);

    Log::info("Image ID: $imageId");

    // Simulate saving image or processing it
    // In a real scenario, you would use the image ID to fetch and process the image
    $matchedResponses = array_map('strval', $matchedResponses); // Ensure all elements are strings
    return ['message' => implode("\n\n", $matchedResponses), 'isBooking' => false];
  }
  /**
   * Handle interactive messages (e.g., list replies).
   */
  private function handleInteractiveMessage(array $messageData, string $patientName, string $patientNo): array
  {
    $interactive = $messageData['interactive'] ?? [];
    $messageId = $messageData['id'] ?? null;
    Log::info('Interactive Message:', $interactive);

    if (isset($interactive['type']) && $interactive['type'] === 'list_reply') {
      $selectedSlot = $interactive['list_reply']['title'] ?? '';

      Log::info("Selected Slot: $selectedSlot");

      $bookingRequest = new Request([
        'doctor_number' => self::DOCTOR_NUMBER,
        'patient_number' => $patientNo,
        'patient_name' => $patientName,
        'selected_slot' => $selectedSlot,
      ]);
      $this->storeChat([
        'sender_id' => $patientNo,
        'receiver_id' => self::DOCTOR_NUMBER,
        'message_type' => 'text',
        'message_text' => $selectedSlot,
        'media_url' => null,
        'media_mime_type' => null,
        'media_sha256' => null,
        'media_id' => null,
        'whatsapp_message_id' => $messageId,
      ]);

      $bookingResponse = $this->bookAppointment($bookingRequest);
      $isBookingSuccessful = $bookingResponse->getData(true)['success'] ?? false;

      return [
        'message' => $isBookingSuccessful ? 'Appointment booked successfully!' : 'Failed to book appointment.',
        'isBooking' => !$isBookingSuccessful,
      ];
    }

    return ['message' => 'Invalid interactive message type.', 'isBooking' => false];
  }

  /**
   * Process user messages and match predefined categories.
   */
  private function processUserMessage(string $message): array
  {
    $matchedResponses = [];
    $isBooking = false;

    foreach ($this->categories as $category => $phrases) {
      foreach ($phrases as $phrase) {
        if ($this->isSimilar($message, $phrase)) {
          $response = match ($category) {
            'appointment' => "You can book an appointment here",
            default => null
          };

          if ($response && !in_array($response, $matchedResponses)) {
            $matchedResponses[] = $response;
            $isBooking = ($category === 'appointment');
          }
        }
      }
    }

    if (empty($matchedResponses)) {
      Log::info("Message not matched. Sending to Python API.");


      $chabotResponse = new SkinAnalysisController();
      $response = $chabotResponse->greetingChatbot(new Request(['question' => $message]));
      // $response = $chabotResponse->chatbot(new Request(['question' => $message]));
      $responseData = json_decode($response->getContent(), true);
      if (isset($responseData['chatbot_response'])) {
        $matchedResponses[] = $responseData['chatbot_response'];
      } else {
        $matchedResponses[] = "Let me forward this to our assistant. Please wait...";
      }

      // $response = Http::withToken(self::BEARER_TOKEN)
      //   ->post("https://router.huggingface.co/hf-inference/models/Qwen/Qwen3-235B-A22B/v1/chat/completions", $body);

      // if ($response->successful()) {
      //   Log::info('API call successful.', ['response' => $response->json()]);
      //   $responseData = $response->json();
      //   Log::info("----------------------------------------------------");
      //   Log::info(json_encode($responseData));
      //   $matchedResponses[] = $responseData['choices'][0]['message']['content'] ?? "Let me forward this to our assistant. Please wait...";
      // } else {
      //   $matchedResponses[] = "Try Again";
      //   Log::error('API call failed.', ['status' => $response->status(), 'response' => $response->body()]);
      // }
      // Log::info("User Message: json_encode($message)");

    }


    return ["message" => implode("\n\n", $matchedResponses), "isBooking" => $isBooking];
  }

  /**
   * Check if a message is similar to a predefined phrase.
   */
  private function isSimilar(string $userMessage, string $phrase): bool
  {
    return stripos($userMessage, $phrase) !== false;
  }

  /**
   * Send a response to WhatsApp API.
   */
  private function sendResponseToWhatsApp(array $sendMessage, string $patientNo)
  {
    $body = $sendMessage['isBooking'] ? $this->getSlotInteractiveBody($patientNo) : [
      'messaging_product' => 'whatsapp',
      'to' => $patientNo,
      'type' => 'text',
      'recipient_type' => 'individual',
      'text' => ['body' => $sendMessage['message']],
    ];
    $this->storeChat([
      'sender_id' => self::DOCTOR_NUMBER,
      'receiver_id' => $patientNo,
      'message_type' => 'text',
      'message_text' => $sendMessage['message'],
      'media_url' => null,
      'media_mime_type' => null,
      'media_sha256' => null,
      'whatsapp_message_id' => null,

    ]);
    $response = Http::withHeaders(self::WHATSAPP_HEADERS)->post(self::WHATSAPP_API_URL, $body);

    if ($response->successful()) {
      Log::info('WhatsApp API Response:', $response->json());
    } else {
      Log::error('WhatsApp API Error:', $response->json());
    }
  }

  /**
   * Generate interactive body for available slots.
   */
  private function getSlotInteractiveBody(string $patientNo): array
  {
    $response = $this->getAvailableSlots(new Request(['doctor_number' => self::DOCTOR_NUMBER]));
    $slots = $response->getData(true)['data']['slots'] ?? [];

    $rows = array_map(fn($slot, $index) => [
      'id' => 'slot_' . $index,
      'title' => $slot['display'],
    ], $slots, array_keys($slots));

    return [
      "messaging_product" => "whatsapp",
      "to" => $patientNo,
      "type" => "interactive",
      "recipient_type" => "individual",
      "interactive" => [
        "type" => "list",
        "header" => ["type" => "text", "text" => "May I assist you in booking a further appointment?"],
        "body" => ["text" => "Please choose a service from the list below:"],
        "footer" => ["text" => "Tap to choose"],
        "action" => [
          "button" => "View Options",
          "sections" => [["title" => "Available Slots", "rows" => $rows]],
        ],
      ],
    ];
  }

  /**
   * Save webhook data into the database.
   */
  private function saveWebhookData(array $jsonData)
  {
    WebhookInputJson::create([
      'whatsapp_business_account' => null,
      'json_identification_id' => null,
      'images_url' => null,
      'long_json' => $jsonData,
    ]);
  }



  /**
   * Get all stored webhook entries.
   */
  public function getAllWebhookInputs(): JsonResponse
  {
    $webhookInputs = WebhookInputJson::all();
    return response()->json($webhookInputs);
  }

  /**
   * Get specific entry by ID.
   */
  public function getWebhookInputById($id): JsonResponse
  {
    $webhookInput = WebhookInputJson::find($id);

    if (!$webhookInput) {
      return response()->json(['error' => 'Webhook input not found'], 404);
    }

    return response()->json($webhookInput);
  }

  /**
   * Update a specific entry.
   */
  public function updateWebhookInput(Request $request, $id): JsonResponse
  {
    $webhookInput = WebhookInputJson::find($id);

    if (!$webhookInput) {
      return response()->json(['error' => 'Webhook input not found'], 404);
    }

    $validatedData = $request->validate([
      'whatsapp_business_account' => 'nullable|string',
      'json_identification_id' => 'nullable|string',
      'images_url' => 'nullable|string',
      'long_json' => 'nullable|array',
    ]);

    $webhookInput->update($validatedData);

    return response()->json([
      'message' => 'Webhook input updated successfully',
      'updated_data' => $webhookInput,
    ]);
  }

  /**
   * Delete a webhook entry.
   */
  public function deleteWebhookInput($id): JsonResponse
  {
    $webhookInput = WebhookInputJson::find($id);

    if (!$webhookInput) {
      return response()->json(['error' => 'Webhook input not found'], 404);
    }

    $webhookInput->delete();

    return response()->json(['message' => 'Webhook input deleted successfully']);
  }
  public function getAvailableSlots(Request $request)
  {
    // Set timezone to India
    date_default_timezone_set('Asia/Kolkata');
    Log::info($request->all());
    try {
      $valdate = $request->validate([
        'doctor_number' => "required|string"
      ]);
      // Get appointment date or default to today's date
      $filterDate = $request->input('appointment_date')
        ? Carbon::parse($request->input('appointment_date'))->startOfDay()
        : Carbon::now();
      $user = User::where('phone', $request->doctor_number)->first();
      if (!$user) {
        return response()->json([
          'status' => 'error',
          'success' => false,
          'error' => true,
          'message' => 'Doctor not found.',
          'data' => [
            'filterDate' => $filterDate->toDateString(),
            'slots' => [],
          ]
        ], 404);
      }
      $user_id = $user->id;
      // Get working hours for the selected day
      $dayName = $filterDate->format('l'); // Monday, Tuesday, etc.
      $workingHours = WorkingHour::where('doctor_id', $user_id)
        ->where('day_of_week', $dayName)
        ->first();

      if (!$workingHours) {
        return response()->json([
          'status' => 'error',
          'success' => false,
          'error' => true,
          'message' => 'No working hours set for the selected day.',
          'data' => [
            'filterDate' => $filterDate->toDateString(),
            'slots' => [],
          ]
        ], 404);
      }

      // Setup working start & end time
      $startTime = Carbon::parse($filterDate->format('Y-m-d') . ' ' . $workingHours->start_time);
      $endTime = Carbon::parse($filterDate->format('Y-m-d') . ' ' . $workingHours->end_time);
      $now = Carbon::now(); // Use the same timezone as set earlier

      // Fetch booked slots for that day
      $bookedSlots = AppointmentSlot::where('doctor_id', $user_id)
        ->whereDate('slot_date', $filterDate->format('Y-m-d'))
        ->get(['start_time', 'end_time']);

      $bookedRanges = [];
      foreach ($bookedSlots as $b) {
        $bookedRanges[] = [
          'start' => Carbon::parse($filterDate->format('Y-m-d') . ' ' . $b->start_time),
          'end' => Carbon::parse($filterDate->format('Y-m-d') . ' ' . $b->end_time),
        ];
      }

      $slots = [];
      $current = $startTime->copy();

      while ($current->copy()->addMinutes(45)->lte($endTime)) {
        $slotStart = $current->copy();
        $slotEnd = $current->copy()->addMinutes(45);

        // Check if the slot is in the past
        $isPast = $slotStart->lt($now);

        // Check overlap with booked slots
        $isBooked = false;
        foreach ($bookedRanges as $range) {
          if (
            $slotStart->lt($range['end']) &&
            $slotEnd->gt($range['start'])
          ) {
            $isBooked = true;
            break;
          }
        }

        if (!$isBooked) {
          $slots[] = [
            'start' => $slotStart->format('H:i'),
            'end' => $slotEnd->format('H:i'),
            'display' => $slotStart->format('h:i A') . ' - ' . $slotEnd->format('h:i A'),
          ];
        }

        $current->addMinutes(45);
      }

      return response()->json([
        'status' => 'success',
        'success' => true,
        'error' => false,
        'message' => 'Available slots fetched successfully.',
        'data' => [
          'filterDate' => $filterDate->toDateString(),
          'slots' => $slots,
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'success' => false,
        'error' => true,
        'message' => 'An error occurred while fetching available slots.',
        'error' => $e->getMessage()
      ], 500);
    }
  }
  public function bookAppointment(Request $request)
  {
    try {
      //code...

      // Validate the incoming request
      $validatedData = $request->validate([
        'patient_number' => 'required|string|max:255',
        'patient_name' => 'nullable|string|max:255',
        'doctor_number' => 'required|string|max:255',
        'selected_slot' => 'required|string|max:255',

      ]);
      $user = User::where('phone', $request->doctor_number)->first();
      if (!$user) {
        return response()->json([
          'success' => true,
          'message' => 'User not found.',
          'appointment' => [],
        ], 404);
      }
      $user_id = $user->id; // Use authenticated user

      $patient = Patient::where('p_phone', $request->patient_number)->first();
      if (!$patient) {
        $patient = new Patient();
        $patient->doctor_id = $user_id;
        $patient->pname = $request->patient_name;
        $patient->p_phone = $request->patient_number;

        if (!$patient->save()) {
          return response()->json([
            'success' => true,
            'error' => true,
            'message' => 'Patient not store.',
            'appointment' => [],
          ], 404);
        }
      }

      // Assign start and end times
      $selectedSlot = $request->selected_slot;

      // Remove "AM" and "PM" from the string
      $timeParts = explode(" - ", $selectedSlot);

      $startTime = DateTime::createFromFormat('h:i A', trim($timeParts[0]))->format('H:i:s');
      $endTime = DateTime::createFromFormat('h:i A', trim($timeParts[1]))->format('H:i:s');

      // Create a slot
      $slot = AppointmentSlot::create([
        'doctor_id' => $user_id,
        'slot_date' => Carbon::parse($request->appointment_date)->format('Y-m-d'),
        'start_time' => $startTime,
        'end_time' => $endTime,
        'is_booked' => true
      ]);

      // Create the appointment
      $appointment = Appointments::create([
        'doctor_id' => $user_id,
        'patient_id' => $patient->pid,
        'patient_name' => $request->patient_name,
        'patient_email' => null,
        'appointment_date' => Carbon::parse($request->appointment_date)->format('Y-m-d'),
        'slot_id' => $slot->id,
        'status' => "booked",
      ]);

      // Return a JSON response
      return response()->json([
        'success' => true,
        'error' => false,
        'message' => 'Appointment created successfully.',
        'appointment' => $appointment,
      ], 201);
    } catch (\Throwable $th) {
      //throw $th;
      return response()->json([
        'success' => true,
        'error' => true,
        'message' => $th->getMessage(),
        'appointment' => [],
      ], 500);
    }
  }

  public function getAnalysis(Request $request)
  {
    try {
      //code...
      

      if (!$request->has('doctor_id') || !$request->has('patient_number')) {
        return response()->json([
          'success' => true,
          'error' => true,
          'message' => 'Missing required parameters: doctor_id or patient_number.',
          'analysis' => [],
        ], 400);
      }
      $doctor = Doctor::where('pharmaclient_id', $request->doctor_id)->first();
      if (!$doctor) {
        return response()->json([
          'success' => true,
        'error' => true,
          'message' => 'Doctor not found.',
          'analysis' => [],
        ], 404);
      }
      $patient = Patientmaster::where('phone', $request->patient_number)->first();
      if (!$patient) {
        return response()->json([
          'success' => true,
          'error' => true,
          'message' => 'Patient not found.',
          'analysis' => [],
        ], 404);
      }


      $messages = Chats::where(function ($query) use ($patient) {
        $query->where('sender_id', $doctor->mobile_no ?? null)
          ->where('receiver_id', $patient->phone ?? null);
      })
        ->orWhere(function ($query) use ($patient) {
          $query->where('sender_id', $patient->p_phone ?? null)
            ->where('receiver_id', $doctor->mobile_no ?? null);
        })
        ->orderBy('created_at', 'asc')
        ->get();


      $imageAnalysis = [];

      foreach ($messages as $chat) {
        if ($doctor->mobile_no == $chat->sender_id) {
          $chat->from = 'Doctor';
        } else {
          $chat->from = 'User';
        }
        if($chat->message_type == 'image'){
          $imageAnalysis[] = [
            'analysis' => $chat->analysis,
            'output' => $chat->output,
            'image_url' => $chat->media_url,
            'media_id' => $chat->media_id,
          ];

        }
      }
      return response()->json([
        'success' => true,
        'error' => false,
        'message' => 'Analysis fetched successfully.',
        'analysis' => $messages,
        'imageAnalysis' => $imageAnalysis,
      ], 200);
    } catch (\Throwable $th) {
      Log::error('Error fetching analysis:', [
        'error' => $th->getMessage(),
        'request' => $request->all(),
      ]);
      return response()->json([
        'success' => true,
        'error' => true,
        'message' => $th->getMessage(),
        'analysis' => [],
      ], 500);
      //throw $th;
    }
  }
}
