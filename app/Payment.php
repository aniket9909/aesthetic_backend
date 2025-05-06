<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\AppointmentDetails;
use DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Jobs\updateStatus;
use App\Jobs\updateHospitalStatus;
use Log;

class Payment extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'docexa_appointment_transcation_master';
    protected $primaryKey = 'id';

    public function createpayment($bookingID)
    {
        $res = new AppointmentDetails();
        $appointment_data = $res->getappointment(null, $bookingID);
         Log::info(['appointment dataaaaa in create appointment' => $appointment_data]);
        if (!isset($appointment_data['appointment'][0])) {
            Log::info(['booking id not match']);
            $data['msg'] = "Booking ID not matched";
            return $data;
        } else {
            $appointment_data = $appointment_data['appointment'][0];
            $data['key'] = "MTHxR83B";
            $salt = "jKt5ITcxzH";
            $data['baseurl'] = $_ENV['GETEWAY_URL_V1'];
            //    $data['baseurl'] = "https://secure.payu.in/_payment";   
            $data['amount'] = $appointment_data->cost;
            $data['productinfo'] = $bookingID;
            $data['firstname'] = $appointment_data->patient_name;
            $data['phone'] = $appointment_data->mobile_no;
            $data['email'] = $appointment_data->email;
            $data['txnid'] = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
            $string = $data['key'] . '|' . $data['txnid'] . '|' . $data['amount'] . '|' . $data['productinfo'] . '|' . $data['firstname'] . '|' . $data['email'] . '|||||||||||' . $salt;

            $data['hash'] = strtolower(hash('sha512', $string));
            $data['surl'] = $_ENV['PAYMENT_URL_V1'] . $bookingID . '/success';
            $data['furl'] = $_ENV['PAYMENT_URL_V1'] . $bookingID . '/fail';
            $data['service_provider'] = 'payu_paisa';
            $ctrl = new Controller();
            if($appointment_data->user_map_id == 70246 ||$appointment_data->user_map_id == 68519 || $appointment_data->user_map_id == 70583 || ($appointment_data->user_map_id == 69646 && $appointment_data->sku_id == 198800) || $appointment_data->user_map_id == 70585 || $appointment_data->user_map_id == 70587 ||
            $appointment_data->user_map_id ==79792 || $appointment_data->user_map_id ==79791 || $appointment_data->user_map_id==70671 || $appointment_data->user_map_id==79793 || $appointment_data->user_map_id==45395 || $appointment_data -> user_map_id == 70686 || $appointment_data -> user_map_id == 70684
            ){
                
              //  $appointment_data->user_map_id == 70671 ||
                $data['payment_url'] = $appointment_data->appointment_url;
                //$appointment_data->payment_mode='free';
                $this->paymentupdate($bookingID);
                $urlArray = parse_url($appointment_data->handle, PHP_URL_PATH);
                $segments = explode('/', $urlArray);
                $numSegments = count($segments);
                $handle = $segments[$numSegments - 1];
                $ctrl = new Controller();
                $notificationdata = [
                'template' => 'new_appointment_request_notifier',
                'handle' => $handle,
                'appointment_id' => $bookingID
                ];
                Log::info(['send1']);

                $ctrl->sendNotification($notificationdata);
                
                Log::info(['send2']);
                $notificationdata = [
                    'template' => 'payment_and_appt_request_acknowledgement',
                    'handle' => $handle,
                    'appointment_id' => $bookingID
                    ];
                    Log::info(['send3']);
      

                    // stop if paylater
                // $ctrl->sendNotification($notificationdata);
                Log::info(['send4']);

            }else{
                
                $data['payment_url'] = $ctrl->urlshorten($_ENV['PAYMENT_URL_V1'].$bookingID.'/pay');
                $data['payment_url_full'] = $_ENV['PAYMENT_URL_V1'].$bookingID.'/pay';
                Log::info(['s2']);

            }
            if($appointment_data->payment_mode=='free' || $appointment_data->payment_mode=='direct')
                $this->paymentupdate($bookingID);

                Log::info(['payment data1' => $data]);
            return $data;
        }
    }
    public function createhospitalpayment($bookingID)
    {
        $res = new AppointmentDetails();
        $appointment_data = $res->gethospitalappointment(null, $bookingID);

        if (!isset($appointment_data['appointment'][0])) {
            $data['msg'] = "Booking ID not matched";
            return $data;
        } else {
            $appointment_data = $appointment_data['appointment'][0];
            $data['key'] = "MTHxR83B";
            $salt = "jKt5ITcxzH";
            $data['baseurl'] = $_ENV['GETEWAY_URL_V1'];
            //    $data['baseurl'] = "https://secure.payu.in/_payment";   
            $data['amount'] = $appointment_data->cost;
            $data['productinfo'] = $bookingID;
            $data['firstname'] = $appointment_data->patient_name;
            $data['phone'] = $appointment_data->mobile_no;
            $data['email'] = $appointment_data->email;
            $data['txnid'] = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
            $string = $data['key'] . '|' . $data['txnid'] . '|' . $data['amount'] . '|' . $data['productinfo'] . '|' . $data['firstname'] . '|' . $data['email'] . '|||||||||||' . $salt;

            $data['hash'] = strtolower(hash('sha512', $string));
            $data['surl'] = $_ENV['PAYMENT_URL_V3'] . $bookingID . '/success';
            $data['furl'] = $_ENV['PAYMENT_URL_V3'] . $bookingID . '/fail';
            $data['service_provider'] = 'payu_paisa';
            $ctrl = new Controller();
            if($appointment_data->user_map_id == 70246 ||$appointment_data->user_map_id == 68519 || $appointment_data->user_map_id == 70583 || ($appointment_data->user_map_id == 69646 && $appointment_data->sku_id == 198800) || $appointment_data->user_map_id == 70585 || $appointment_data->user_map_id == 70587 || $appointment_data->user_map_id==70671
            || $appointment_data->user_map_id==45395
            )  {
                
                $data['payment_url'] = $appointment_data->appointment_url;
                //$appointment_data->payment_mode='free';
                $this->hospitalpaymentupdate($bookingID);
                $urlArray = parse_url($appointment_data->handle, PHP_URL_PATH);
                $segments = explode('/', $urlArray);
                $numSegments = count($segments);
                $handle = $segments[$numSegments - 1];
                $ctrl = new Controller();
                $notificationdata = [
                'template' => 'new_appointment_request_notifier',
                'handle' => $handle,
                'appointment_id' => $bookingID
                ];
                
                $ctrl->sendNotification($notificationdata);
                
                $notificationdata = [
                    'template' => 'payment_and_appt_request_acknowledgement',
                    'handle' => $handle,
                    'appointment_id' => $bookingID
                    ];
                $ctrl->sendNotification($notificationdata);
            }else{
                $data['payment_url'] = $ctrl->urlshorten($_ENV['PAYMENT_URL_V3'].$bookingID.'/pay');
            }
            if($appointment_data->payment_mode=='free' || $appointment_data->payment_mode=='direct')
                $this->hospitalpaymentupdate($bookingID);

            return $data;
        }
    }
    public function createpaymentv2($bookingID)
    {
        $res = new AppointmentDetails();
        $appointment_data = $res->getappointmentv3(null, $bookingID);
        if (!isset($appointment_data['appointment'][0])) {
            $data['msg'] = "Booking ID not matched";
            return $data;
        } else {
            $appointment_data = $appointment_data['appointment'][0];
            $data['key'] = "MTHxR83B";
            $salt = "jKt5ITcxzH";
            $data['baseurl'] = $_ENV['GETEWAY_URL_V2'];

            $data['amount'] = $appointment_data->cost;
            $data['productinfo'] = $bookingID;
            $data['firstname'] = $appointment_data->patient_name;
            $data['phone'] = $appointment_data->mobile_no;
            $data['email'] = $appointment_data->email;
            $data['udf2'] = "pfizer";
            $data['txnid'] = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
            $string = $data['key'] . '|' . $data['txnid'] . '|' . $data['amount'] . '|' . $data['productinfo'] . '|' . $data['firstname'] . '|' . $data['email'] . '||' . $data['udf2'] . '|||||||||' . $salt;

            $data['hash'] = strtolower(hash('sha512', $string));
            $data['surl'] = $_ENV['PAYMENT_URL_V2'] . $bookingID . '/success';
            $data['furl'] = $_ENV['PAYMENT_URL_V2'] . $bookingID . '/fail';
            $data['service_provider'] = 'payu_paisa';
            $data['payment_url'] = $_ENV['PAYMENT_URL_V2'] . $bookingID . '/pay';


            return $data;
        }
    }


    function getCallbackUrl()
    {
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . 'response.php';
    }
    public function paymentupdate($appointment_encrypted_id)
    {
        $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
        $res = new AppointmentDetails();
        $appointment_data = $res->getappointment(null, $appointment_encrypted_id);
        Log::info(['getttttttttttt',$appointment_data]);


        


    
        $appointment_data = $appointment_data['appointment'][0];

        if(        $appointment_data->payment_mode =='byPatient'){

        
        $creditid = DB::table('docexa_patient_credit_history')->insertGetId([
            'patient_id' => $appointment_data->patient_id,
            'doctor_id' => $appointment_data->doctor_id,
            'booking_id' => $appointment_data->book_id,
            'credit_point'  => $appointment_data->cost,
            'transaction_id' => $txnid,
            'transaction_date' => date('Y-m-d'),
            'created_date' => date('Y-m-d H:i')
        ]);
        DB::update("update docexa_patient_booking_details set credit_history_id='" . $creditid . "' where booking_id='" . $appointment_data->book_id . "'");
        Log::info(['credit history id updated ']);
    
    }
        
        $urlArray = parse_url($appointment_data->handle, PHP_URL_PATH);
        $segments = explode('/', $urlArray);
        $numSegments = count($segments);
        $appointment_data->handle = $segments[$numSegments - 1];
        Log::info(['aptttttt data' , $appointment_data->handle]);
        
        $data = [
            'template' => 'appointment_accepted_notifier',
            'handle' => $appointment_data->handle,
            'appointment_id' => $appointment_encrypted_id
        ];
        
        $ctrl = new Controller();
        // $ctrl->sendNotification($data);

        $start = Carbon::parse($appointment_data->date . " " . $appointment_data->start_time);
        $start->setTimezone('Asia/Kolkata');
        $us = new updateStatus($appointment_encrypted_id);
        $us->apptID();
        dispatch((new updateStatus($appointment_encrypted_id)));
 
        Log::info(['dispatchhhhhhhh']);

        // dispatch((new updateStatus($appointment_encrypted_id))->delay($start->addMinutes($_ENV['QUEUE_TIME'])));

        // $data = [
        //    'template' => 'new_appointment_request_notifier',
        //    'handle' => $appointment_data->handle,
        //    'appointment_id' => $appointment_encrypted_id
        // ];
        // $ctrl->sendNotification($data);
    }
    public function hospitalpaymentupdate($appointment_encrypted_id)
    {
        $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
        $res = new AppointmentDetails();
        $appointment_data = $res->gethospitalappointment(null, $appointment_encrypted_id);

        $appointment_data = $appointment_data['appointment'][0];
        $creditid = DB::table('docexa_patient_credit_history')->insertGetId([
            'patient_id' => $appointment_data->patient_id,
            'doctor_id' => $appointment_data->doctor_id,
            'booking_id' => $appointment_data->book_id,
            'credit_point'  => $appointment_data->cost,
            'transaction_id' => $txnid,
            'transaction_date' => date('Y-m-d'),
            'created_date' => date('Y-m-d H:i')
        ]);
        DB::update("update docexa_hospital_appointment_sku_details set credit_history_id='" . $creditid . "' where booking_id='" . $appointment_data->book_id . "'");

        $urlArray = parse_url($appointment_data->handle, PHP_URL_PATH);
        $segments = explode('/', $urlArray);
        $numSegments = count($segments);
        $appointment_data->handle = $segments[$numSegments - 1];
      
        $data = [
            'template' => 'appointment_accepted_notifier',
            'handle' => $appointment_data->handle,
            'appointment_id' => $appointment_encrypted_id
        ];
        

        $ctrl = new Controller();
        $ctrl->sendNotification($data);
        $start = Carbon::parse($appointment_data->date . " " . $appointment_data->start_time);
        $start->setTimezone('Asia/Kolkata');
        $us = new updateHospitalStatus($appointment_encrypted_id);
        $us->apptID();
        dispatch((new updateHospitalStatus($appointment_encrypted_id))->delay($start->addMinutes($_ENV['QUEUE_TIME'])));


        $data = [
           'template' => 'new_appointment_request_notifier',
           'handle' => $appointment_data->handle,
           'appointment_id' => $appointment_encrypted_id
        ];
        $ctrl->sendNotification($data);
    }

    public function createpaymentForWalkIn($bookingID)
    {
        $res = new AppointmentDetails();
        $appointment_data = $res->getappointment(null, $bookingID);
         Log::info(['appointment dataaaaa in create appointment' => $appointment_data]);
        if (!isset($appointment_data['appointment'][0])) {
            Log::info(['booking id not match']);
            $data['msg'] = "Booking ID not matched";
            return $data;
        } else {
            $appointment_data = $appointment_data['appointment'][0];
            $data['key'] = "MTHxR83B";
            $salt = "jKt5ITcxzH";
            $data['baseurl'] = $_ENV['GETEWAY_URL_V1'];
            //    $data['baseurl'] = "https://secure.payu.in/_payment";   
            $data['amount'] = $appointment_data->cost;
            $data['productinfo'] = $bookingID;
            $data['firstname'] = $appointment_data->patient_name;
            $data['phone'] = $appointment_data->mobile_no;
            $data['email'] = $appointment_data->email;
            $data['txnid'] = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
            $string = $data['key'] . '|' . $data['txnid'] . '|' . $data['amount'] . '|' . $data['productinfo'] . '|' . $data['firstname'] . '|' . $data['email'] . '|||||||||||' . $salt;

            $data['hash'] = strtolower(hash('sha512', $string));
            $data['surl'] = $_ENV['PAYMENT_URL_V1'] . $bookingID . '/success';
            $data['furl'] = $_ENV['PAYMENT_URL_V1'] . $bookingID . '/fail';
            $data['service_provider'] = 'payu_paisa';
            $ctrl = new Controller();
            if($appointment_data->user_map_id == 70246 ||$appointment_data->user_map_id == 68519 || $appointment_data->user_map_id == 70583 || ($appointment_data->user_map_id == 69646 && $appointment_data->sku_id == 198800) || $appointment_data->user_map_id == 70585 || $appointment_data->user_map_id == 70587 ||
            $appointment_data->user_map_id ==79792 || $appointment_data->user_map_id ==79791 || $appointment_data->user_map_id==70671 || $appointment_data->user_map_id==79793 || $appointment_data->user_map_id==45395 || $appointment_data -> user_map_id == 70686 || $appointment_data -> user_map_id == 70684
            ){
                
              //  $appointment_data->user_map_id == 70671 ||
                $data['payment_url'] = $appointment_data->appointment_url;
                //$appointment_data->payment_mode='free';
                $this->paymentupdatewalkIn($bookingID);
                $urlArray = parse_url($appointment_data->handle, PHP_URL_PATH);
                $segments = explode('/', $urlArray);
                $numSegments = count($segments);
                $handle = $segments[$numSegments - 1];
                
            }else{
                
                $data['payment_url'] = $ctrl->urlshorten($_ENV['PAYMENT_URL_V1'].$bookingID.'/pay');
                $data['payment_url_full'] = $_ENV['PAYMENT_URL_V1'].$bookingID.'/pay';
                Log::info(['s2']);

            }
            if($appointment_data->payment_mode=='free' || $appointment_data->payment_mode=='direct')
                $this->paymentupdatewalkIn($bookingID);

                Log::info(['payment data1' => $data]);
            return $data;
        }
    }


    public function paymentupdatewalkIn($appointment_encrypted_id)
    {
        $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
        $res = new AppointmentDetails();
        $appointment_data = $res->getappointment(null, $appointment_encrypted_id);
        Log::info(['getttttttttttt',$appointment_data]);
    
        $appointment_data = $appointment_data['appointment'][0];

        if(        $appointment_data->payment_mode =='byPatient'){

        
        $creditid = DB::table('docexa_patient_credit_history')->insertGetId([
            'patient_id' => $appointment_data->patient_id,
            'doctor_id' => $appointment_data->doctor_id,
            'booking_id' => $appointment_data->book_id,
            'credit_point'  => $appointment_data->cost,
            'transaction_id' => $txnid,
            'transaction_date' => date('Y-m-d'),
            'created_date' => date('Y-m-d H:i')
        ]);
        DB::update("update docexa_patient_booking_details set credit_history_id='" . $creditid . "' where booking_id='" . $appointment_data->book_id . "'");
        Log::info(['credit history id updated ']);
    
    }
        
        $urlArray = parse_url($appointment_data->handle, PHP_URL_PATH);
        $segments = explode('/', $urlArray);
        $numSegments = count($segments);
        $appointment_data->handle = $segments[$numSegments - 1];
        Log::info(['aptttttt data' , $appointment_data->handle]);
        
        $data = [
            'template' => 'appointment_accepted_notifier',
            'handle' => $appointment_data->handle,
            'appointment_id' => $appointment_encrypted_id
        ];
        
        $ctrl = new Controller();
        $start = Carbon::parse($appointment_data->date . " " . $appointment_data->start_time);
        $start->setTimezone('Asia/Kolkata');
        $us = new updateStatus($appointment_encrypted_id);
        $us->apptID();
        dispatch((new updateStatus($appointment_encrypted_id)));
 
        Log::info(['dispatchhhhhhhh']);

    
    }
}