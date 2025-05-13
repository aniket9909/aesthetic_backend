<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConverstionState extends Model
{
    protected $table = 'conversation_states';
    protected $fillable = [
        'user_id',
        'current_state',
        'flow_type',
        'data',
        'is_active',
    ];

}
