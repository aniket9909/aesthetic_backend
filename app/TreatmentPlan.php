<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TreatmentPlan extends Model
{

    protected $table = 'treatment_plan';
    
    public function details()
    {
        return $this->hasMany(TreatmentPlanDetail::class,'plan_id','id');
    }
    public function treatment()
    {
        $treatment = new TreatmentPlanDetail();
        return $treatment->treatmentdetails();
    }

}