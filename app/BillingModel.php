<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Log;
use App\Http\Controllers\Controller;
use App\Models\BillingLogModel;
use Carbon\Carbon;

Class BillingModel extends Model{
/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'billing';
    protected $primaryKey = 'id';
    protected $with = ['billingLogs'];


    public function billingLogs()
    {
        return $this->hasMany(BillingLogModel::class, 'billing_id', 'id');
    }
}