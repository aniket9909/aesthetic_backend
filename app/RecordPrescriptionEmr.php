<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;

class RecordPrescriptionEmr extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'emr_patient_record_prescription';
    protected $primaryKey = 'id';
    public function getRecordPrescriptionList($docId,$patientId)
    {
        $reports = RecordPrescriptionEmr::where('patient_id', $patientId)->where('user_map_id', $docId)->orderBy('created_at', 'desc')->get()->all();
        if (count($reports) > 0) {
            return $reports;
        }
        return null;
    }



}