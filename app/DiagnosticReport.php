<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class DiagnosticReport extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'docexa_emr_diagnostic_report';
    protected $primaryKey = 'id';

    public function getDiagnosticReport($patient_id)
    {
        $res = DiagnosticReport::where('patient_id', $patient_id)->get();
        return $res;
    }
}