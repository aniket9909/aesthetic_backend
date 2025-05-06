<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class State extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'docexa_state_master';
    protected $primaryKey = 'state_id';
    
}
