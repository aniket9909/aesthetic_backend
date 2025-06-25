<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsumableUsageLog extends Model
{
    protected $table = 'consumable_usage_logs';
    protected $fillable = [
        'enrollment_transaction_id',
        'enrollment_item_id',
        'consumable_id',
        'used_quantity',
        'used_unit',
        'used_by_doctor_id',
        'used_at',
        'remarks',
        'session_log_id',
    ];
    protected $with = ['consumableInfo'];
    public function consumableInfo(){
        return $this->belongsTo(ConsumableMaster::class, 'consumable_id', 'id');
    }
}
    