<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceMaster extends Model
{
    protected $fillable = [
        'name',
        'description',
        'base_price',
        'category',
        'is_tax_applied',
        'tax_percent'

    ];
    protected $table = 'service_master';
    protected $primaryKey = 'id';
    protected $with = ['consumable'];
    public function services()
    {
        return $this->hasMany(ServiceGroupItems::class, 'group_master_id');
    }
    public function group()
    {
        return $this->belongsTo(ServiceGroupMaster::class, 'group_master_id');
    }
    public function consumable()
    {
        return $this->hasMany(ServiceConsumable::class, 'service_master_id','id');
    }
}
