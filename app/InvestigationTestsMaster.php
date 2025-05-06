<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class InvestigationTestsMaster extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'investigation_tests';
    protected $primaryKey = 'id';
    public $timestamps = false;



}