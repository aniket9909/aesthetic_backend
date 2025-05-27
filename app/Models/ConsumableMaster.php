<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsumableMaster extends Model
{
    protected $table = 'consumables';
    protected $fillable = ['name', 'unit', 'description'];
}
