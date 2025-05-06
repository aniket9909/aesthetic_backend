<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Vitalusermap extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'doc_vital_user_map';
    protected $primaryKey = 'id';

    protected $fillable = ['user_map_id'];
  
}
