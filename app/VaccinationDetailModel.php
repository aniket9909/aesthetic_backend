<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class VaccinationDetailModel extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'vaccination_details';
    protected $primaryKey = 'id';

    // public function getViewOfPrescription($id,$patientId,$bookingId){
    //     $results = PrescriptionData ::where('patient_id', $patientId)->where('user_map_id',$id)->where('booking_id', $bookingId)->first();
    //     if($results){
    //         return $results;
    //     }else{
    //         return false;
    //     }
    // }
    protected $fillable = [
        'vaccine_name',
        'vaccine_date',
        'brand_name',
        'given_date',
        'notes',
        'patient_id',
        'user_map_id',
        'vaccine_category',
        'prescription_id',
        'flag',
        'deleted_by',
    ];
    
}
