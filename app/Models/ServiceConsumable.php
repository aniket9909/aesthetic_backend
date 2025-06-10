<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceConsumable extends Model
{
    protected $table = 'service_consumables';
    
    protected $with = ['consumableInfo'];

    protected $fillable = ['service_master_id', 'consumable_id', 'quantity'];

    public function consumableInfo()
    {
        return $this->belongsTo(ConsumableMaster::class, 'consumable_id');
    }

}
