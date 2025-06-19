<?php

namespace App;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;

class Patientmaster extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'docexa_patient_details';
    protected $primaryKey = 'patient_id';
    public function search($key, $value)
    {
        $patient = [];
        if ($key == 'patient_id') {
            $result = Patientmaster::where($key, $value)->first();
            $totalCount = $result->count();
            $patient = [
                "patient_id" => $result->patient_id,
                "patient_name" => $result->patient_name,
                "email" => $result->email_id,
                "mobile_no" => $result->mobile_no,
                "age" => $result->age,
                "gender" => $result->gender,
                "dob" => $result->dob,
                "address" => $result->address,
                "health_id" => $result->health_id,
                "pincode" => $result->pincode,
                "occupation" => $result->occupation,
                "visit_type" => $result->visit_type,
                "created_by_doctor" => $result->created_by_doctor,
                "registered_date" => $result->created_at,
                "flag" => $result ->flag,
                "language" => $result -> language
            ];
        } else {
            $results = Patientmaster::where('patient_name', 'like', '%' . $value . '%')->orWhere('email_id', 'like', '%' . $value . '%')->orWhere('mobile_no', 'like', '%' . $value . '%')->get();
            $totalCount = $results->count();
            foreach ($results as $result) {
                $patient[] = [
                    "patient_id" => $result->patient_id,
                    "patient_name" => $result->patient_name,
                    "email" => $result->email_id,
                    "mobile_no" => $result->mobile_no,
                    "age" => $result->age,
                    "gender" => $result->gender,
                    "dob" => $result->dob,
                    "address" => $result->address,
                    "health_id" => $result->health_id,
                    "pincode" => $result->pincode,
                    "occupation" => $result->occupation,
                    "visit_type" => $result->visit_type,
                    "created_by_doctor" => $result->created_by_doctor,
                    "registered_date" => $result->created_at,
                    "flag" => $result ->flag,
                    "language" => $result -> language
                ];
            }
        }

        return array("patient" => $patient , "total_count" =>$totalCount);
    }
    public function patientlist($esteblishmentusermapID)
    {
        $patient = [];
        //$result =  Patientmaster::join('docexa_patient_booking_details', 'docexa_patient_booking_details.patient_id', '=', 'docexa_patient_details.patient_id')->where('docexa_patient_booking_details.status', '!=', 3)->where('docexa_patient_booking_details.status', '!=', 1)->where('user_map_id', $esteblishmentusermapID)->groupBy('docexa_patient_booking_details.patient_id')->orderBy('docexa_patient_details.patient_name')->get();
        $result = Patientmaster::join('docexa_patient_doctor_relation', 'docexa_patient_doctor_relation.patient_id', 'docexa_patient_details.patient_id')->where('docexa_patient_doctor_relation.user_map_id', $esteblishmentusermapID)
        ->orderBy('docexa_patient_details.created_at', 'desc')
        ->distinct()->get();
        foreach ($result as $r) {
            $patient[] = [
                "patient_id" => $r->patient_id,
                "patient_name" => $r->patient_name,
                "email" => $r->email_id,
                "mobile_no" => $r->mobile_no,
                "age" => $r->age,
                "gender" => $r->gender,
                "dob" => $r->dob,
                "address" => $r->address,
                "health_id" => $r->health_id,
                "pincode" => $r->pincode,
                "occupation" => $r->occupation,
                "visit_type" => $r->visit_type,
                "registered_date" => $r->created_at,
                "last_appointment_date" => DB::table('docexa_patient_booking_details')->where(array('docexa_patient_booking_details.patient_id' => $r->patient_id, 'docexa_patient_booking_details.user_map_id' => $esteblishmentusermapID))->orderBy('date')->max('date') == null ? $r->created_at : DB::table('docexa_patient_booking_details')->where(array('docexa_patient_booking_details.patient_id' => $r->patient_id, 'docexa_patient_booking_details.user_map_id' => $esteblishmentusermapID))->orderBy('date',)->max('date')
            ];
        }

        return array("patient" => $patient);
    }
    public function getuserinfo($patient_id)
    {
        $tabdata = Patientmaster::find($patient_id);
        if (isset($tabdata->patient_id)) {
            
            $response = [
                'patient_id' => $tabdata->patient_id,
                'patient_name' => $tabdata->patient_name,
                'dob' => $tabdata->dob,
                'email_id' => $tabdata->email_id,
                'mobile_no' => $tabdata->mobile_no,
                'gender'=> $tabdata ->gender,
                'age' => $tabdata->age
                // 'patient_image' => (strpos($tabdata->patient_image, 'docexa_default_image') !== false) ? URL::Asset('upload/doctor/profile/' . $tabdata->patient_image) : $tabdata->patient_image
            ];
            return $response;
        } else {
            return [];
        }
    }

    public function patientlistv2($esteblishmentusermapID, $page, $limit)
{
    $patient = [];

    // Calculate the offset
    $offset = ($page - 1) * $limit;

    // Modify the query to include limit and offset for pagination
    $result = Patientmaster::join('docexa_patient_doctor_relation', 'docexa_patient_doctor_relation.patient_id', 'docexa_patient_details.patient_id')
        ->where('docexa_patient_doctor_relation.user_map_id', $esteblishmentusermapID)
        // ->orderBy('docexa_patient_details.patient_name')
        ->orderBy('docexa_patient_details.patient_id', 'desc')
        ->distinct()
        ->offset($offset)
        ->limit($limit)
        ->get();

        // $totalPatients = Patientmaster::join('docexa_patient_doctor_relation', 'docexa_patient_doctor_relation.patient_id', 'docexa_patient_details.patient_id')
        // ->where('docexa_patient_doctor_relation.user_map_id', $esteblishmentusermapID)
        // ->distinct()
        // ->count('docexa_patient_details.patient_id');

        
        $totalPatientCount = DB::table('docexa_patient_doctor_relation')->where('user_map_id', $esteblishmentusermapID)
        ->count();

    foreach ($result as $r) {
        $lastAppointmentDate = DB::table('docexa_patient_booking_details')
            ->where(['docexa_patient_booking_details.patient_id' => $r->patient_id, 'docexa_patient_booking_details.user_map_id' => $esteblishmentusermapID])
            ->orderBy('date')
            ->max('date');

        $patient[] = [
            "patient_id" => $r->patient_id,
            "patient_name" => $r->patient_name,
            "email" => $r->email_id,
            "mobile_no" => $r->mobile_no,
            "age" => $r->age,
            "gender" => $r->gender,
            "dob" => $r->dob,
            "address" => $r->address,
            "health_id" => $r->health_id,
            "pincode" => $r->pincode,
            "occupation" => $r->occupation,
            "visit_type" => $r->visit_type,
            "registered_date" => Carbon::parse($r->created_at)->format('Y-m-d H:i:s'),
            "last_appointment_date" => $lastAppointmentDate ? Carbon::parse($lastAppointmentDate)->format('Y-m-d H:i:s') : Carbon::parse($r->created_at)->format('Y-m-d H:i:s'),
            "state_id" => $r->state_id,
            "state" => $r->state,
            "city_id" =>$r->city_id,
            "city" =>$r->city,
            "flag" => $r ->flag,
            "language" => $r -> language

        ];
    }

    return array("patient" => $patient , "total_patient" =>  $totalPatientCount);
}

