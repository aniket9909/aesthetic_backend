<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSessionLog extends Model
{
    protected $table = 'service_session_logs';
    
    protected $fillable = [
        'enrollment_item_id',
        'session_number',
        'conducted_at',
        'conducted_by_doctor_id',
        'remarks',
        "prescription_id"
    ];
}
