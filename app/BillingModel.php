<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Log;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

Class BillingModel extends Model{
/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'billing';
    protected $primaryKey = 'id';
}