public function searchv2($esteblishmentusermapID,$key, $value, $page, $limit)
{
    $patient = [];
    $totalCount = 0;
    $offset = ($page - 1) * $limit;
    // dd($offset);
    // Define the valid columns to search against
    $validColumns = ['patient_id', 'patient_name', 'email_id', 'mobile_no'];

    // Validate the key
    if (!in_array($key, $validColumns)) {
        return response()->json(['status' => "fail", 'msg' => "Invalid search key"], 400);
    }

    if ($key == 'patient_id') {
        // $result = Patientmaster::where($key, $value)->where('created_by_doctor',$esteblishmentusermapID)->first();
        $result = Patientmaster::where($key, $value)
        ->join('docexa_patient_doctor_relation', 'docexa_patient_doctor_relation.patient_id', '=', 'docexa_patient_details.patient_id')
        // ->where('docexa_patient_details.' . $key, $value)    
         ->where('docexa_patient_doctor_relation.user_map_id', $esteblishmentusermapID)
        ->select('docexa_patient_details.*') 
        ->first();

        $totalCount = $result ? 1 : 0;
        if ($totalCount > 0) {
            $patient = [
                "patient_id" => $result->patient_id,
                "patient_name" => $result->patient_name,
                "email" => $result->email_id,
                "mobile_no" => $result->mobile_no,
                "age" => $result->age,
                "gender" => $result->gender,
                "dob" => $result->dob,
                "address" => $result->address,
                "health_id" => $result->health_id,
                "pincode" => $result->pincode,
                "occupation" => $result->occupation,
                "visit_type" => $result->visit_type,
                "created_by_doctor" => $result->created_by_doctor,
                "registered_date" => $result->created_at,
                "flag" => $result ->flag , 
                "language" => $result -> language
            ];
        }
    } 
    else{
        // $query = Patientmaster::query()
        //     ->where($key, 'like', '%' . $value . '%')
        //     ->where('created_by_doctor',$esteblishmentusermapID);

        $query = Patientmaster::query()->where($key, 'like', '%' . $value . '%')
            ->join('docexa_patient_doctor_relation', 'docexa_patient_doctor_relation.patient_id', '=', 'docexa_patient_details.patient_id')
            ->where('docexa_patient_doctor_relation.user_map_id', $esteblishmentusermapID)
            ->select('docexa_patient_details.*');
            // ->get();

        $totalCount = $query->count();

        $results = $query->offset($offset)
                         ->limit($limit)
                         ->get();

        foreach ($results as $result) {
            $patient[] = [
                "patient_id" => $result->patient_id,
                "patient_name" => $result->patient_name,
                "email" => $result->email_id,
                "mobile_no" => $result->mobile_no,
                "age" => $result->age,
                "gender" => $result->gender,
                "dob" => $result->dob,
                "address" => $result->address,
                "health_id" => $result->health_id,
                "pincode" => $result->pincode,
                "occupation" => $result->occupation,
                "visit_type" => $result->visit_type,
                "created_by_doctor" => $result->created_by_doctor,
                "registered_date" => $result->created_at,
                "flag" => $result ->flag ,
                "language" => $result -> language
            ];
        }
    }

    return array("patient" => $patient, "total_count" => $totalCount);
}


