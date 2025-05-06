<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class DoctorExperience extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'docexa_doctor_experience';
    protected $fillable = [
        'organization', 'year','experience', 'pharmaclient_id','from_date','to_date','designation'
    ];

    


}
