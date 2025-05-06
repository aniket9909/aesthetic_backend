<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'drug_list';
    protected $primaryKey = 'id';
 
}
