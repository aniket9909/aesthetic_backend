<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class Accountmaster extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'docexa_doctor_bank_details';
    protected $primaryKey = 'doctor_bank_id';
    protected $fillable = ['user_map_id'];
}