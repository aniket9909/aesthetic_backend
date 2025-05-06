<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class InvestigationTestsusermapModel extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'investigation_tests_user_map';
    protected $primaryKey = 'id';
}