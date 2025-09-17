<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DoctorSuggestedTreatment;

class ConsentForm extends Model
{
    protected $table = 'consent_forms';
     protected $fillable = [
        'doctor_id',
        'patient_id',
        'form_type',
        'name',
        'address',
        'contact_no',
        'email',
        'date',
        'exp_date',
        'medical_history',
        'medications',
        'procedures',
        'doctor_sign',
        'patient_sign',
        'is_submitted_by_doctor',
        'is_submitted_by_patient',
        'is_complete',
    ];

    public function treatmentHistories()
    {
        return $this->hasMany(DoctorSuggestedTreatment::class);
    }
}
