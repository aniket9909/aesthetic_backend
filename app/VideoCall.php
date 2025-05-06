<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OpenTok\OpenTok;
use OpenTok\MediaMode;
use OpenTok\Role;
use Radis;
use DB;
use Log;
class VideoCall extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function createsession($bookingID, $type)
    {
        $openTokAPI = new OpenTok(env('OPENTOK_API_KEY'), env('OPENTOK_API_SECRET'));
        $handle = DB::table('docexa_patient_booking_details')
        ->join('docexa_medical_establishments_medical_user_map','docexa_medical_establishments_medical_user_map.id','docexa_patient_booking_details.user_map_id')
        ->where('docexa_patient_booking_details.bookingidmd5',$bookingID)->first()->handle;
        if (app('redis')->exists($bookingID)) {
            $sessionID = app('redis')->get($bookingID);
        } else {
            $session = $openTokAPI->createSession();
            $sessionID = $session->getSessionId();
        }
        if ($type == 'doctor') {
            $data = [
                'apiKey' => env('OPENTOK_API_KEY'),
                'sessionId' => $sessionID,
                'token' => $openTokAPI->generateToken($sessionID, array( 
                    'role'       => Role::MODERATOR,
                    'expireTime' => time() + (2 * 60 * 60), // in two hours
                    'data'       => json_encode(["type"=>"doctor","envornment"=>"dev","handle"=>$handle,"appointment_id"=>$bookingID])
                ))
            ];
        } else {
            $data = [
                'apiKey' => env('OPENTOK_API_KEY'),
                'sessionId' => $sessionID,
                'token' => $openTokAPI->generateToken($sessionID, array(
                    'expireTime' => time() + (2 * 60 * 60), // in two hours
                    'data'       => json_encode(["type"=>"patient","envornment"=>"dev","handle"=>$handle,"appointment_id"=>$bookingID])
                ))
            ];
        }
        app('redis')->set($bookingID, $sessionID, 'EX', 60 * 60 * 2);
        $data = json_encode($data);
        return json_decode($data);
    }

    public function disconnect($sessionId, $connectionId)
    {
        // var_dump(env('OPENTOK_API_SECRET'));die;
        $openTokAPI = new OpenTok(env('OPENTOK_API_KEY'), env('OPENTOK_API_SECRET'));
        $openTokAPI->forceDisconnect($sessionId, $connectionId);
        return response()->json(['status' => 'success'], 200);
    }

    public function signal($sessionId)
    {
        $openTokAPI = new OpenTok(env('OPENTOK_API_KEY'), env('OPENTOK_API_SECRET'));

        $signalPayload = array(
            'data' => 'start call',
            'type' => 'configure'
        );
        $openTokAPI->signal($sessionId, $signalPayload);
        return response()->json(['status' => 'success'], 200);
    }
    public function callbackUrl($request)
    {
        Log::info('Video Call', ['data' => $request]);
        return response()->json(['status' => 'success'], 200);
    }
}
