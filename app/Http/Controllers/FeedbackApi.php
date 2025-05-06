<?php
namespace App\Http\Controllers;

use App\Models\FeedbackIdentifier;
use App\Models\Feedback;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FeedbackApi extends Controller
{
    public function get(Request $request)
    {
        // Retrieve the 'identifier' from the request header
        $identifierHeader = $request->query('identifier');
        if ($identifierHeader) {
            // Check if the identifier exists in the FeedbackIdentifier table
            $feedbackIdentifier = FeedbackIdentifier::where('identifier', $identifierHeader)->first();

            if (!$feedbackIdentifier) {
                // If the identifier doesn't exist, return an error with a list of valid identifiers
                return response()->json([
                    'status' => false,
                    'code' => 400,
                    'message' => 'Invalid identifier: ' . $identifierHeader,
                    'identifiers' => FeedbackIdentifier::pluck('identifier')->toArray()
                ], 400);
            }

            // If it exists, fetch all Feedback records matching this identifier's ID
            $feedback = Feedback::join('feedback_identifier', 'feedback.identifier', '=', 'feedback_identifier.id')
                ->where('feedback_identifier.identifier', $identifierHeader)
                ->select(
                    'feedback.id',
                    'feedback.feedback',
                    'feedback_identifier.identifier as identifier_value',
                    'feedback_identifier.logo',
                    'feedback.updated_at'
                )
                ->get();


            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'List of feedback for identifier: ' . $identifierHeader,
                'data' => $feedback
            ], 200);
        } else {
            // No identifier header found, return all feedback records
            $allFeedback = Feedback::all();

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'List of all feedback',
                'data' => $allFeedback
            ], 200);
        }
    }


    public function post(Request $request)
    {
        try {
            $this->validate($request, [
                'id' => 'required|numeric',
                'identifier' => 'required|string',
                'feedback' => 'required|string'
            ]);

            $identifier = FeedbackIdentifier::where('identifier', $request->input('identifier'))->first();

            if (!isset($identifier)) {
                return response()->json([
                    'status' => false,
                    'code' => 400,
                    'message' => 'unmatched identifier',
                    'identifer' => FeedbackIdentifier::pluck('identifier')
                ], 400);
            }

            $feedback = new Feedback();
            $feedback->user_id = $request->input('id');
            $feedback->identifier = $identifier['id'];
            $feedback->feedback = $request->input('feedback');
            $save = $feedback->save();

            if ($save) {
                return response()->json([
                    'status' => false,
                    'code' => 200,
                    'message' => 'Feedback submitted successfully',
                ], 200);
            }

            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'Failed to save feedback',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'code' => 500,
                'error' => 'Failed to save feedback',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        return response()->json(['message' => "Feedback details for ID $id"], 200);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => "Feedback ID $id updated"], 200);
    }

    public function destroy($id)
    {
        return response()->json(['message' => "Feedback ID $id deleted"], 200);
    }
}
