<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class DoctorAbout extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'docexa_doctor_about_me';
    protected $primaryKey = 'id';
    protected $fillable = [
        'pharmaclient_id', 'about_me',
    ];



}
