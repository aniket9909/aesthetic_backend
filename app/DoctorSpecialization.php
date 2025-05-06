<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class DoctorSpecialization extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'docexa_doctor_specialization';
    protected $primaryKey = 'id';
    protected $fillable = [
        'pharmaclient_id', 'specialization',
    ];



}
