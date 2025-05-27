<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTransaction extends Model
{
    protected $table = 'service_enrollment_transactions';

  
    public function serviceTransactionItems()
    {
        return $this->hasMany(ServiceTransactionItems::class, 'enrollment_transaction_id');
    }
}
