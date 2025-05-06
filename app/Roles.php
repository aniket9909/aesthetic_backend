<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Roles extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'usertype';
    protected $primaryKey = 'usertypeID';
    
}
