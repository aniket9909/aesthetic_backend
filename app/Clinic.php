<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\City;
use App\State;
use App\Slotmaster;
class Clinic extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'docexa_clinic_user_map';
    protected $primaryKey = 'id';
    /**
     * Get the city associated with the clinic.
     */
    public function cities(){
        return $this->belongsTo(City::class,'city');
     }
     /**
     * Get the city associated with the clinic.
     */
    public function states(){
        return $this->belongsTo(State::class,'state');
     }

     public function slot(){
        return $this->hasMany(Slotmaster::class,'clinicID')->orderBy('user_map_id')->orderBy('day_id');
     }
}
