<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class VitalTemplate extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'docexa_vital_record_template';
    public $timestamps = true;
}
