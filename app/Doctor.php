<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Doctor extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'docexa_doctor_master';
    protected $primaryKey = 'pharmaclient_id';
    protected $fillable = [
        'pharmaclient_name',
        'email_id',
        'prescription_theme'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function isPharmaClientExist($id)
    {
        try {
            $res = Doctor::where('pharmaclient_id', $id)->exists();
            return $res;
        } catch (\Throwable $th) {
            //throw $th;
            return 500;
        }
    }


    /**
     * Get the user that owns the phone.
     */
    public function aboutme()
    {
        return $this->belongsTo(DoctorAbout::class, 'pharmaclient_id', 'pharmaclient_id');
    }
    public function cities()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
    /**
     * Get the city associated with the clinic.
     */
    public function states()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
    public function education()
    {
        return $this->hasMany(DoctorEducation::class, 'pharmaclient_id', 'pharmaclient_id');
    }
    public function award()
    {
        return $this->hasMany(DoctorAward::class, 'pharmaclient_id', 'pharmaclient_id');
    }
    public function experienced()
    {
        return $this->hasMany(DoctorExperience::class, 'pharmaclient_id', 'pharmaclient_id');
    }
    public function service()
    {
        return $this->hasMany(DoctorServices::class, 'pharmaclient_id', 'pharmaclient_id');
    }
    public function specialization()
    {
        return $this->hasMany(DoctorSpecialization::class, 'pharmaclient_id', 'pharmaclient_id');
    }

}