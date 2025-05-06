<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class DoctorFee extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'docexa_doctor_rate_details';
    protected $primaryKey = 'rate_id';
    
}