public function searchv3($esteblishmentusermapID,$key, $value)
{
    $patient = [];
    $totalCount = 0;
    // $offset = ($page - 1) * $limit;
    // dd($offset);
    // Define the valid columns to search against
    $validColumns = ['patient_id', 'patient_name', 'email_id', 'mobile_no'];

    // Validate the key
    if (!in_array($key, $validColumns)) {
        return response()->json(['status' => "fail", 'msg' => "Invalid search key"], 400);
    }

    if ($key == 'patient_id') {
        // $result = Patientmaster::where($key, $value)->where('created_by_doctor',$esteblishmentusermapID)->first();
        $result = Patientmaster::where($key, $value)
        ->join('docexa_patient_doctor_relation', 'docexa_patient_doctor_relation.patient_id', '=', 'docexa_patient_details.patient_id')
        // ->where('docexa_patient_details.' . $key, $value)    
         ->where('docexa_patient_doctor_relation.user_map_id', $esteblishmentusermapID)
        ->select('docexa_patient_details.*') 
        ->first();

        $totalCount = $result ? 1 : 0;
        if ($totalCount > 0) {
            $patient = [
                "patient_id" => $result->patient_id,
                "patient_name" => $result->patient_name,
                "email" => $result->email_id,
                "mobile_no" => $result->mobile_no,
                "age" => $result->age,
                "gender" => $result->gender,
                "dob" => $result->dob,
                "address" => $result->address,
                "health_id" => $result->health_id,
                "pincode" => $result->pincode,
                "occupation" => $result->occupation,
                "visit_type" => $result->visit_type,
                "created_by_doctor" => $result->created_by_doctor,
                "registered_date" => $result->created_at,
                "flag" => $result->flag
            ];
        }
    } 
    else{
        // $query = Patientmaster::query()
        //     ->where($key, 'like', '%' . $value . '%')
        //     ->where('created_by_doctor',$esteblishmentusermapID);

        $query = Patientmaster::query()->where($key, 'like', '%' . $value . '%')
            ->join('docexa_patient_doctor_relation', 'docexa_patient_doctor_relation.patient_id', '=', 'docexa_patient_details.patient_id')
            ->where('docexa_patient_doctor_relation.user_map_id', $esteblishmentusermapID)
            ->select('docexa_patient_details.*');
            // ->get();

        $totalCount = $query->count();
        $results = $query->get();
        foreach ($results as $result) {
            $patient[] = [
                "patient_id" => $result->patient_id,
                "patient_name" => $result->patient_name,
                "email" => $result->email_id,
                "mobile_no" => $result->mobile_no,
                "age" => $result->age,
                "gender" => $result->gender,
                "dob" => $result->dob,
                "address" => $result->address,
                "health_id" => $result->health_id,
                "pincode" => $result->pincode,
                "occupation" => $result->occupation,
                "visit_type" => $result->visit_type,
                "created_by_doctor" => $result->created_by_doctor,
                "registered_date" => $result->created_at,
                "flag" => $result->flag
            ];
        }
    }

    return array("patient" => $patient, "total_count" => $totalCount);
}


