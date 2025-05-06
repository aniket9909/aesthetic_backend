<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;
class PrescriptionNotesModel
 extends Model
 {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'prescription_notes';
    protected $primaryKey = 'id';



 }