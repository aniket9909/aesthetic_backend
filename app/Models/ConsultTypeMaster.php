<?php

namespace App\Models;

use App\Staff;
use Illuminate\Database\Eloquent\Model;

class ConsultTypeMaster extends Model
{
    protected $table = "consult_type_master";
    protected $fillable = [
        'name',
        'parent_id',
        'description',
        'is_active',
        'staff_id'
    ];
    protected $with = ['staff' ];
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