public function getPatientByMobileNumber($esteblishmentusermapID,$mobileNumber){
  
    $controller = new Controller();
        $result = Patientmaster::where('mobile_no', $mobileNumber)
        ->where('created_by_doctor',$esteblishmentusermapID)
        ->get();
        if (count($result) > 0) {
            foreach ($result as $r) {

                $existDoctorPatientRelation = DB::table('docexa_patient_doctor_relation')->where('user_map_id' , $esteblishmentusermapID)->where('patient_id',$r->patient_id)->first();

                if($existDoctorPatientRelation){
                    $patient[] = [
                        "patient_id" => $r->patient_id,
                        "health_id" => $r->health_id,
                        "patient_name" => $r->patient_name,
                        "dob" => $r->dob,
                        "gender" => $r->gender,
                        "mobile" => $r->mobile_no,
                        "state_id" => $r->state,
                        "state" => $controller->stateName($r->state),
                        "city_id" => $r->city,
                        "city" => $controller->cityName($r->city),
                        "pincode" => $r->pincode,
                        "visit_type" => $r->visit_type,
                        "occupation" => $r->occupation,
                        "registered_date" => $r->created_at,
                        "last_appointment_date" => $r->updated_at
                    ];
                }else {
                    return null;
                }
               
            }

            return $patient;
        }
        return null;

}
public function patientlistV4($esteblishmentusermapID)
{
    $patient = [];
    //$result =  Patientmaster::join('docexa_patient_booking_details', 'docexa_patient_booking_details.patient_id', '=', 'docexa_patient_details.patient_id')->where('docexa_patient_booking_details.status', '!=', 3)->where('docexa_patient_booking_details.status', '!=', 1)->where('user_map_id', $esteblishmentusermapID)->groupBy('docexa_patient_booking_details.patient_id')->orderBy('docexa_patient_details.patient_name')->get();
    $result = Patientmaster::join('docexa_patient_doctor_relation', 'docexa_patient_doctor_relation.patient_id', 'docexa_patient_details.patient_id')
    ->where('docexa_patient_doctor_relation.user_map_id', $esteblishmentusermapID)
    ->where('docexa_patient_details.deleted_date', null)
    ->orderBy('docexa_patient_details.created_at', 'desc')
    ->distinct()->get();
    foreach ($result as $r) {
        $patient[] = [
            "patient_id" => $r->patient_id,
            "patient_name" => $r->patient_name,
            "email" => $r->email_id,
            "mobile_no" => $r->mobile_no,
            "age" => $r->age,
            "gender" => $r->gender,
            "dob" => $r->dob,
            "address" => $r->address,
            "health_id" => $r->health_id,
            "pincode" => $r->pincode,
            "occupation" => $r->occupation,
            "visit_type" => $r->visit_type,
            "registered_date" => $r->created_at,
            "last_appointment_date" => DB::table('docexa_patient_booking_details')->where(array('docexa_patient_booking_details.patient_id' => $r->patient_id, 'docexa_patient_booking_details.user_map_id' => $esteblishmentusermapID))->orderBy('date')->max('date') == null ? $r->created_at : DB::table('docexa_patient_booking_details')->where(array('docexa_patient_booking_details.patient_id' => $r->patient_id, 'docexa_patient_booking_details.user_map_id' => $esteblishmentusermapID))->orderBy('date',)->max('date')
        ];
    }

    return array("patient" => $patient);
}

}