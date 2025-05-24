<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceMaster extends Model
{
    protected $fillable=[
        'name',
        'desciption','base_price','category','is_tax_applied','tax_percent'
        
    ];
    protected $table = 'service_master';
    public function services()
{
    return $this->hasMany(ServiceGroupItems::class, 'group_master_id');
}
public function group()
{
    return $this->belongsTo(ServiceGroupMaster::class, 'group_master_id');
}

}
