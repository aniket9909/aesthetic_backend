<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTransaction extends Model
{
    protected $table = 'service_enrollment_transactions';
    protected $with = ['groupInfo','serviceTransactionItems'];


    public function serviceTransactionItems()
    {
        return $this->hasMany(ServiceTransactionItems::class, 'enrollment_transaction_id');
    }
    public function groupInfo()
    {
        return $this->belongsTo(ServiceGroupMaster::class, 'group_master_id', 'id');
    }
}
