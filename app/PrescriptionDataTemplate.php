<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class PrescriptionDataTemplate extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'prescription_template_record';
    protected $primaryKey = 'id';
}
