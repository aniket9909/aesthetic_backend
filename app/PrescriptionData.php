<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class PrescriptionData extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'prescription';
    protected $primaryKey = 'id';

    public function getViewOfPrescription($id,$patientId,$bookingId){
        $results = PrescriptionData ::where('patient_id', $patientId)->where('user_map_id',$id)->where('booking_id', $bookingId)->first();
        if($results){
            return $results;
        }else{
            return false;
        }
    }

   
}
