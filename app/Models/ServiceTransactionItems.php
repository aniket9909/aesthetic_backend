<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTransactionItems extends Model
{
    protected $table = 'service_enrollment_items';
    protected $with = ['service','sessions'];

    public function consumable()
    {
        return $this->hasMany(ConsumableUsageLog::class, 'enrollment_item_id');
    }
     public function service()
    {
        return $this->belongsTo(ServiceMaster::class, 'service_master_id','id',);
    }
     public function sessions()
    {
        return $this->hasMany(ServiceSessionLog::class, 'enrollment_item_id');
    }
}
