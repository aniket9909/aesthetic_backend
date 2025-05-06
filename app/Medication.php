<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class Medication extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'medication';
    protected $primaryKey = 'id';
}
