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

class medicalCertificate extends Model
{
    protected $table = "docexa_medical_certificate";

}