<?php

namespace App\Http\Controllers;

use App\Doctor;
use App\Jobs\CheckPatient;
use App\Jobs\StoreChatMessage;
use App\Jobs\StoreWebhookJson;
use App\Models\Chats;
use App\Models\ConverstionState;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\WebhookInputJson;
use App\Patientmaster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;

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
      'how are you doing'
      // 'is anyone there',
      // 'can you help me',
      // 'i need help',
      // 'just wanted to say hi'
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
  private $WHATSAPP_HEADERS = [
    'key' => "a2608dfcbeXX",
    'Accept' => 'application/json',
    'wabaNumber' => '919321962947',
    'Content-Type' => 'application/json',
  ];

  private $DOCTOR_NUMBER = '9321962947';

  /**
   * Handle incoming JSON payload from webhook.
   */
  public function handleJsonInput(Request $request)
  {
    try {
      //code...

      if (!$request->isJson()) {
        return response()->json(['error' => 'Invalid content type, JSON expected'], 415);
      }

      $jsonData = $request->all();
      Log::info('Incoming JSON:', $jsonData);

      // Handle message status updates
      if ($this->isStatusUpdate($jsonData)) {
        // Log::info('Message status update received. Skipping processing.');
        return response()->json(['message' => 'Status update ignored.'], 200);
      } else {
        // Log::info('Incoming JSON:', $jsonData);
      }

      // Extract patient details
      $this->DOCTOR_NUMBER = $jsonData['entry'][0]['changes'][0]['value']['metadata']['display_phone_number'] ?? null;
      $this->WHATSAPP_HEADERS['wabaNumber'] = $this->DOCTOR_NUMBER;

      if ($this->DOCTOR_NUMBER && substr($this->DOCTOR_NUMBER, 0, 2) === '91' && strlen($this->DOCTOR_NUMBER) > 10) {
        $this->DOCTOR_NUMBER = substr($this->DOCTOR_NUMBER, 2);
      }

      $patientName = $jsonData['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'] ?? null;
      $patientNo = $jsonData['entry'][0]['changes'][0]['value']['contacts'][0]['wa_id'] ?? null;

      if (!$patientName || !$patientNo) {
        return response()->json(['error' => 'Missing patient information'], 400);
      }


      dispatch(new CheckPatient([
        'patient_number' => $patientNo,
        'doctor_number' => $this->DOCTOR_NUMBER,
        'patient_name' => $patientName,
        'visit_type' => '2',
      ]));

      Log::info("Patient Name: $patientName, Patient Number: $patientNo");

      // Process user message or interactive input
      $sendMessage = $this->processMessage($jsonData, $patientName, $patientNo);
      $handleConversationFlow = $this->handleConversationFlow($patientNo, $sendMessage['message']);


      // Send response to WhatsApp API
      $this->sendResponseToWhatsApp($sendMessage, $patientNo);


      Log::info("Handle Conversation Flow: $handleConversationFlow");
      if ($handleConversationFlow != null) {
        $this->sendResponseToWhatsApp(["message" => $handleConversationFlow, 'isBooking' => false], $patientNo);
      } else {
        $tempNumber = $patientNo;
        if (substr($tempNumber, 0, 2) === '91' && strlen($tempNumber) > 10) {
          $tempNumber = substr($tempNumber, 2);
        }
        $state = ConverstionState::where('user_id', $tempNumber)->first();

        if (!empty($state) && ($state->ask_image == false || $state->ask_image == null)) {
          $this->sendResponseToWhatsApp(["message" => "Please upload a photo if you would like to have your skin analyzed.", 'isBooking' => false], $patientNo);
        }
        if (!empty($state) && ($state->ask_appointment == false || $state->ask_appointment == null)) {
          $this->sendResponseToWhatsApp(["message" => "Available Slots", 'isBooking' => true], $patientNo);
        }
      }


      // Save the incoming JSON payload
      $this->saveWebhookData($jsonData);

      return response()->json(['message' => 'Full JSON received and stored successfully']);
    } catch (\Throwable $th) {
      //throw $th;
      Log::error('Error in handleJsonInput:', [
        'error' => $th->getMessage(),
        'request' => $request->all(),
      ]);
    }
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
    $tempNumber = $patientNo;
    if (substr($tempNumber, 0, 2) === '91' && strlen($tempNumber) > 10) {
      $tempNumber = substr($tempNumber, 2);
    }
    $converstionState = ConverstionState::firstOrCreate(
      ['user_id' => $tempNumber],
      [
        'current_state' => 'idle',
        'flow_type' => null,
        'data' => [],
        'is_active' => true,
      ]
    );

    if ($messageData) {
      if (isset($messageData['type'])) {
        switch ($messageData['type']) {
          case 'interactive':
            return $this->handleInteractiveMessage($messageData, $patientName, $patientNo);
          case 'image':
            if ($converstionState != null && $converstionState->current_state != 'idle' && $converstionState->current_state != 'confirmed') {
              return ['message' => $messageData['text']['body'], 'isBooking' => false];
            } else {
              return $this->handleImageMessage($messageData, $patientNo);
            }
        }
      }
    }
    if ($converstionState != null && $converstionState->current_state != 'idle' && $converstionState->current_state != 'confirmed') {

      return ['message' => $messageData['text']['body'], 'isBooking' => false];
    }

    $message = $jsonData['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'] ?? '';

    $this->storeChat([
      'sender_id' => $tempNumber,
      'receiver_id' => $this->DOCTOR_NUMBER,
      'message_type' => 'text',
      'message_text' => $message,
      'analysis' => null,
      'output' => null,
      'media_url' => null,
      'media_mime_type' => null,
      'media_sha256' => null,
      'media_id' => null,
      'whatsapp_message_id' => $messageId,
      'date' => Carbon::now()->toDateTimeString()
    ]);
    return $this->processUserMessage($message);
  }
  public function storeChat(array $data)
  {

    // Chats::create($data);
    dispatch(new StoreChatMessage($data));

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
    $analysis = null;
    $responseData = json_decode($response->getContent(), true);
    // Log::info(json_encode($responseData));
    if (isset($responseData['result'])) {
      $imageUrl = $responseData['media_url'] ?? null;
      $analysis = $responseData['analysis'] ?? null;
      Log::info("Image URL: $imageUrl");
      // $matchedResponses[] =  $responseData['result'];
      $analysis = is_array($analysis) ? $analysis : [$analysis];
      $matchedResponses[] = implode(', ', $analysis);
      $output[] = $responseData['result'];
    } else {
      $matchedResponses[] = "Let me forward this to our assistant. Please wait...";
      $output[] = "Output not generated";
    }

    // $matchedResponses = array_map('strval', $matchedResponses); // Ensure all elements are strings
    if (substr($patientNo, 0, 2) === '91' && strlen($patientNo) > 10) {
      $patientNo = substr($patientNo, 2);
    }
    $state = ConverstionState::where('user_id', $patientNo)->first();
    $state->ask_image = true;
    $state->save();

    $this->storeChat([
      'sender_id' => $patientNo,
      'receiver_id' => $this->DOCTOR_NUMBER,
      'message_type' => 'image',
      'message_text' => "Image received",
      'analysis' => implode(', ', $analysis),
      'output' => implode(', ', $output),
      'media_url' => $imageUrl,
      'media_mime_type' => null,
      'media_sha256' => null,
      'media_id' => $imageId,
      'whatsapp_message_id' => null,
      'date' => Carbon::now()->toDateTimeString()
    ]);

    // Log::info("Image ID: $imageId");
    // Log::info(json_encode($matchedResponses));

    // Simulate saving image or processing it
    // In a real scenario, you would use the image ID to fetch and process the image

    return ['message' => (implode("\n\n", $matchedResponses)), 'isBooking' => false];
  }
  /**
   * Handle interactive messages (e.g., list replies).
   */
  private function handleInteractiveMessage(array $messageData, string $patientName, string $patientNo): array
  {
    $interactive = $messageData['interactive'] ?? [];
    $messageId = $messageData['id'] ?? null;
    // Log::info('Interactive Message:', $interactive);

    if (isset($interactive['type']) && $interactive['type'] === 'list_reply') {
      $selectedSlot = $interactive['list_reply']['title'] ?? '';

      // Log::info("Selected Slot: $selectedSlot");

      $bookingRequest = new Request([
        'doctor_number' => $this->DOCTOR_NUMBER,
        'patient_number' => $patientNo,
        'patient_name' => $patientName,
        'selected_slot' => $selectedSlot,
      ]);

      if (substr($patientNo, 0, 2) === '91' && strlen($patientNo) > 10) {
        $patientNo = substr($patientNo, 2);
      }
      $this->storeChat([
        'sender_id' => $patientNo,
        'receiver_id' => $this->DOCTOR_NUMBER,
        'message_type' => 'text',
        'message_text' => $selectedSlot,
        'analysis' => null,
        'output' => null,
        'media_url' => null,
        'media_mime_type' => null,
        'media_sha256' => null,
        'media_id' => null,
        'whatsapp_message_id' => $messageId,
        'date' => Carbon::now()->toDateTimeString()
      ]);
      Log::info("Selected Slot: $selectedSlot");

      $bookingResponse = $this->bookAppointment($bookingRequest);
      $isBookingSuccessful = $bookingResponse->getData(true)['success'] ?? false;
      if ($isBookingSuccessful) {
        $convertionState = ConverstionState::firstOrCreate(
          ['user_id' => $patientNo],
          [
            'current_state' => 'book',
            'flow_type' => null,
            'data' => [],
            'is_active' => true,
          ]
        );
        if ($convertionState->current_state != 'confirmed') {

          $convertionState->current_state = 'book';
          $convertionState->ask_appointment = true;
          $convertionState->save();
        } else {
          $convertionState->ask_appointment = true;
          $convertionState->save();
        }
        Log::info("-------------------------------------------------------------------------------------------------------------------------------------------------------------");
        Log::info($convertionState);
        Log::info("-------------------------------------------------------------------------------------------------------------------------------------------------------------");
      }
      // $this->handleConversationFlow($patientNo, $selectedSlot);

      return [
        'message' => $isBookingSuccessful ? "Appointment booked for $selectedSlot . Thank You." : 'Failed to book appointment.',
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
            'greetings' => "Hello!👋 Welcome to Aesthetic AI – your personal skincare assistant. I'm here to help you with all your skin-related concerns. Let's get started!",
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
      // $response = $chabotResponse->greetingChatbot(new Request(['question' => $message]));

      $response = $chabotResponse->chatbot(new Request(['question' => $message]));
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

  function handleConversationFlow($userId, $messageText)
  {
    try {
      //code...

      if (substr($userId, 0, 2) === '91' && strlen($userId) > 10) {
        $userId = substr($userId, 2);
      }

      // Fetch or create the conversation state
      $state = ConverstionState::firstOrCreate(
        ['user_id' => $userId],
        [
          'current_state' => 'idle',
          'flow_type' => null,
          'data' => [],
          'is_active' => true,
        ]
      );
      if ($state->current_state === 'idle') {
        return null;
      }
      if ($state->current_state === 'confirmed') {
        return null;
      }

      $data = $state->data ?? [];
      $nextMessage = '';

      if ($state->current_state === 'book') {
        $state->current_state = 'ask_name';
        $state->save();
        return 'Please provide your full name.';
      }
      $this->storeChat([
        'sender_id' => $userId,
        'receiver_id' => $this->DOCTOR_NUMBER,
        'message_type' => 'text',
        'message_text' => $messageText,
        'analysis' => null,
        'output' => null,
        'media_url' => null,
        'media_mime_type' => null,
        'media_sha256' => null,
        'media_id' => null,
        'whatsapp_message_id' => null,
        'date' => Carbon::now()->toDateTimeString()
      ]);

      $patient = Patientmaster::where('mobile_no', $userId)->first();

      switch ($state->current_state) {
        case 'ask_name':
          $data['name'] = $messageText;
          $state->current_state = 'ask_age';
          $state->data = $data;
          $state->save();
          $patient->patient_name = $messageText;
          $patient->save();

          return 'Thanks! Now please provide your age.';

        case 'ask_age':
          $data['age'] = $messageText;
          $state->current_state = 'ask_gender';
          $state->data = $data;
          $state->save();
          $patient->age = $messageText;
          $patient->save();


          return 'Great! Please provide your gender.';

        case 'ask_gender':
          $data['gender'] = $messageText;
          $state->current_state = 'ask_primary_concern';
          $state->data = $data;

          $state->save();
          // Store gender as 1 for male, 2 for female, else store as-is
          $genderValue = strtolower(trim($messageText));
          if ($genderValue === 'male') {
            $patient->gender = 1;
          } elseif ($genderValue === 'female') {
            $patient->gender = 2;
          } else {
            $patient->gender = $messageText;
          }
          $patient->save();

          return "
          Thanks! What is your primary skin concern today? You can choose one or more from the list below:
          
      Options (multiple choice):

      Wrinkles / Fine Lines
      Pigmentation / Dark Spots
      Acne / Acne Scars
      Dull or Uneven Skin Tone
      Large Pores
      Sagging Skin / Loss of Firmness
      Under-Eye Circles / Puffiness
      Dry / Dehydrated Skin
      Oily / Acne-Prone Skin
      Redness / Sensitive Skin
      Unwanted Facial Hair
      Sun Damage
      Other (Please specify)
          ";

        case 'ask_primary_concern':
          $data['primary_concern'] = $messageText;
          $state->current_state = 'existing_condition';
          $state->data = $data;
          $state->save();
          $patient->primary_concern = $messageText;
          $patient->save();
          return "Do you have any existing medical conditions or a history of major illnesses? If yes, please specify. If not, you can reply with 'No'.";
        case 'existing_condition':
          $data['existing_condition'] = $messageText;
          $state->current_state = 'allergy';
          $state->data = $data;
          $state->save();
          $patient->existing_condition = $messageText;
          $patient->save();
          return "Do you have any allergies — including to medications, skincare products, or food? If yes, please specify. If not, you can reply with 'No'.";
        case 'allergy':
          $data['allergy'] = $messageText;
          $state->current_state = 'confirmed';
          $state->data = $data;
          $state->save();
          $patient->allergy = $messageText;
          $patient->save();
          return "Thank you for the information. Your appointment is booked!
Please upload a photo if you would like to have your skin analyzed.
          ";
        case 'confirmed':
          return "Your appointment is already booked. To book again, type 'book'.";

        default:
          return null;
      }
    } catch (\Throwable $th) {
      //throw $th;
      Log::error('Error in handleConversationFlow:', [
        'error' => $th->getMessage(),
        'userId' => $userId,
        'messageText' => $messageText,
      ]);
      // die;
      return null;
    }
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
    if (substr($patientNo, 0, 2) === '91' && strlen($patientNo) > 10) {
      $patientNo = substr($patientNo, 2);
    }
    $this->storeChat([
      'sender_id' => $this->DOCTOR_NUMBER,
      'receiver_id' => $patientNo,
      'message_type' => 'text',
      'message_text' => $sendMessage['message'],
      'analysis' => null,
      'output' => null,
      'media_url' => null,
      'media_mime_type' => null,
      'media_sha256' => null,
      'whatsapp_message_id' => null,
      'date' => Carbon::now()->toDateTimeString()

    ]);
    $response = Http::withHeaders($this->WHATSAPP_HEADERS)->post(self::WHATSAPP_API_URL, $body);

    if ($response->successful()) {
      Log::info('WhatsApp API Response success:');
      // Log::info('WhatsApp API Response:', $response->json());
    } else {
      Log::error('WhatsApp API Error:', $response->json());
    }
  }

  /**
   * Generate interactive body for available slots.
   */
  private function getSlotInteractiveBody(string $patientNo): array
  {
    $response = $this->getAvailableSlots(new Request(['doctor_number' => $this->DOCTOR_NUMBER]));
    if (
      (is_array($response) && ($response['error'] ?? false) === true) ||
      (is_object($response) && method_exists($response, 'getData') && ($response->getData(true)['error'] ?? false) === true)
    ) {
      return [
        "messaging_product" => "whatsapp",
        "to" => $patientNo,
        "type" => "text",
        "recipient_type" => "individual",
        "text" => ["body" => $response['message']],
      ];
    }
    $slots = is_array($response) ? ($response['data']['slots'] ?? []) : ($response->getData(true)['data']['slots'] ?? []);

    $rows = array_map(fn($slot, $index) => [
      'id' => 'slot_' . $index,
      'title' => $slot['slot'],
    ], array_slice($slots, 0, 10), array_keys(array_slice($slots, 0, 10)));

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
    // WebhookInputJson::create([
    //   'whatsapp_business_account' => null,
    //   'json_identification_id' => null,
    //   'images_url' => null,
    //   'long_json' => $jsonData,
    // ]);
    dispatch(new StoreWebhookJson($jsonData));
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

    // Log::info($request->all());
    // try {
    // Validate the incoming request
    $doctorNumber = $request->input('doctor_number');
    if (!$doctorNumber) {
      return response()->json([
        'status' => 'error',
        'success' => false,
        'error' => true,
        'message' => 'Doctor number is required.',
        'data' => [
          'filterDate' => null,
          'slots' => [],
        ]
      ], 400);
    }
    $doctor = Doctor::where('mobile_no', $doctorNumber)->first();
    if (!$doctor) {
      return ['message' => 'Doctor not found.', 'isBooking' => false];
    }
    $doctor_id = $doctor->pharmaclient_id;
    $establishId = DB::table('docexa_medical_establishments_medical_user_map')->where('medical_user_id', $doctor_id)->first();
    if (!$establishId) {
      return ['message' => 'Establishment ID not found.', 'isBooking' => false];
    }

    $clinicId = DB::table('docexa_clinic_user_map')->where('user_map_id', $establishId->id)->first();
    if (!$clinicId) {
      return ['message' => 'Clinic ID not found.', 'isBooking' => false];
    }


    $doctorApi = new DoctorsApi();
    $bookingResponse = $doctorApi->slotdetails($establishId->id, Carbon::now()->format('Y-m-d'), $clinicId->id)->getData(true);
    $currentTime = Carbon::now()->format('H:i');

    // Filter slots to only include those after the current time
    $filteredSlots = array_filter($bookingResponse['slot'] ?? [], function ($slot) use ($currentTime) {
      return isset($slot['slot']) && $slot['slot'] > $currentTime;
    });

    // Re-index array to have sequential keys
    $filteredSlots = array_values($filteredSlots);

    $bookingResponse['slot'] = $filteredSlots;
    if ($bookingResponse['status'] === 'success') {
      return response()->json([
        'status' => 'success',
        'success' => true,
        'error' => false,
        'message' => 'Available slots fetched successfully.',
        'data' => [
          'filterDate' => Carbon::now()->toDateString(),
          'slots' => $bookingResponse['slot'] ?? collect([]),
        ]
      ]);
    } else {
      return response()->json([
        'status' => 'error',
        'success' => false,
        'error' => true,
        'message' => 'Failed to fetch available slots.',
        'data' => [
          'filterDate' => Carbon::now()->toDateString(),
          'slots' => collect([]),
        ]
      ], 500);
    }



    // } catch (\Exception $e) {
    //   return response()->json([
    //     'status' => 'error',
    //     'success' => false,
    //     'error' => true,
    //     'message' => 'An error occurred while fetching available slots.',
    //     'error' => $e->getMessage()
    //   ], 500);
    // }
  }
  public function bookAppointment(Request $request)
  {
    try {
      // Log::info($request->all());
      $doctorNumber = $request->input('doctor_number');
      if (!$doctorNumber) {
        return response()->json([
          'status' => 'error',
          'success' => false,
          'error' => true,
          'message' => 'Doctor number is required.',
          'data' => [
            'filterDate' => null,
            'slots' => [],
          ]
        ], 400);
      }
      $pastientNumber = $request->input('patient_number');
      if (substr($pastientNumber, 0, 2) === '91' && strlen($pastientNumber) > 10) {
        $pastientNumber = substr($pastientNumber, 2);
      }
      if (!$pastientNumber) {
        Log::info("Patient number is required.");
        return response()->json([
          'status' => 'error',
          'success' => false,
          'error' => true,
          'message' => 'Patient number is required.',
          'data' => [
            'filterDate' => null,
            'slots' => [],
          ]
        ], 400);
      }
      $patient = Patientmaster::where('mobile_no', $pastientNumber)->first();
      if (!$patient) {
        return response()->json([
          'status' => 'error',
          'success' => false,
          'error' => true,
          'message' => 'Patient not found.',
          'data' => [
            'filterDate' => null,
            'slots' => [],
          ]
        ], 400);
      }
      $doctor = Doctor::where('mobile_no', $doctorNumber)->first();
      if (!$doctor) {
        return ['message' => 'Doctor not found.', 'isBooking' => false];
      }
      $doctor_id = $doctor->pharmaclient_id;
      $establishId = DB::table('docexa_medical_establishments_medical_user_map')->where('medical_user_id', $doctor_id)->first();
      if (!$establishId) {
        return ['message' => 'Establishment ID not found.', 'isBooking' => false];
      }

      $clinicId = DB::table('docexa_clinic_user_map')->where('user_map_id', $establishId->id)->first();
      if (!$clinicId) {
        return ['message' => 'Clinic ID not found.', 'isBooking' => false];
      }
      $sku = DB::table('docexa_esteblishment_user_map_sku_details')->where('user_map_id', $clinicId->id)->first();
      if (!$establishId) {
        return ['message' => 'sku ID not found.', 'isBooking' => false];
      }
      $request->merge([
        'appointment_date' => Carbon::now()->format('Y-m-d'),
        'schedule_time' => $request->selected_slot,
        'schedule_date' => Carbon::now()->format('Y-m-d'),
        'clinic_id' => $clinicId->id,
        'user_map_id' => $establishId->id,
        'sku_id' => $sku->id,
        'payment_mode' => "direct",
        'schedule_remark' => "",
        'gender' => $request->gender,
        'patient_id' => $patient->patient_id,
        'patient_name' => $patient->patient_name,
        'patient_mobile_no' => $patient->mobile_no,
        'age' => $request->age,
        'email' => $request->email,
      ]);
      $bookAppointment = new DoctorsApi();
      $result = $bookAppointment->createAppointmentV4($request);

      // Return a JSON response
      return response()->json([
        'success' => true,
        'error' => false,
        'message' => 'Appointment created successfully.',
        'appointment' => $result,
      ], 201);
    } catch (\Throwable $th) {
      throw $th;
      return response()->json([
        'success' => false,
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
          'patient' => [],
          'chats' => [],
          'analysis' => [],
        ], 400);
      }
      // $doctor = Doctor::where('pharmaclient_id', $request->doctor_id)->first();
      $doctor = DB::table(env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map')->where('id', $request->doctor_id)->join(env('DB_DATABASE') . '.docexa_doctor_master', 'docexa_doctor_master.pharmaclient_id', env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map.medical_user_id')->first();
      if (!$doctor) {
        return response()->json([
          'success' => true,
          'error' => true,
          'message' => 'Doctor not found.',
          'analysis' => [],
          'patient' => [],
          'chats' => [],
        ], 404);
      }
      $patient = Patientmaster::where('mobile_no', $request->patient_number)->first();
      if (!$patient) {
        return response()->json([
          'success' => true,
          'error' => true,
          'message' => 'Patient not found.',
          'analysis' => [],
          'patient' => [],
          'chats' => [],
        ], 404);
      }
      $patientInfo = [
        'patient_id' => $patient->patient_id,
        'patient_name' => $patient->patient_name,
        'mobile_no' => $patient->mobile_no,
        'age' => $patient->age,
        'gender' => $patient->gender,
        'allergy' => $patient->allergy,
        'primary_concern' => $patient->primary_concern,
        'existing_condition' => $patient->existing_condition

      ];
      $messages = Chats::where(function ($query) use ($patient, $doctor) {
        $query->where('sender_id', $doctor->mobile_no ?? null)
          ->where('receiver_id', $patient->mobile_no ?? null);
      })
        ->orWhere(function ($query) use ($patient, $doctor) {
          $query->where('sender_id', $patient->mobile_no ?? null)
            ->where('receiver_id', $doctor->mobile_no ?? null);
        })
        ->orderBy(DB::raw('Date(now())'), 'asc')
        ->get();


      $imageAnalysis = [];

      foreach ($messages as $chat) {
        if ($doctor->mobile_no == $chat->sender_id) {
          $chat->from = 'Doctor';
        } else {
          $chat->from = 'User';
        }
        $baseUrl = url('/skin_images/');


        if ($chat->message_type == 'image') {
          if ($chat->media_id) {
            $imageName = $chat->media_id . '.png'; // or .jpg if needed

            // Build full image URL using Lumen's `url()` helper
            $imageUrl = url('images/' . $imageName);

            $chat->media_url = url('images/' . $chat->media_id . '.png');
          }

          $imageAnalysis[] = [
            'analysis' => $chat->analysis,
            'output' => $chat->output,
            'image_url' => $chat->media_url ?? null,
            'media_id' => $chat->media_id,
          ];
        }
      }
      return response()->json([
        'success' => true,
        'error' => false,
        'message' => 'Analysis fetched successfully.',
        // Only include chats where is_visible is true or null (not 0)
        'chats' => $messages->filter(function ($chat) {
          return $chat->is_visible === null || $chat->is_visible == 1;
        })->values(),
        'imageAnalysis' => $imageAnalysis,
        'patient' => $patientInfo,
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
        'patient' => [],
        'chats' => [],
      ], 500);
      //throw $th;
    }
  }

  public function getAfterImages(Request $request)
  {
    try {
      if (!$request->has('doctor_id') || !$request->has('patient_number')) {
        return response()->json([
          'success' => false,
          'error' => true,
          'message' => 'Missing required parameters: doctor_id or patient_number.',
          'images' => [],
        ], 400);
      }
      // $doctor = Doctor::where('pharmaclient_id', $request->doctor_id)->first();
      $doctor = DB::table(env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map')->where('id', $request->doctor_id)->join(env('DB_DATABASE') . '.docexa_doctor_master', 'docexa_doctor_master.pharmaclient_id', env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map.medical_user_id')->first();
      if (!$doctor) {
        return response()->json([
          'success' => false,
          'error' => true,
          'message' => 'Doctor not found.',
          'images' => [],
        ], 404);
      }
      $patient = Patientmaster::where('mobile_no', $request->patient_number)->first();
      if (!$patient) {
        return response()->json([
          'success' => false,
          'error' => true,
          'message' => 'Patient not found.',
          'images' => [],
        ], 404);
      }



      $images = Chats::where(function ($query) use ($patient, $doctor) {
        $query->where(function ($q) use ($patient, $doctor) {
          $q->where('sender_id', $doctor->mobile_no ?? null)
            ->where('receiver_id', $patient->mobile_no ?? null);
        })->orWhere(function ($q) use ($patient, $doctor) {
          $q->where('sender_id', $patient->mobile_no ?? null)
            ->where('receiver_id', $doctor->mobile_no ?? null);
        });
      })
        ->where('message_type', 'image')
        ->whereNotNull('media_id')
        ->orderBy('created_at', 'asc')
        ->first();
      if (!$images) {
        return response()->json([
          'success' => false,
          'error' => true,
          'message' => 'No images found.',
          'images' => [],
        ], 404);
      }
      $afterImages = [];
      if ($images->after_image != null) {
        $afterImages = [
          "before_image" => url('skin_images/' . $images->media_id . '.png'),
          "after_image" => url('skin_images/after_' . $images->media_id . '.png'),
        ];
      } else {
        $chabotResponse = new SkinAnalysisController();
        $response = $chabotResponse->afterImageAnalysis(new Request(['mediaId' => $images->media_id]));
        $responseData = json_decode($response->getContent(), true);

        // Log::info(json_encode($responseData));
        if (isset($responseData['images'])) {
          $matchedResponses[] = $responseData['images'];
          $images->after_image = $responseData['images'];
          $images->save();
        }
        $afterImages = [
          "before_image" => url('skin_images/' . $images->media_id . '.png'),
          "after_image" => url('skin_images/after_' . $images->media_id . '.png'),
        ];
      }


      return response()->json([
        'success' => true,
        'error' => false,
        'message' => 'After images fetched successfully.',
        'images' => $afterImages,
      ], 200);
    } catch (\Throwable $th) {
      //throw $th;
      return response()->json([
        'success' => false,
        'error' => true,
        'message' => $th->getMessage(),
        'images' => [],
      ], 500);
    }
  }

  public function checkPatient($patientNo, $doctorNumber, Request $request)
  {
    if ($patientNo == null) {
      return response()->json([
        'success' => true,
        'error' => true,
        'message' => 'Patient number is required.',
        'patient' => [],
      ], 400);
    }
    if (substr($patientNo, 0, 2) === '91' && strlen($patientNo) > 10) {
      $patientNo = substr($patientNo, 2);
    }
    if (substr($doctorNumber, 0, 2) === '91' && strlen($doctorNumber) > 10) {
      $doctorNumber = substr($doctorNumber, 2);
    }

    $patient = Patientmaster::where('mobile_no', $patientNo)->first();
    if (!$patient) {
      // Create a new Request object with patient details from the incoming request
      $doctor = Doctor::where('mobile_no', $doctorNumber)->first();
      if (!$doctor) {
        return response()->json([
          'success' => true,
          'error' => true,
          'message' => 'Doctor not found.',
          'patient' => [],
        ], 404);
      }
      $doctor_id = $doctor->pharmaclient_id;
      $establishId = DB::table('docexa_medical_establishments_medical_user_map')->where('medical_user_id', $doctor_id)->first();
      if (!$establishId) {
        return response()->json([
          'success' => true,
          'error' => true,
          'message' => 'Establishment ID not found.',
          'patient' => [],
        ], 404);
      }
      $patientRequest = new Request([
        'patient_name' => $request->input('patient_name', " NO_NAME"),
        'mobile_no' => $patientNo,
        'mobile' => $patientNo,
        'email_id' => $request->input('email_id', null),
        'age' => $request->input('age', null),
        'dob' => $request->input('dob', null),
        'gender' => $request->input('gender', null),
        'address' => $request->input('address', null),
        'city' => $request->input('city', null),
        'state' => $request->input('state', null),
        'pincode' => $request->input('pincode', null),
        'occupation' => $request->input('occupation', null),
        'health_id' => $request->input('health_id', null),
        'flag' => $request->input('flag', null),
        'visit_type' => $request->input('visit_type', null),
      ]);

      $patient = new PatientApi();

      $patient = $patient->createPatientv2($establishId->id, $patientRequest);

      return response()->json([
        'success' => true,
        'error' => false,
        'message' => 'Patient create success.',
        'patient' => $patient,
      ], 201);
    } else {
      return response()->json([
        'success' => true,
        'error' => false,
        'message' => 'Patient found.',
        'patient' => $patient,
      ], 200);
    }
  }

  public function sendDocumentToWhatsApp(Request $request)
  {
    // dd($request->all());
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

    // Optionally log or store the outgoing document message
    // $this->storeChat([
    //   'sender_id' => $this->DOCTOR_NUMBER,
    //   'receiver_id' => $to,
    //   'message_type' => 'document',
    //   'message_text' => $caption,
    //   'analysis' => null,
    //   'output' => null,
    //   'media_url' => $link,
    //   'media_mime_type' => null,
    //   'media_sha256' => null,
    //   'media_id' => null,
    //   'whatsapp_message_id' => null,
    //   'date' => Carbon::now()->toDateTimeString()
    // ]);

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


  public function uploadPdf(Request $request)
  {

    try {
      $file = $request->file('file');
      $doctorId = $request->input('doctor_id');
      $patientNo = $request->input('patient_number');
      $patient = Patientmaster::where('mobile_no', $patientNo)->get();
      if ($patient == null) {
        return response()->json([
          'success' => false,
          'message' => 'Patient not found',

        ], 404);
      }
      $doctor = DB::table(env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map')->where('id', $doctorId)->join(env('DB_DATABASE') . '.docexa_doctor_master', 'docexa_doctor_master.pharmaclient_id', env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map.medical_user_id')->first();
      if ($doctor == null) {
        return response()->json([
          'success' => false,
          'message' => 'Doctor not found',
        ], 404);
      }

      if (!$file) {
        return response()->json([
          'success' => false,
          'message' => 'No PDF file uploaded',
          'error' => 'File not found'
        ], 400);
      }

      // Use original name or custom name
      $filename = $request->filename ?? 'document_' . time() . '.pdf';

      // Store in storage/app/public/pdf
      $path = $file->storeAs('pdf', $filename, 'public');
      $fullUrl = url(Storage::url($path));
      $request->merge(['to' => $patientNo, 'from' => $doctor->mobile_no, 'caption' => "Prescription Details.", 'link' => $fullUrl, 'filename' => 'Prescription ' . Carbon::now() . '']);
      $messageResponse = $this->sendDocumentToWhatsApp($request);
      return response()->json([
        'success' => true,
        'message' => 'PDF uploaded successfully',
        'link' => $fullUrl,
        'file_path' => Storage::url($path)
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to upload PDF',
        'error' => $e->getMessage()
      ], 500);
    }
  }


  // public function uploadImageFromDoc(Request $request)
  // {
  //   try {
  //     $doctorId = $request->input('doctor_id');
  //     $patientNumber = $request->input('patient_number');

  //     $doctor = DB::table(env('DB_DATABASE').'.docexa_medical_establishments_medical_user_map')
  //       ->where(env('DB_DATABASE').'.docexa_medical_establishments_medical_user_map.id', $doctorId)
  //       ->join(env('DB_DATABASE').'.docexa_doctor_master', 'docexa_doctor_master.pharmaclient_id', '=', env('DB_DATABASE').'.docexa_medical_establishments_medical_user_map.medical_user_id')
  //       ->select('docexa_doctor_master.mobile_no')
  //       ->first();

  //     if ($doctor == null) {
  //       return response()->json([
  //         'success' => false,
  //         'message' => 'Doctor not found',
  //       ], 404);
  //     }

  //     if (!$request->hasFile('images')) {
  //       return response()->json([
  //         'status' => false,
  //         'message' => 'No images found in the request.'
  //       ], 400);
  //     }

  //     $uploadedFiles = [];

  //     $files = is_array($request->file('images'))
  //       ? $request->file('images')
  //       : [$request->file('images')];

  //     foreach ($files as $file) {
  //       if ($file && $file->isValid()) {
  //         $path = $file->store('public/patient_images');
  //         $filename = basename($path);

  //         // ✅ Save only the relative path or filename in DB
  //         $relativePath = Storage::url($path); // e.g., /storage/patient_images/filename.jpg

  //         // ✅ Append full URL for API response only
  //         //$ip = 'https://aestheticai.globalspace.in/aesthetic_backend/public/'; // Change to your server IP or domain
  //         $publicUrl = url($relativePath);

  //         // ✅ Add to response array
  //         $uploadedFiles[] = [
  //           'file_name' => $filename,
  //           'url' => $publicUrl,
  //         ];

  //         // ✅ Save only relative data to DB
  //         $chat = new Chats();
  //         $chat->sender_id = $doctor->mobile_no;
  //         $chat->receiver_id = $request->input('patient_number');
  //         $chat->message_type = 'image';
  //         $chat->media_url = $relativePath; // Save only path
  //         $chat->media_mime_type = null;
  //         $chat->media_sha256 = null;
  //         $chat->media_id = $filename;
  //         $chat->whatsapp_message_id = null;
  //         $chat->is_visible = 0;
  //         $chat->date = Carbon::now()->toDateTimeString();
  //         $chat->save();
  //       }
  //     }

  //     // Get images from chat
  //     $images = Chats::where(function ($query) use ($patientNumber, $doctor) {
  //       $query->where('sender_id', $doctor->mobile_no)
  //         ->where('message_type', 'image')

  //         ->where('receiver_id', $patientNumber);
  //     })
  //       ->orWhere(function ($query) use ($patientNumber, $doctor) {
  //         $query->where('sender_id', $patientNumber)
  //           ->where('message_type', 'image')

  //           ->where('receiver_id', $doctor->mobile_no);
  //       })
  //       ->orderBy('date', 'desc')
  //       ->get();


  //     // Transform the images to match the Dart model
  //     $imageList = $images->map(function ($chat) {
  //       return [
  //         'file_name' => $chat->media_id,
  //         'url' => url($chat->media_url), // Generate full URL
  //         'uploaded_date' => Carbon::parse($chat->date)->format('Y-m-d H:i:s'),
  //         // 'file_size' => $this->getFileSize($chat->media_url) // Add helper method to get file size
  //       ];
  //     });

  //     // Return response matching the Dart model structure
  //     return response()->json([
  //       'status' => true,
  //       'message' => 'Image uploaded successfully',
  //       'data' => [
  //         'uploaded_files' => $imageList
  //       ]
  //     ]);
  //   } catch (\Exception $e) {
  //     \Log::error('Error in getUploadedImages: ' . $e->getMessage());
  //     return response()->json([
  //       'status' => false,
  //       'message' => 'Server Error: ' . $e->getMessage(),
  //       'data' => null
  //     ], 500);
  //   }
  // }


  public function uploadImageFromDoc(Request $request)
  {
    try {
      $doctorId = $request->input('doctor_id');
      $patientNumber = $request->input('patient_number');

      $doctor = DB::table(env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map')
        ->where(env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map.id', $doctorId)
        ->join(env('DB_DATABASE') . '.docexa_doctor_master', 'docexa_doctor_master.pharmaclient_id', '=', env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map.medical_user_id')
        ->select('docexa_doctor_master.mobile_no')
        ->first();

      if ($doctor == null) {
        return response()->json([
          'success' => false,
          'message' => 'Doctor not found',
        ], 404);
      }

      if (!$request->hasFile('images')) {
        return response()->json([
          'status' => false,
          'message' => 'No images found in the request.'
        ], 400);
      }
      // $manager = new ImageManager(['driver' => 'gd']); // or 'imagick'

      $uploadedFiles = [];

      $files = is_array($request->file('images'))
        ? $request->file('images')
        : [$request->file('images')];

      foreach ($files as $file) {
        if ($file && $file->isValid()) {
          $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.png';
          $fullPath = base_path('skin_images/' . $filename);
          // Ensure destination directory exists
          if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
          }

          // Create image resource based on MIME type
          $mimeType = $file->getMimeType();
          $image = null;

          switch ($mimeType) {
            case 'image/jpeg':
              $image = @imagecreatefromjpeg($file->getRealPath());
              break;
            case 'image/png':
              $image = @imagecreatefrompng($file->getRealPath());
              break;
            case 'image/gif':
              $image = @imagecreatefromgif($file->getRealPath());
              break;
            default:
              Log::error("Unsupported image type: $mimeType");
              continue 2; // skip this file
          }

          if (!$image) {
            Log::error("Failed to create image resource for: " . $file->getClientOriginalName());
            continue;
          }

          // Save image as PNG
          $saved = imagepng($image, $fullPath);
          imagedestroy($image); // free memory

          if ($saved) {
            Log::info("Image saved as PNG: $filename");
          } else {
            Log::error("Failed to save image: $fullPath");
          }

          $command = "/usr/bin/python3 /var/www/html/aesthetic_backend/image_analysis.py " . escapeshellarg($fullPath) . " 2>&1";
          Log::info("Command executed: $command");

          $output = shell_exec($command);
          // Step 2: Clean the output
          // Remove the 'Loaded as API' line and any unwanted text
          $output = preg_replace('/^Loaded as API: .*/', '', $output); // Remove the first line
          $output = trim($output); // Trim any extra spaces/newlines at the beginning/end

          // Step 3: Decode the JSON output from the Python script
          $result = json_decode($output, true);

          if ($result['success'] === true) {
            // Step 4: Extract and store the result in a clean format
            $message = $result['message'];
            Log::info(json_encode($message));


            $skinType = $message[0];
            $mainCondition = $message[1];
            $otherIssues = str_replace('\n', "\n", $message[2]);

            $formatMessage = "You are an expert dermatologist AI assistant.\n\n";
            $formatMessage .= "Patient Details:\n";
            $formatMessage .= "• Skin Type: {$skinType}\n";
            $formatMessage .= "• Primary Skin Condition: {$mainCondition}\n";
            $formatMessage .= "• Possible Other Conditions: {$otherIssues}\n\n";

            $formatMessage .= "Based on this information, generate a short diagnosis-based treatment plan for a dermatologist to review.\n";
            $formatMessage .= "Include both medicinal and aesthetic procedure suggestions (e.g., Botox, fillers, chemical peels, laser treatments, etc) where clinically appropriate.\n";
            $formatMessage .= "Include the following:\n";
            $formatMessage .= "- Confirmed Diagnosis\n";
            $formatMessage .= "- List of recommended medicines (with dosage form and usage if needed)\n";
            $formatMessage .= "- Treatment notes (application instructions, any test advice, aesthetic treatment suggestions, or skin-type considerations)\n\n";
            $formatMessage .= "Return the response in under 1000 words in this format:\n\n";
            $skinAnalysis = new SkinAnalysisController();
            $chatbotResponse = $skinAnalysis->chatbot(new Request(['question' => $formatMessage]))->getData(true);
            Log::info("Chatbot Response: " . json_encode($chatbotResponse));

            // ✅ Save only relative data to DB
            $chat = new Chats();
            $chat->sender_id = $doctor->mobile_no;
            $chat->receiver_id = $request->input('patient_number');
            $chat->message_type = 'image';
            $chat->media_url = $fullPath;
            $chat->media_mime_type = null;
            $chat->media_sha256 = null;
            $chat->output = $chatbotResponse['chatbot_response'] ?? 'No response';
            $chat->analysis  = json_encode($message);
            // Remove .jpg, .jpeg, .png, .gif, .bmp, .webp extensions from filename for media_id
            $chat->media_id = preg_replace('/\.(jp[e]?g|png|gif|bmp|webp)$/i', '', $filename);
            $chat->whatsapp_message_id = null;
            $chat->is_visible = 0;
            $chat->date = Carbon::now()->toDateTimeString();
            $chat->save();
          }

          Log::info("Uploaded and converted to PNG: " . $filename);
        }
      }

      // Get images from chat
      $images = Chats::where(function ($query) use ($patientNumber, $doctor) {
        $query->where('sender_id', $doctor->mobile_no)
          ->where('message_type', 'image')

          ->where('receiver_id', $patientNumber);
      })
        ->orWhere(function ($query) use ($patientNumber, $doctor) {
          $query->where('sender_id', $patientNumber)
            ->where('message_type', 'image')

            ->where('receiver_id', $doctor->mobile_no);
        })
        ->orderBy('date', 'desc')
        ->get();


      // Transform the images to match the Dart model
      $imageList = $images->map(function ($chat) {
        return [
          'file_name' => $chat->media_id,
          'url' => url('skin_images/' . $chat->media_id . '.png'),
          'uploaded_date' => Carbon::parse($chat->date)->format('Y-m-d H:i:s'),
          // 'file_size' => $this->getFileSize($chat->media_url) // Add helper method to get file size
        ];
      });

      // Return response matching the Dart model structure
      return response()->json([
        'status' => true,
        'message' => 'Image uploaded successfully',
        'data' => [
          'uploaded_files' => $imageList
        ]
      ]);
    } catch (\Exception $e) {
      \Log::error('Error in getUploadedImages: ' . $e->getMessage());
      return response()->json([
        'status' => false,
        'message' => 'Server Error: ' . $e->getMessage(),
        'data' => null
      ], 500);
    }
  }


  // public function uploadMarkedImageFromDoc(Request $request)
  // {
  //   try {
  //     $doctorId = $request->input('doctor_id');
  //     $patientNumber = $request->input(key: 'patient_number');
  //     $isMarked = $request->input(key: 'ismarked');


  //     $doctor = DB::table(env('DB_DATABASE').'.docexa_medical_establishments_medical_user_map')
  //       ->where(env('DB_DATABASE').'.docexa_medical_establishments_medical_user_map.id', $doctorId)
  //       ->join(env('DB_DATABASE').'.docexa_doctor_master', 'docexa_doctor_master.pharmaclient_id', '=', env('DB_DATABASE').'.docexa_medical_establishments_medical_user_map.medical_user_id')
  //       ->select('docexa_doctor_master.mobile_no')
  //       ->first();

  //     if ($doctor == null) {
  //       return response()->json([
  //         'success' => false,
  //         'message' => 'Doctor not found',
  //       ], 404);
  //     }

  //     if (!$request->hasFile('images')) {
  //       return response()->json([
  //         'status' => false,
  //         'message' => 'No images found in the request.'
  //       ], 400);
  //     }

  //     $uploadedFiles = [];

  //     $files = is_array($request->file('images'))
  //       ? $request->file('images')
  //       : [$request->file('images')];

  //     foreach ($files as $file) {
  //       if ($file && $file->isValid()) {
  //         $path = $file->store('public/patient_images');
  //         $filename = basename($path);

  //         // ✅ Save only the relative path or filename in DB
  //         $relativePath = Storage::url($path); // e.g., /storage/patient_images/filename.jpg

  //         // ✅ Append full URL for API response only
  //         //$ip = 'https://aestheticai.globalspace.in/aesthetic_backend/public/'; // Change to your server IP or domain
  //         $publicUrl = url($relativePath);

  //         // ✅ Add to response array
  //         $uploadedFiles[] = [
  //           'file_name' => $filename,
  //           'url' => $publicUrl,
  //         ];

  //         // ✅ Save only relative data to DB
  //         $chat = new Chats();
  //         $chat->sender_id = $doctor->mobile_no;
  //         $chat->receiver_id = $request->input('patient_number');
  //         $chat->message_type = 'image';
  //         $chat->media_url = $relativePath; // Save only path
  //         $chat->media_mime_type = null;
  //         $chat->media_sha256 = null;
  //         $chat->media_id = $filename;
  //         $chat->whatsapp_message_id = null;
  //         $chat->is_visible = 0;
  //         $chat->is_marked = $isMarked;
  //         $chat->date = Carbon::now()->toDateTimeString();
  //         $chat->save();
  //       }
  //     }

  //     // Get images from chat
  //     $images = Chats::where(function ($query) use ($patientNumber, $doctor) {
  //       $query->where('sender_id', $doctor->mobile_no)
  //         ->where('message_type', 'image')

  //         ->where('receiver_id', $patientNumber);
  //     })
  //       ->orWhere(function ($query) use ($patientNumber, $doctor) {
  //         $query->where('sender_id', $patientNumber)
  //           ->where('message_type', 'image')

  //           ->where('receiver_id', $doctor->mobile_no);
  //       })
  //       ->orderBy('date', 'desc')
  //       ->get();


  //     // Transform the images to match the Dart model
  //     $imageList = $images->map(function ($chat) {
  //       return [
  //         'file_name' => $chat->media_id,
  //         'url' => url($chat->media_url), // Generate full URL
  //         'uploaded_date' => Carbon::parse($chat->date)->format('Y-m-d H:i:s'),
  //         // 'file_size' => $this->getFileSize($chat->media_url) // Add helper method to get file size
  //       ];
  //     });

  //     // Return response matching the Dart model structure
  //     return response()->json([
  //       'status' => true,
  //       'message' => 'Image uploaded successfully',
  //       'data' => [
  //         'uploaded_files' => $imageList
  //       ]
  //     ]);
  //   } catch (\Exception $e) {
  //     \Log::error('Error in getUploadedImages: ' . $e->getMessage());
  //     return response()->json([
  //       'status' => false,
  //       'message' => 'Server Error: ' . $e->getMessage(),
  //       'data' => null
  //     ], 500);
  //   }
  // }

  public function uploadMarkedImageFromDoc(Request $request)
  {
    try {
      $doctorId = $request->input('doctor_id');
      $patientNumber = $request->input('patient_number');
      $isMarked = $request->input('ismarked');

      $doctor = DB::table(env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map')
        ->where(env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map.id', $doctorId)
        ->join(env('DB_DATABASE') . '.docexa_doctor_master', 'docexa_doctor_master.pharmaclient_id', '=', env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map.medical_user_id')
        ->select('docexa_doctor_master.mobile_no')
        ->first();

      if (!$doctor) {
        return response()->json([
          'success' => false,
          'message' => 'Doctor not found',
        ], 404);
      }

      if (!$request->hasFile('images')) {
        return response()->json([
          'status' => false,
          'message' => 'No images found in the request.'
        ], 400);
      }

      $uploadedFiles = [];
      $files = is_array($request->file('images')) ? $request->file('images') : [$request->file('images')];

      foreach ($files as $file) {
        if ($file && $file->isValid()) {
          $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.png';
          $fullPath = base_path('skin_images/' . $filename);

          if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
          }

          $mimeType = $file->getMimeType();
          $image = null;
          switch ($mimeType) {
            case 'image/jpeg':
              $image = @imagecreatefromjpeg($file->getRealPath());
              break;
            case 'image/png':
              $image = @imagecreatefrompng($file->getRealPath());
              break;
            case 'image/gif':
              $image = @imagecreatefromgif($file->getRealPath());
              break;
            default:
              Log::error("Unsupported image type: $mimeType");
              continue 2;
          }

          if (!$image) {
            Log::error("Failed to create image resource for: " . $file->getClientOriginalName());
            continue;
          }

          $saved = imagepng($image, $fullPath);
          imagedestroy($image);

          if (!$saved) {
            Log::error("Failed to save image: $fullPath");
            continue;
          }

        
          // ✅ Save chat entry with all info
          $chat = new Chats();
          $chat->sender_id = $doctor->mobile_no;
          $chat->receiver_id = $patientNumber;
          $chat->message_type = 'image';
          $chat->media_url = $fullPath;
          $chat->media_mime_type = null;
          $chat->media_sha256 = null;
          $chat->media_id = preg_replace('/\.(jp[e]?g|png|gif|bmp|webp)$/i', '', $filename);
          $chat->whatsapp_message_id = null;
          $chat->is_visible = 0;
          $chat->is_marked = $isMarked;
          $chat->output = null;
          $chat->analysis =null;
          $chat->date = Carbon::now()->toDateTimeString();
          $chat->save();

          $uploadedFiles[] = [
            'file_name' => $chat->media_id,
            'url' => url('skin_images/' . $chat->media_id . '.png'),
          ];
        }
      }

      // Return uploaded + fetched images
      $images = Chats::where(function ($query) use ($patientNumber, $doctor) {
        $query->where('sender_id', $doctor->mobile_no)->where('message_type', 'image')->where('receiver_id', $patientNumber);
      })->orWhere(function ($query) use ($patientNumber, $doctor) {
        $query->where('sender_id', $patientNumber)->where('message_type', 'image')->where('receiver_id', $doctor->mobile_no);
      })->orderBy('date', 'desc')->get();

      $imageList = $images->map(function ($chat) {
        return [
          'file_name' => $chat->media_id,
          'url' => url('skin_images/' . $chat->media_id . '.png'),
          'uploaded_date' => Carbon::parse($chat->date)->format('Y-m-d H:i:s'),
        ];
      });

      return response()->json([
        'status' => true,
        'message' => 'Image uploaded and analyzed successfully',
        'data' => [
          'uploaded_files' => $imageList
        ]
      ]);
    } catch (\Exception $e) {
      \Log::error('Error in uploadMarkedImageFromDoc: ' . $e->getMessage());
      return response()->json([
        'status' => false,
        'message' => 'Server Error: ' . $e->getMessage(),
        'data' => null
      ], 500);
    }
  }



  public function getUploadedImages($doctorId, $patientNumber)
  {
    try {
      // Validate request
      if (!$doctorId || !$patientNumber) {
        return response()->json([
          'status' => false,
          'message' => 'Doctor ID and Patient Number are required.',
          'data' => null
        ], 400);
      }

      $doctor = DB::table(env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map')
        ->where(env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map.id', $doctorId)
        ->join(env('DB_DATABASE') . '.docexa_doctor_master', 'docexa_doctor_master.pharmaclient_id', '=', env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map.medical_user_id')
        ->select('docexa_doctor_master.mobile_no')
        ->first();

      if (!$doctor) {
        return response()->json([
          'status' => false,
          'message' => 'Doctor not found.',
          'data' => null
        ], 404);
      }

      // Get images from chat
      $images = Chats::where(function ($query) use ($patientNumber, $doctor) {
        $query->where('sender_id', $doctor->mobile_no)
          ->where('message_type', 'image')

          ->where('receiver_id', $patientNumber);
      })
        ->orWhere(function ($query) use ($patientNumber, $doctor) {
          $query->where('sender_id', $patientNumber)
            ->where('message_type', 'image')

            ->where('receiver_id', $doctor->mobile_no);
        })
        ->orderBy('date', 'desc')
        ->get();

      // Transform the images to match the Dart model
      $imageList = $images->map(function ($chat) {
        return [
          'file_name' => $chat->media_id,
          // 'url' => url($chat->media_url), // Generate full URL
          'url' => url('skin_images/' . $chat->media_id . '.png'), // Use skin_images path
          'uploaded_date' => Carbon::parse($chat->date)->format('Y-m-d H:i:s'),
          // 'file_size' => $this->getFileSize($chat->media_url) // Add helper method to get file size
        ];
      });

      // Return response matching the Dart model structure
      return response()->json([
        'status' => true,
        'message' => 'Image list fetched successfully',
        'data' => [
          'uploaded_files' => $imageList
        ]
      ]);
    } catch (\Exception $e) {
      \Log::error('Error in getUploadedImages: ' . $e->getMessage());
      return response()->json([
        'status' => false,
        'message' => 'Server Error: ' . $e->getMessage(),
        'data' => null
      ], 500);
    }
  }

  /**
   * Helper method to get file size
   */



  public function deletePatientImage(Request $request)
  {
    try {
      $doctorId = $request->input('doctor_id');
      $patientNumber = $request->input('patient_number');
      $filename = $request->input('file_name');
      Log::info("Doctor ID: $doctorId, Patient Number: $patientNumber, File Name: $filename");

      // Validate input
      if (!$doctorId || !$patientNumber || !$filename) {
        return response()->json([
          'status' => false,
          'message' => 'Doctor ID, Patient Number, and File Name are required.',
          'data' => null
        ], 400);
      }

      // Fetch doctor mobile number
      $doctor = DB::table(env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map')
        ->where(env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map.id', $doctorId)
        ->join(env('DB_DATABASE') . '.docexa_doctor_master', 'docexa_doctor_master.pharmaclient_id', '=', env('DB_DATABASE') . '.docexa_medical_establishments_medical_user_map.medical_user_id')
        ->select('docexa_doctor_master.mobile_no')
        ->first();

      if (!$doctor) {
        return response()->json([
          'status' => false,
          'message' => 'Doctor not found.',
          'data' => null
        ], 404);
      }

      // Find the image by media_id
      $image = Chats::where('media_id', $filename)
        ->where('message_type', 'image')
        ->where(function ($query) use ($patientNumber, $doctor) {
          $query->where('sender_id', $doctor->mobile_no)
            ->where('receiver_id', $patientNumber);
        })
        ->orWhere(function ($query) use ($patientNumber, $doctor, $filename) {
          $query->where('media_id', $filename)
            ->where('message_type', 'image')
            ->where('sender_id', $patientNumber)
            ->where('receiver_id', $doctor->mobile_no);
        })
        ->first();

      if (!$image) {
        return response()->json([
          'status' => false,
          'message' => 'Image not found.',
          'data' => null
        ], 404);
      }

      // Delete image file
      // $filePath = public_path($image->media_url);
      $filePath = $image->media_url;
      if (file_exists($filePath)) {
        unlink($filePath);
      }

      // Delete record
      $image->delete();

      // Fetch remaining images
      $remainingImages = Chats::where('message_type', 'image')
        ->where(function ($query) use ($patientNumber, $doctor) {
          $query->where('sender_id', $doctor->mobile_no)
            ->where('receiver_id', $patientNumber);
        })
        ->orWhere(function ($query) use ($patientNumber, $doctor) {
          $query->where('sender_id', $patientNumber)
            ->where('receiver_id', $doctor->mobile_no);
        })
        ->orderBy('date', 'desc')
        ->get();

      // Transform to Dart model format
      $imageList = $remainingImages->map(function ($chat) {
        return [
          'file_name' => $chat->media_id,
          'url' => base_path('skin_images/' . $chat->media_id . '.png'), // Use skin_images path
          'uploaded_date' => \Carbon\Carbon::parse($chat->date)->format('Y-m-d H:i:s'),
        ];
      });

      return response()->json([
        'status' => true,
        'message' => 'Image list fetched successfully',
        'data' => [
          'uploaded_files' => $imageList
        ]
      ]);
    } catch (\Exception $e) {
      \Log::error('Error in deletePatientImage: ' . $e->getMessage());
      return response()->json([
        'status' => false,
        'message' => 'Server Error: ' . $e->getMessage(),
        'data' => null
      ], 500);
    }
  }


  public function register(Request $request)
  {
    return response()->json([
      'status' => true,
      'message' => 'Your verification is under process',
      'data' => null
    ], 200);
  }





  /**
   * Helper method to get file size
   */
}
