<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Medicalestablishmentsmedicalusermap extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'docexa_medical_establishments_medical_user_map';
    protected $primaryKey = 'id';
    
     /**
     * Get the phone associated with the user.
     */
    public function doctors()
    {
        return $this->hasOne(Doctor::class,'pharmaclient_id','medical_user_id');
    }
    // public function ScopeDoctorDetails($query)
    // {
    //     return $query->join('docexa_doctor_master','docexa_doctor_master.pharmaclient_id','docexa_medical_establishments_medical_user_map.medical_user_id');
    // }
    public function clinicDetails($id)
    {
        return Clinic::where('user_map_id',$id)->get();
    }
    public function skuDetails($id)
    {
        return Skuusermap::where('user_map_id',$id)->get();
    }
}
