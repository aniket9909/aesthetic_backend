<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class ConsultComplaintsUsermapModel extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'consult_complaints_usermap';
    protected $primaryKey = 'id';
}