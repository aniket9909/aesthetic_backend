<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTransactionItems extends Model
{
    protected $table = 'service_enrollment_items';
    protected $with = ['consumable'];
    public function consumable()
    {
        return $this->belongsTo(ServiceConsumable::class, 'service_master_id');
    }
}
