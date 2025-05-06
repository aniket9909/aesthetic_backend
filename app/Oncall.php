<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Oncall extends Model
{
    protected $table = 'doctor_oncall_appointment';

    public function doctor()
    {
        return $this->belongsTo(Doctor::class,'user_map_id','pharmaclient_id');
    }

}