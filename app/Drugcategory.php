<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Drugcategory extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'drug_category';
    protected $primaryKey = 'id';
 
}
