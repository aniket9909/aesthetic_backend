<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Log;

use App\Http\Controllers\Controller;

use Carbon\Carbon;

class MedicalCertificateTemplateModel extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'medical_certificate_template';
    protected $primaryKey = 'id';


}