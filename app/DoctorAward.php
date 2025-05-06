<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class DoctorAward extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'docexa_doctor_awards';
    
    protected $fillable = [
        'award_name', 'year','pharmaclient_id'
    ];



}
