<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Staff extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'staff';
    protected $primaryKey = 'id';
    
}
