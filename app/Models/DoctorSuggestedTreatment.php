<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ConsentForm;
use App\Models\ServiceMaster;


class DoctorSuggestedTreatment extends Model
{
    protected $table = 'doctor_suggested_treatments';
    protected $with = ['service'];
    protected $fillable = [

        'consent_form_id',
        'service_id',
        'treatment',
        'mode',
        'paid',
        'total_amount',
        'date',
    ];

    public function consentForm()
    {
        return $this->belongsTo(ConsentForm::class);
    }
    public function service()
    {
        return $this->hasMany(ServiceMaster::class,'id','service_id');
    }

}
