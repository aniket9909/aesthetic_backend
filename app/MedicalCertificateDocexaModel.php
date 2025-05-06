<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use URL;
use DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

use Log;

class MedicalCertificateDocexaModel extends Model
{
    protected $table = "medical_certificate_docexa";

}