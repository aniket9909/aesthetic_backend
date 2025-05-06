<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class MedicationTemplate extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'medication_template';
    protected $primaryKey = 'id';
}
