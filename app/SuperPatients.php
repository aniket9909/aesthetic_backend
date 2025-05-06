<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;

class SuperPatients extends Model
{
  /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'docexa_super_patient_details';
    protected $primaryKey = 'patient_id';

    // protected $fillable = [
    //         'health_id', 'patient_name', 'gender', 'dob', 'mobile', 'occupation', 'state', 'city', 'pincode', 'created_by'
    //     ];
        
      
        protected $fillable = ['patient_id', 'patient_name', 'patient_image', 'gender', 'mobile_no', 'email_id', 'dob', 'city', 'city_id', 'state', 'state_id', 'password', 'username', 'patient_from', 'registration_type', 'created_date', 'deleted_date', 'chemist_id', 'device_web_type', 'upi_id', 'updated_at', 'age', 'address', 'height', 'weight', 'valid_from', 'valid_to', 'relationship', 'any_chronic_disease', 'subscription_fee', 'membership_no', 'medication', 'doctor_flag', 'mode_of_payment', 'care_context', 'created_at', 'health_id', 'pincode', 'occupation', 'visit_type', 'created_by_doctor'];

        
}