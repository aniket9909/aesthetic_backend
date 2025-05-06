<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Speciality extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'docexa_speciality_master';
    protected $primaryKey = 'speciality_id';
    
}
