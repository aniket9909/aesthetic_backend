<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Patientmasterphizer extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'docexa_patient_details_phizer';
    protected $primaryKey = 'patient_id';
    
}
