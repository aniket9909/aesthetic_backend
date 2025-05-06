<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class DoctorServices extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'docexa_doctor_services';
    protected $primaryKey = 'id';
    protected $fillable = [
        'pharmaclient_id', 'services',
    ];



}
