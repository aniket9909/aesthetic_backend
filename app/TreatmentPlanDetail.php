<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreatmentPlanDetail extends Model
{

    protected $table = 'treatment_plan_detail';
    public function treatmentdetails()
    {
        return $this->BelongsTo(TreatmentUserMap::class,'treatment_id','id');
    }

}