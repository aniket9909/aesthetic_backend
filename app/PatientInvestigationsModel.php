<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class PatientInvestigationsModel extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'patient_investigations';
    protected $primaryKey = 'id';


}