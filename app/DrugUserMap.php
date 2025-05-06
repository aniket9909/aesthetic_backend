<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DrugUserMap extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'drug_user_map_list';
    protected $primaryKey = 'id';
 
}
