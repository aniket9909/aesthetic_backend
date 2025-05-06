<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreatmentUserMap extends Model
{

    protected $table = 'establishment_treatment_master';

    public function details()
    {
        return $this->hasMany(TreatmentUserMap::class,'parent_treatment','id');
    }
    

}