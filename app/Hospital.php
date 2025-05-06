<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hospital extends Model
{
    protected $table = 'docexa_hospital_master';
    protected $hidden = [
        'login_name',
        'password',
    ];
    public function services(): HasMany
    {
        return $this->hasMany(HospitalServices::class,'hospital_id','hospital_id');
    }
    public function coverimg(): HasMany
    {
        return $this->hasMany(HospitalBanner::class,'hospital_id','hospital_id');
    }
    
    public function scopeDoctors($query)
    {
       return $query->join('doctor_hospital_relation','doctor_hospital_relation.hospital_id','docexa_hospital_master.hospital_id')
       ->join('docexa_doctor_master','doctor_hospital_relation.pharmaclient_id','docexa_doctor_master.pharmaclient_id')
       ->join('docexa_medical_establishments_medical_user_map','docexa_medical_establishments_medical_user_map.medical_user_id','docexa_doctor_master.pharmaclient_id')
       ->join('docexa_doctor_speciality_relation','docexa_doctor_speciality_relation.user_map_id','docexa_medical_establishments_medical_user_map.id')
       ->join('docexa_speciality_master','docexa_speciality_master.speciality_id','docexa_doctor_speciality_relation.speciality_id')->orderBy('doctor_hospital_relation.order_id','ASC');
        
    }

    

}
