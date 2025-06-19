<?php

namespace App\Http\Controllers;

use App\Jobs\AfterImageStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SkinAnalysisController extends Controller
{
    // Gradio API Clients
    private $skinAnalysisClient;
    private $skinBotClient;

    public function __construct()
    {
        // Initialize Gradio Client for Skin Analysis and Chatbot
        $this->skinAnalysisClient = "https://gradio.api/harshadsalunkhe1212/SkinAnalysis/predict";
        $this->skinBotClient = 'https://gradio.app/Gajendra5490/SkinChatBot/predict';
    }

    public function analyzeSkin(Request $request)
    {

        $mediaId = $request->input('mediaId');
        if (empty($mediaId)) {
            return response()->json([
                'error' => true,
                'status' => 400,
                'message' => 'Media ID is required.',
                'result' => 'No media ID provided'
            ], 400);
        }

        $url = "https://api.dovesoft.io/REST/directApi/downloadAttachmentFile";

        // Optional: replace with actual authentication headers
        $headers = [
            "Key" => 'a2608dfcbeXX',
            "wabaNumber" => '919321962947',
        ];

        // Step 1: Send the API request
        $response = Http::withHeaders($headers)
            ->get($url, ['mediaId' => $mediaId]);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'API request failed',
                'status' => 500,
                'message' => 'Failed to download the image from the API.',
                'result' => "Image not found or invalid media.Please try again."
            ], 500);
        }
        $data = json_decode($response->body(), true);

        $binary = pack('c*', ...$data['file']); // 'c*' means pack all signed chars

        file_put_contents(base_path('skin_images/' . $mediaId . '.png'), $binary);
        $imagePath = base_path('skin_images/' . $mediaId . '.png');

        $command = "/usr/bin/python3 /var/www/html/aesthetic_backend/image_analysis.py " . escapeshellarg($imagePath) . " 2>&1";
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
          
            $chatbotResponse = $this->chatbot(new Request(['question' => $formatMessage]))->getData(true);


            dispatch(new AfterImageStore(['mediaId' => $mediaId]));

            // Log the chatbot response
            Log::info('Chatbot response: ' . json_encode($chatbotResponse));

            // Step 5: Return the formatted message in response
            return response()->json([
                'error' => false,
                'status' => 200,
                'message' => 'Skin analysis completed successfully.',
                'media_url' => $imagePath,
                'analysis' => $message,
                'result' => $chatbotResponse['chatbot_response'] ?? 'No response'
            ]);
        } else {
            return response()->json([
                'error' => true,
                'status' => 500,
                'error' => 'Skin analysis failed.',
                'result' => 'No message returned from the analysis. Can you please try again?'

            ], 500);
        };
    }

    public function chatbot(Request $request)
    {
        if (!$request->has('question')) {
            return response()->json(['error' => 'No question provided'], 400);
        }

        $question = $request->input('question');

        // Static greeting message
        $greeting = "Hello!👋 Welcome to Aesthetic AI – your personal skincare assistant. I'm here to help you with all your skin-related concerns. Let's get started!";

        // Append instruction to ensure skin-related answers only
        $instruction = "Note: Only respond to skin-related questions. If this question is not related to skin, reply: 'I can only answer skin-related questions. Please ask accordingly.'";

        // Combine everything
        $finalPrompt = "$greeting\n\nUser: $question\n\n$instruction";


        // $question = $request->input('question');
        // $finalPrompt = $question . "\n\nNote: Only respond to skin-related questions. If this question is not related to skin, reply: 'I can only answer skin-related questions. Please ask accordingly.'";
        $escapedQuestion = escapeshellarg($question); // Escape to prevent shell injection
        try {
            // $pythonPath = 'python3'; // Adjust if your system uses another path

            // $output = shell_exec("$pythonPath $scriptPath $question");
            // $output = shell_exec("/usr/bin/python3 /var/www/html/aesthetic_backend/chatbot.py $escapedQuestion 2>&1");
            // Log::info("Chatbot output: $output");
            $groqApiUrl = 'https://api.groq.com/openai/v1/chat/completions';
            $bearerToken = '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'; // Replace with your actual token

            $groqBody = [
                "model" => "llama-3.3-70b-versatile",
                "messages" => [
                    [
                        "role" => "user",
                        "content" => $finalPrompt
                    ]
                ],
                "temperature" => 0.7,
                "max_tokens" => 1000
            ];

            $groqResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearerToken,
                'Content-Type' => 'application/json'
            ])->post($groqApiUrl, $groqBody);

            if ($groqResponse->successful()) {
                $groqData = $groqResponse->json();
                $chatbotResponse = [
                    'chatbot_response' => $groqData['choices'][0]['message']['content'] ?? 'No response'
                ];
            } else {
                $chatbotResponse = [
                    'chatbot_response' => 'No response from  API'
                ];
            }

            // if (!$output) {
            //     return response()->json([
            //         'error' => 'No response from chatbot',
            //         'status' => 500,
            //         'message' => 'No response from chatbot',
            //         'chatbot_response' => 'No response.Please try again.',
            //     ], 500);
            // }

            // Format the chatbot response for better readability
            // $formattedResponse = preg_replace('/\\\n/', "\n", $chatbotResponse['chatbot_response']);
            // $formattedResponse = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formattedResponse); // Bold formatting
            // $formattedResponse = preg_replace('/\n{2,}/', "\n\n", $formattedResponse); // Normalize multiple newlines
            $formattedResponse = preg_replace('/\*\*(.*?)\*\*/', strtoupper('$1'), $chatbotResponse['chatbot_response']);
            // $lines = explode("\n", trim($formattedResponse));
            // $lastLine = end($lines);

            // $responseData = json_decode($lastLine, true);

            if ($formattedResponse === null) {
                return response()->json([
                    'error' => 'Failed to decode JSON',
                    'status' => 500,
                    'message' => 'Failed to decode JSON response from chatbot',
                    'chatbot_response' => 'No response',
                    'raw_output' => $formattedResponse
                ], 500);
            }
            return response()->json([
                'error' => false,
                'message' => 'Chatbot response received successfully',
                'status' => 200,
                'question' => $request->input('question'),
                'chatbot_response' => $formattedResponse ?? 'No response'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'status' => 500,
                'chatbot_response' => 'Failed to get response from chatbot',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function greetingChatbot(Request $request)
    {
        if (!$request->has('question')) {
            return response()->json(['error' => 'No question provided'], 400);
        }

        $question = $request->input('question');
        $escapedQuestion = escapeshellarg($question); // Escape to prevent shell injection
        try {
            $pythonPath = 'python3'; // Adjust if your system uses another path

            // $output = shell_exec("$pythonPath $scriptPath $question");
            $output = shell_exec("/usr/bin/python3 /var/www/html/aesthetic_backend/greeting_chatbot.py $escapedQuestion 2>&1");
            Log::info("Chatbot output: $output");

            if (!$output) {
                return response()->json([
                    'error' => 'No response from Greeeting chatbot',
                    'status' => 500,
                    'message' => 'No response from Greeeting chatbot',
                    'chatbot_response' => 'No response.Please try again.',
                ], 500);
            }

            $lines = explode("\n", trim($output));
            $lastLine = end($lines);

            $responseData = json_decode($lastLine, true);

            if ($responseData === null) {
                return response()->json([
                    'error' => 'Failed to decode JSON',
                    'status' => 500,
                    'message' => 'Failed to decode JSON response from Greeeting chatbot',
                    'chatbot_response' => 'No response',
                    'raw_output' => $output
                ], 500);
            }
            return response()->json([
                'error' => false,
                'message' => 'Greeeting Chatbot response received successfully',
                'status' => 200,
                'question' => $request->input('question'),
                'chatbot_response' => $responseData['response'] ?? 'No response'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'status' => 500,
                'chatbot_response' => 'Failed to get response from Greeeting chatbot',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function afterImageAnalysis(Request $request)
    {
        if (!$request->has('mediaId')) {
            return response()->json(['error' => 'No question provided'], 400);
        }

        $mediaId = $request->input('mediaId');
        $escapedQuestion = escapeshellarg($mediaId); // Escape to prevent shell injection
        try {
            $pythonPath = 'python3'; // Adjust if your system uses another path

            // $output = shell_exec("$pythonPath $scriptPath $question");
            $output = shell_exec("python3 /var/www/html/aesthetic_backend/afterImage.py $escapedQuestion 2>&1");
            // $output = shell_exec("python3 ~/var/docexa/afterImage.py $escapedQuestion 2>&1");
            Log::info("image analysis output: $output");

            if (!$output) {
                return response()->json([
                    'error' => 'No response from Greeeting chatbot',
                    'status' => 500,
                    'message' => 'No response from Greeeting chatbot',
                    'chatbot_response' => 'No response.Please try again.',
                ], 500);
            }

            $lines = explode("\n", trim($output));
            $lastLine = end($lines);
            Log::info("last line: $lastLine");
            $responseData = json_decode($lastLine, true);

            if ($responseData === null) {
                return response()->json([
                    'error' => 'Failed to decode JSON',
                    'status' => 500,
                    'message' => 'Failed to decode JSON response from Greeeting chatbot',
                    'chatbot_response' => 'No response',
                    'images' => $output
                ], 500);
            }
            return response()->json([
                'error' => false,
                'message' => 'Image response received successfully',
                'status' => 200,
                'mediaId' => $request->input('mediaId'),
                'images' => $responseData['response'] ?? 'No response'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'status' => 500,
                'chatbot_response' => 'Failed to get response from Greeeting chatbot',
                'images' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    // // Endpoint for Chatbot Interaction
    // public function chatbot(Request $request)
    // {
    //     if (!$request->has('question')) {
    //         return response()->json(['error' => 'No question provided'], 400);
    //     }

    //     $question = $request->input('question');

    //     // Send the question to the Skin Chatbot API
    //     // $response = Http::post($this->skinBotClient, [
    //     //     'question' => $question
    //     // ]);

    //     // $response = Http::timeout(10)->post('https://gradio.app/Gajendra5490/SkinChatBot/predict', [
    //     //     'data' => [$question]  // Gradio expects a 'data' array
    //     // ]);
    //     // dd($response->json());
    //     $question = escapeshellarg($request->input('question'));
    //     $output = shell_exec("python3 /home/andy/codes/aesthetic_clinic/chatbot.py");
    //     dd($output);
    //     // return response()->json([
    //     //     'chatbot_response' => $response->json()
    //     // ]);
    // }
}
