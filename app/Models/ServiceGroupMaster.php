<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceGroupMaster extends Model
{
    protected $table = 'group_master';

    protected $fillable = [
        "name",
        "package_type",
        "package_amount",
        "total_discount",
        "total_tax",
        "validity_months",
        "remarks"
    ];

    public function services()
    {
        return $this->hasMany(ServiceGroupMaster::class, 'group_master_id');
    }
}
