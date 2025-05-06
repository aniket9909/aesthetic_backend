<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class PrescriptionItems extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'prescription_items';
    protected $primaryKey = 'id';
}
