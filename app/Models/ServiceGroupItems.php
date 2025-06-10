<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceGroupItems extends Model
{
    protected $table = 'group_service_items';
    protected $with = ['services'];

    protected $fillable = [
        "group_master_id",
        "service_master_id",
        "custom_price",
        "tax_amount",
        "discount_amount",
        "total_sessions",
        "completed_sessions",
        "is_tax_inclusive",
        'total',
        'tax_per'
    ];

    public function services()
    {
        return $this->belongsTo(ServiceMaster::class, 'service_master_id',);
    }
}
