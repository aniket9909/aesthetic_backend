<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class PrescriptionItemsTemplate extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'prescription_items_template';
    protected $primaryKey = 'id';
}
