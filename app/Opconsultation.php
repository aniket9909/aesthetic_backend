<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Opconsultation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'docexa_emr_op_consultation';
    protected $primaryKey = 'id';

    public function getOpconsultationReport($patient_id)
    {
        $res = Opconsultation::where('patient_id', $patient_id)->get();
        return $res;
    }
}