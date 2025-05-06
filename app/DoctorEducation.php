<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class DoctorEducation extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'docexa_doctor_education';
    
    protected $fillable = [
        'pharmaclient_id', 'qualification','university','year'
    ];



}
