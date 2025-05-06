<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Certificate extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'certificate';
    protected $primaryKey = 'id';

    public function doctor(){
        return $this->belongsTo(Doctor::class,'user_map_id','user_map_id');
     }
    
}
