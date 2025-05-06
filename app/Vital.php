<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Vital extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'docexa_vital_record';
    public $timestamps = true;
}
