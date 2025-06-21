<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingLogModel extends Model
{
    protected $table = "billing_payment_logs";
    protected $fillable =[
        'billing_id',
        'paid_amount',
        'payment_date',
        'mode_of_payment',
        'balanced_amount',
        'remarks',
        'created_at',
        'settle_amount'
    ];
}
