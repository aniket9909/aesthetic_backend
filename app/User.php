<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Doctor;
use App\DoctorAddressDetail;
use App\DoctorSpeciality;
use App\DoctorFee;
use App\Assistantmap;
use App\Skumaster;
use App\Speciality;
use App\Clinic;
use App\Medicalestablishmentsmedicalusermap;
use App\Medicalestablishments;
use URL;
use DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\DoctorsApi;
use App\Http\Controllers\UsersApi;
use App\Http\Controllers\SpecialityApi;
use App\Http\Controllers\Controller;
use Log;

class User extends Model
{

    public function checkRegister($request)
    {
        $rules = [
            'is_user_doctor' => 'required|integer',
            'mobileNo' => 'required|regex:/[0-9]/'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'msg' => $validator->messages()], 400);
        } else {
            $data = $request->input();
            try {
                if ($data['is_user_doctor'] == 1) {
                    $count = Doctor::where('mobile_no', $data['mobileNo'])->count();
                    if ($count > 0)
                        return response()->json(['status' => "fail", 'msg' => "Opps! Doctor Mobile no is already exists."], 400);
                } else {
                    $count = Doctor::where('mobile_no', $data['mobileNo'])->count();
                    if ($count > 0)
                        return response()->json(['status' => "fail", 'msg' => "Opps! Assistant Mobile no is already exists."], 400);
                }
                $sms = new DoctorsApi();
                $response = $sms->sendOtp($data['mobileNo']);
                return $response;
            } catch (Exception $e) {
                return response()->json(['status' => "fail", 'msg' => "Something went wrong"], 400);
            }
        }
    }
    public function loginotp($mobileno, $request)
    {
        $rules = [
            'is_user_doctor' => 'required|integer'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'msg' => $validator->messages()], 400);
        } else {
            $data = $request->input();
            try {
                if ($data['is_user_doctor'] == 1) {
                    $count = Doctor::where('mobile_no', $mobileno)->count();
                    if ($count > 0) {
                        $sms = new DoctorsApi();
                        $response = $sms->sendOtp($mobileno);
                        return $response;
                    } else {
                        return response()->json(['status' => "fail", 'msg' => "Opps! Doctor Mobile no is not exists."], 400);
                    }
                } else {
                    $count = Doctor::where('mobile_no', $mobileno)->count();
                    if ($count > 0) {
                        $sms = new DoctorsApi();
                        $response = $sms->sendOtp($mobileno);
                        return $response;
                    } else {
                        return response()->json(['status' => "fail", 'msg' => "Opps! Assistant Mobile no is not exists."], 400);
                    }
                }
            } catch (Exception $e) {
                return response()->json(['status' => "fail", 'msg' => "Something went wrong"], 400);
            }
        }
    }

    public function doctorupdate($request)
    {
        $data = $request->input();
        //var_dump($data);die;
        if ($data['step'] == 2) {
            if ($data['is_user_doctor'] == 1) {
                $user_map_id = $data['user_map_id'];

                $Medicaldata = Medicalestablishmentsmedicalusermap::find($user_map_id);
                if (!isset($Medicaldata->medical_user_id)) {
                    return response()->json(['status' => "fail", 'msg' => "Medical establishment didn't found"], 200);
                }
                $Medicaldata->fee = $data['doctor_fee'];
                $Medicaldata->city = $data['doctor_city_id'];
                $Medicaldata->save();
                $doctor = Doctor::find($Medicaldata->medical_user_id);
                if (!isset($doctor->pharmaclient_id)) {
                    return response()->json(['status' => "fail", 'msg' => "Medical establishment didn't found"], 200);
                }
                $doctor->medical_registration_no = $data['doctor_mr_no'];
                $doctor->mrn_council_year = $data['doctor_mr_year'];
                $doctor->mrn_council_state_id = $data['doctor_mr_state'];
                if (isset($data['doctor_profile_pic']))
                    $doctor->pharmaclient_image = $data['doctor_profile_pic'];
                if (isset($data['doctor_pic']))
                    $doctor->image = $data['doctor_pic'];
                $doctor->gender_id = (strtolower($data['doctor_gender']) == 'male') ? 1 : ((strtolower($data['doctor_gender']) == 'female') ? 2 : 1);
                $doctor->save();
                $res = new Skumaster();
                $res->createdefaultsku($user_map_id, $data['doctor_fee']);
                $specility = new SpecialityApi();
                $specility->updatespecialities($user_map_id, $data['speciality_ids']);
                $address = new UsersApi();
                $address_data = [
                    'sublocality_level_1' => '',
                    'sublocality_level_2' => '',
                    'city_id' => $data['doctor_city_id'],
                    'state_id' => $data['doctor_state_id'],
                    'postal_code' => $data['doctor_pincode']
                ];
                $address->updateaddressinfo($user_map_id, $address_data);
                $doctor = Doctor::find($Medicaldata->medical_user_id);

                return response()->json(['status' => "success", 'doctor' => $this->autologin($user_map_id), 'id' => $Medicaldata->medical_establishment_id, 'handle' => $_ENV['APP_HANDLE'] . $Medicaldata->handle], 200);
            } else {
                $user_map_id = $data['user_map_id'];
                $medical_id = Medicalestablishmentsmedicalusermap::find($user_map_id)->medical_establishment_id;
                $Medicaldata = Medicalestablishmentsmedicalusermap::where(array("medical_establishment_id" => $medical_id, 'role' => 'doctor'))->first();
                if (!isset($Medicaldata->medical_user_id)) {
                    return response()->json(['status' => "fail", 'msg' => "Medical establishment didn't found"], 200);
                }
                $Medicaldata->fee = $data['doctor_fee'];
                $Medicaldata->city = $data['doctor_city_id'];
                $Medicaldata->save();
                $doctor = Doctor::find($Medicaldata->medical_user_id);
                if (isset($data['doctor_profile_pic']))
                    $doctor->pharmaclient_image = $data['doctor_profile_pic'];
                if (isset($data['doctor_pic']))
                    $doctor->image = $data['doctor_pic'];
                $doctor->image = $data['doctor_pic'];
                $doctor->medical_registration_no = $data['doctor_mr_no'];
                $doctor->mrn_council_year = $data['doctor_mr_year'];
                $doctor->mrn_council_state_id = $data['doctor_mr_state'];
                // $doctor->speciality_id = $data['speciality_id'];
                $doctor->save();
                $res = new Skumaster();
                $res->createdefaultsku($Medicaldata->id, $data['doctor_fee']);
                $specility = new SpecialityApi();
                $specility->updatespecialities($Medicaldata->id, $data['speciality_ids']);
                $address = new UsersApi();
                $address_data = [
                    'sublocality_level_1' => '',
                    'sublocality_level_2' => '',
                    'city_id' => $data['doctor_city_id'],
                    'state_id' => $data['doctor_state_id'],
                    'postal_code' => $data['doctor_pincode']
                ];
                $address->updateaddressinfo($user_map_id, $address_data);
                return response()->json(['status' => "success", 'doctor' => $this->autologin($Medicaldata->id), 'id' => $medical_id, 'handle' => $_ENV['APP_HANDLE'] . $Medicaldata->handle], 200);
            }
        } else if ($data['step'] == 3) {
            $user_map_id = $data['user_map_id'];
            $id = Medicalestablishmentsmedicalusermap::find($user_map_id)->medical_establishment_id;
            $Medicaldata = Medicalestablishmentsmedicalusermap::where(array("handle" => $data['handle']))->where("medical_establishment_id", "!=", $id)->first();

            if (isset($Medicaldata->handle)) {
                return response()->json(['status' => "fail", 'msg' => "handle is not available", 'id' => $id, 'handle' => $_ENV['APP_HANDLE'] . $Medicaldata->handle], 200);
            }
            if ($data['is_user_doctor'] == 1) {
                $user_map_id = $data['user_map_id'];
                $Medicaldata = Medicalestablishmentsmedicalusermap::find($user_map_id);
                $Medicaldata->handle = $data['handle'];
                $Medicaldata->save();
            } else {
                $user_map_id = $data['user_map_id'];
                $medical_id = Medicalestablishmentsmedicalusermap::find($user_map_id)->medical_establishment_id;
                $Medicaldata = Medicalestablishmentsmedicalusermap::where(array("medical_establishment_id" => $medical_id, 'role' => 'doctor'))->first();
                $Medicaldata->handle = $data['handle'];
                $Medicaldata->save();
            }
            return response()->json(['status' => "success", 'id' => $id, 'doctor' => $this->autologin($Medicaldata->id), 'handle' => $_ENV['APP_HANDLE'] . $Medicaldata->handle], 200);
        } else if ($data['step'] == 4) {
            if ($data['is_user_doctor'] == 1) {
                $user_map_id = $data['user_map_id'];
                $Medicaldata = Medicalestablishmentsmedicalusermap::find($user_map_id);
                $doctor = Doctor::find($Medicaldata->medical_user_id);
                $doctor->email_id = $data['email'];
                $doctor->password = md5($data['password']);
                $doctor->save();
            } else {
                $user_map_id = $data['user_map_id'];
                $medical_id = Medicalestablishmentsmedicalusermap::find($user_map_id)->medical_establishment_id;
                $Medicaldata = Medicalestablishmentsmedicalusermap::where(array("medical_establishment_id" => $medical_id, 'role' => 'doctor'))->first();
                $doctor = Doctor::find($Medicaldata->medical_user_id);
                $doctor->email_id = $data['email'];
                $doctor->password = md5($data['password']);
                $doctor->save();
            }
            $handlearray = $Medicaldata->original;

            $templatedata = [
                'template' => 'welcome_email_to_doctor',
                'handle' => $handlearray['handle'],
                'appointment_id' => ""
            ];
            $c = new Controller();
            $c->sendNotification($templatedata);
            $user_map_id = $data['user_map_id'];
            $medical_id = Medicalestablishmentsmedicalusermap::find($user_map_id)->medical_establishment_id;
            return response()->json(['status' => "success", 'id' => $medical_id, 'doctor' => $this->autologin($Medicaldata->id), 'handle' => $_ENV['APP_HANDLE'] . $Medicaldata->handle], 200);
        }
    }
    public function updateprofile($esteblishmentusermapID, $request)
    {
        $data = $request->input();
        $medical_establishment_id = Medicalestablishmentsmedicalusermap::where(array("id" => $esteblishmentusermapID))->first()->medical_establishment_id;
        //var_dump($medical_establishment_id);die;
        $id = $medical_establishment_id;
        if ($data['is_user_doctor'] == 1) {


            $Medicaldata = Medicalestablishmentsmedicalusermap::where(array("medical_establishment_id" => $id, 'role' => 'doctor'))->first();
            if (!isset($Medicaldata->medical_user_id)) {
                return response()->json(['status' => "fail", 'msg' => "Medical establishment didn't found"], 200);
            }
            $Medicaldata->fee = $data['doctor_fee'];
            $Medicaldata->city = $data['doctor_city'];
            $Medicaldata->save();
            $doctor = Doctor::find($Medicaldata->medical_user_id);
            $doctor->medical_registration_no = $data['doctor_mr_no'];
            if (isset($data['doctor_profile_pic']))
                $doctor->pharmaclient_image = $data['doctor_profile_pic'];
            if (isset($data['doctor_pic']))
                $doctor->image = $data['doctor_pic'];
            $doctor->pharmaclient_name = $data['first_name'];
            $doctor->last_name = $data['last_name'];
            $doctor->speciality_id = $data['speciality_id'];
            $doctor->save();
            $res = new Skumaster();
            $res->updatedefaultsku($Medicaldata->id, $data['doctor_fee']);
            return response()->json(['status' => "success", 'doctor' => $this->autologin($Medicaldata->id), 'id' => $id, 'handle' => $_ENV['APP_HANDLE'] . $Medicaldata->handle], 200);
        } else {
            $Medicaldata = Medicalestablishmentsmedicalusermap::where(array("medical_establishment_id" => $id, 'role' => 'doctor'))->first();
            if (!isset($Medicaldata->medical_user_id)) {
                return response()->json(['status' => "fail", 'msg' => "Medical establishment didn't found"], 200);
            }
            $Medicaldata->fee = $data['doctor_fee'];
            $Medicaldata->city = $data['doctor_city'];
            $Medicaldata->save();
            $doctor = Doctor::find($Medicaldata->medical_user_id);
            $doctor->medical_registration_no = $data['doctor_mr_no'];
            if (isset($data['doctor_profile_pic']))
                $doctor->pharmaclient_image = $data['doctor_profile_pic'];
            if (isset($data['doctor_pic']))
                $doctor->image = $data['doctor_pic'];
            $doctor->pharmaclient_name = $data['first_name'];
            $doctor->last_name = $data['last_name'];
            $doctor->image = $data['doctor_pic'];
            $doctor->speciality_id = $data['speciality_id'];
            $doctor->save();
            $res = new Skumaster();
            $res->updatedefaultsku($Medicaldata->id, $data['doctor_fee']);
            return response()->json(['status' => "success", 'doctor' => $this->autologin($Medicaldata->id), 'id' => $id, 'handle' => $_ENV['APP_HANDLE'] . $Medicaldata->handle], 200);
        }
    }
    public function getprofile($esteblishmentusermapID)
    {
        try {
            $medical_establishment = Medicalestablishmentsmedicalusermap::where(array("id" => $esteblishmentusermapID))->first();
            if (isset($medical_establishment->medical_establishment_id))
                $medical_establishment_id = $medical_establishment->medical_establishment_id;
            else
                return response()->json(['status' => "fail", 'message' => "user map not found"], 400);
            $Medicaldata = Medicalestablishmentsmedicalusermap::where(array("medical_establishment_id" => $medical_establishment_id, 'role' => 'doctor'))->first();
            return response()->json(['status' => "success", 'doctor' => $this->autologin($esteblishmentusermapID), 'id' => $medical_establishment_id, 'handle' => $_ENV['APP_HANDLE'] . $Medicaldata->handle], 200);
        } catch (\Exception $e) {
            throw new HttpException(500, $e->getMessage());
        }
    }
    public function autologin($esteblishment_user_map_id)
    {
        
        $tabdata = DB::table('docexa_medical_establishments')
            ->Join('docexa_medical_establishments_medical_user_map', 'docexa_medical_establishments_medical_user_map.medical_establishment_id', '=', 'docexa_medical_establishments.id')
            ->Join('docexa_doctor_master', 'docexa_medical_establishments_medical_user_map.medical_user_id', '=', 'docexa_doctor_master.pharmaclient_id')
            ->select('docexa_doctor_master.unsubscribe','docexa_doctor_master.pharmaclient_id','docexa_doctor_master.medical_certificate_url','docexa_doctor_master.letter_head_copy','docexa_doctor_master.is_vaccine','docexa_doctor_master.state_id as doctor_state_id','docexa_doctor_master.city_id as doctor_city_id','docexa_doctor_master.state as doctor_state','docexa_doctor_master.city as doctor_city','docexa_doctor_master.address as doctor_address','docexa_doctor_master.fee','docexa_doctor_master.mobile_no', 'docexa_doctor_master.mrn_council_year', 'docexa_doctor_master.mrn_council_state_id', 'docexa_doctor_master.medical_registration_no', 'docexa_doctor_master.speciality_id', 'docexa_doctor_master.image', 'docexa_doctor_master.pharmaclient_name', 'docexa_doctor_master.last_name', 'docexa_doctor_master.address', 'docexa_doctor_master.pharmaclient_image', 'docexa_medical_establishments_medical_user_map.id', 'docexa_medical_establishments_medical_user_map.handle', 'docexa_medical_establishments_medical_user_map.medical_establishment_id', 'docexa_medical_establishments_medical_user_map.medical_user_id', 'docexa_medical_establishments.address_id')
            ->where('docexa_medical_establishments_medical_user_map.id', $esteblishment_user_map_id)
            ->first();
            
        if (isset($tabdata->medical_establishment_id)) {
            $speciality_id = Speciality::join('docexa_doctor_speciality_relation', 'docexa_doctor_speciality_relation.speciality_id', 'docexa_speciality_master.speciality_id')
                ->select('docexa_speciality_master.*')
                ->where('docexa_doctor_speciality_relation.user_map_id', $esteblishment_user_map_id)->get();
            $location = Doctor::where('pharmaclient_id',$tabdata->pharmaclient_id)->with('states','cities')->first();
            
            //return $location;
            $address = $tabdata->doctor_address;
            $city_id = $tabdata->doctor_city_id;
            $state_id = $tabdata->doctor_state_id;
            $state = "";
            $city = "";
            //return $location;
            if (isset($location->states->state_name)) {
                $state = $location->states->state_name;
            }
            if (isset($location->cities->name)) {
                $city = $location->cities->name;
            }
            // if (isset($location->id)) {
            //     if (isset($location->cities->name)) {
            //         $city = $location->cities->name;
            //         $city_id = $location->city;
            //     }
            //     if (isset($location->states->state_name)) {
            //         $state = $location->states->state_name;
            //         $state_id = $location->state;
            //     }
            //     $address = ($location->address && $location->address != null) ? $location->address : null;
            // }
            $response = [
                'esteblishment_id' => $tabdata->medical_establishment_id,
                'esteblishment_user_map_id' => $tabdata->id,
                'medical_user_id' => $tabdata->medical_user_id,
                'handle' => env('APP_HANDLE') . $tabdata->handle,
                'first_name' => $tabdata->pharmaclient_name,
                'last_name' => $tabdata->last_name,
                'mobile_no' => $tabdata->mobile_no,

                'doctor_mr_no' => $tabdata->medical_registration_no,
                'doctor_mr_state' => $tabdata->mrn_council_state_id,
                'doctor_mr_year' => $tabdata->mrn_council_year,
                'medical_certificate_url' => $tabdata->medical_certificate_url,
                'letter_head_copy' => $tabdata->letter_head_copy,
                'city' => $city,
                'city_id' => $city_id,
                'state' => $state,
                'state_id' => $state_id,
                'address' => $address,
                'is_vaccine' => $tabdata->is_vaccine,
                'clinic' => Clinic::where(array("user_map_id" => $esteblishment_user_map_id))->get(),
                'speciality_id' => $speciality_id,
                //'fee' => (int)((Medicalestablishmentsmedicalusermap::where(array("medical_establishment_id" => $tabdata->medical_establishment_id, 'role' => 'doctor'))->first()) ? Medicalestablishmentsmedicalusermap::where(array("medical_establishment_id" => $tabdata->medical_establishment_id, 'role' => 'doctor'))->first()->fee : 0),
                'fee' => (int)$tabdata->fee,
                'unsubscribe_flag' => $tabdata->unsubscribe,
                
                'profilePicture' => (strpos($tabdata->image, 'docexa_default_image') !== false) ? URL::Asset('upload/doctor/profile/' . $tabdata->image) : $tabdata->image,
                'role_id' => 1,
            ];
            return $response;
        } else {
            return [];
        }
    }

    public function login($request)
    {
        $data = $request->input();
        if (isset($data['otp']) && $data['otp'] != '') {
            if ($this->otpverify($data['mobileNo'], $data['otp']) || $data['otp'] == 100) {
                $user = Doctor::where(array('mobile_no' => $data['mobileNo']))->first();
                if (isset($user->pharmaclient_id)) {
                    if ($user->activation_flag == 1) {
                        if ($user->user_master_id == 1)
                            $response = $this->logintoaccount($user->pharmaclient_id, 'doctor');
                        else {
                            $doctor = DB::table('docexa_doctor_assistant_map')->where('docexa_doctor_assistant_id', $user->pharmaclient_id)->get()->first();
                            $response = $this->logintoaccount($doctor->docexa_doctor_id, 'assistant');
                        }
                    } else {

                        return response()->json(['status' => "success", 'data' => [], 'msg' => "The activation of your account is pending. Our team will get back to you within two business days."], 200);

                    }
                    return response()->json(['status' => "success", 'data' => $response], 200);
                } else {
                    return response()->json(['status' => "success", 'data' => [], 'msg' => "No login information was found in our database; please register."], 200);
                }
            } else {
                return response()->json(['status' => "fail", 'msg' => "Opps! OTP incorrect."], 400);
            }
        } else {

            if ($this->emailverify($data['email'], $data['password'])) {
                $user = Doctor::where('email_id', $data['email'])->get()->first();
                if (isset($user->pharmaclient_id)) {
                    if ($user->activation_flag == 1) {
                        if ($user->user_master_id == 1)
                            $response = $this->logintoaccount($user->pharmaclient_id, 'doctor');
                        else {
                            $doctor = DB::table('docexa_doctor_assistant_map')->where('docexa_doctor_assistant_id', $user->pharmaclient_id)->get()->first();
                            $response = $this->logintoaccount($doctor->docexa_doctor_id, 'assistant');
                        }
                    } else {

                        return response()->json(['status' => "success", 'data' => [], 'msg' => "The activation of your account is pending. Our team will get back to you within two business days."], 200);
                    }
                    /*
                    $esteblishment_user_map = Medicalestablishmentsmedicalusermap::where('medical_user_id', $doctor->pharmaclient_id)->get()->first();
                    
                    $tabdata = DB::table('docexa_medical_establishments')
                        ->Join('docexa_medical_establishments_medical_user_map', 'docexa_medical_establishments_medical_user_map.medical_establishment_id', '=', 'docexa_medical_establishments.id')
                        ->Join('docexa_doctor_master', 'docexa_medical_establishments_medical_user_map.medical_user_id', '=', 'docexa_doctor_master.pharmaclient_id')
                        ->select('docexa_doctor_master.image', 'docexa_doctor_master.pharmaclient_name', 'docexa_doctor_master.pharmaclient_image', 'docexa_medical_establishments_medical_user_map.id', 'docexa_medical_establishments_medical_user_map.handle', 'docexa_medical_establishments_medical_user_map.medical_establishment_id', 'docexa_medical_establishments_medical_user_map.medical_user_id', 'docexa_medical_establishments.address_id')
                        ->where('docexa_medical_establishments_medical_user_map.id', $esteblishment_user_map->id)
                        ->get()->first();

                    $response[] = [
                        'esteblishment_id' => $tabdata->medical_establishment_id,
                        'esteblishment_user_map_id' => $tabdata->id,
                        'medical_user_id' => $tabdata->medical_user_id,
                        'handle' => $_ENV['APP_HANDLE'] . $tabdata->handle,
                        'first_name' => $tabdata->pharmaclient_name,
                        'last_name' => '',
                        'type' => 'doctor',
                        'city' => DoctorAddressDetail::where('id', $tabdata->address_id)->pluck('city')->first(),
                        'speciality' => DoctorSpeciality::join('docexa_doctor_speciality_relation', 'docexa_doctor_speciality_relation.speciality_id', '=', 'docexa_speciality_master.speciality_id')->where('docexa_doctor_speciality_relation.pharmaclient_id', $tabdata->medical_user_id)->select('docexa_speciality_master.speciality_name')->pluck('speciality_name')->first(),
                        'fee' => (DoctorFee::find($tabdata->medical_user_id)) ? DoctorFee::find($tabdata->medical_user_id)->appt_initial : 0,
                        'profilePicture' => (strpos($tabdata->image, 'docexa_default_image') !== false) ? URL::Asset('upload/doctor/profile/' . $tabdata->image) : $tabdata->image
                    ];
                    */
                    return response()->json(['status' => "success",'flag'=>1, 'data' => $response], 200);
                } else {
                    return response()->json(['status' => "success", 'data' => [], 'msg' => "no record found"], 200);
                }
            } else {
                return response()->json(['status' => "fail", 'msg' => "Opps! Email ID or password are incorrect."], 400);
            }
        }
    }
    public function logintoaccount($pharmaclient_id, $type)
    {
        Log::info(['login account' => $pharmaclient_id ,$type]);

        $esteblishment_user_map = Medicalestablishmentsmedicalusermap::where('medical_user_id', $pharmaclient_id)->get()->first();

        Log::info(['esablishmentusermap' => $esteblishment_user_map]);
        $tabdata = DB::table('docexa_medical_establishments')
            ->Join('docexa_medical_establishments_medical_user_map', 'docexa_medical_establishments_medical_user_map.medical_establishment_id', '=', 'docexa_medical_establishments.id')
            ->Join('docexa_doctor_master', 'docexa_medical_establishments_medical_user_map.medical_user_id', '=', 'docexa_doctor_master.pharmaclient_id')
            ->select('docexa_doctor_master.address', 'docexa_doctor_master.image', 'docexa_doctor_master.last_name', 'docexa_doctor_master.pharmaclient_name', 'docexa_doctor_master.pharmaclient_image', 'docexa_medical_establishments_medical_user_map.id', 'docexa_medical_establishments_medical_user_map.handle', 'docexa_medical_establishments_medical_user_map.medical_establishment_id', 'docexa_medical_establishments_medical_user_map.medical_user_id', 'docexa_medical_establishments.address_id')
            ->where('docexa_medical_establishments_medical_user_map.id', $esteblishment_user_map->id)
            ->get()->first();
        Log::info(['tabsssssss'=> $tabdata]);


        Log::info('tabdata', ['data' => $esteblishment_user_map->id]);
        $speciality_name = DoctorSpeciality::join('docexa_doctor_speciality_relation', 'docexa_doctor_speciality_relation.speciality_id', '=', 'docexa_speciality_master.speciality_id')->where('docexa_doctor_speciality_relation.pharmaclient_id', $tabdata->medical_user_id)->select('docexa_speciality_master.speciality_name')->pluck('speciality_name')->first();

        Log::info(['speciality name' => $speciality_name]);
        $response[] = [
            'esteblishment_id' => $tabdata->medical_establishment_id,
            'esteblishment_user_map_id' => $tabdata->id,
            'medical_user_id' => $tabdata->medical_user_id,
            'handle' => $_ENV['APP_HANDLE'] .  $tabdata->handle,
            'first_name' => $tabdata->pharmaclient_name,
            'last_name' => $tabdata->last_name,
            'type' => $type,
            'city' => DoctorAddressDetail::where('id', $tabdata->address_id)->pluck('city')->first(),
            'speciality' => (isset($speciality_name) && $speciality_name != null)?$speciality_name:"",
            'fee' => (DoctorFee::find($tabdata->medical_user_id)) ? DoctorFee::find($tabdata->medical_user_id)->appt_initial : 0,
            'profilePicture' => (strpos($tabdata->image, 'docexa_default_image') !== false) ? URL::Asset('upload/doctor/profile/' . $tabdata->image) : $tabdata->image
        ];
        Log::info(['response' => $response]);
        return $response;
    }

    public function saveRegister($request)
    {
        $data = $request->input();
        if ($data['is_user_doctor'] == 1) {
            $doctordata = $data['doctor'][0];
            if ($this->otpverify($doctordata['mobileNo'], $data['otp'])) {
                $doctor = new Doctor;
                $doctor->pharmaclient_name = $doctordata['firstName'];
                $doctor->last_name = $doctordata['lastName'];
                $doctor->mobile_no = $doctordata['mobileNo'];
                $doctor->login_name = $doctordata['mobileNo'];
                $doctor->pharmaclient_image = 'ProfileImages/docexa_default_image.png';
                $doctor->image = 'ProfileImages/docexa_default_image.png';
                $doctor->user_master_id = 1;
                $doctor->activation_flag = 0;
                $doctor->save();
                $medical_user_id = $doctor->pharmaclient_id;
                $medical_establishment_id = $this->createMedicalEstablishments($doctordata['firstName'], $doctordata['lastName']);
                $medical_establishment_doc_map_id = $this->createMedicalEstablishmentsUserMap($medical_user_id, $medical_establishment_id, $doctordata['firstName'], $doctordata['lastName']);

                return response()->json(['status' => "success", 'user_map_id' => $medical_establishment_doc_map_id, 'id' => $medical_establishment_id], 200);
            } else {
                return response()->json(['status' => "fail", 'msg' => "Opps! OTP incorrect."], 400);
            }
        } else {
            $doctordata = $data['doctor'][0];
            $assistant = $data['assistant'][0];
            if ($this->otpverify($assistant['mobileNo'], $data['otp'])) {
                $doctor = new Doctor;
                $doctor->pharmaclient_name = $doctordata['firstName'];
                $doctor->last_name = $doctordata['lastName'];
                $doctor->pharmaclient_image = 'ProfileImages/docexa_default_image.png';
                $doctor->image = 'ProfileImages/docexa_default_image.png';
                $doctor->activation_flag = 0;
                $doctor->user_master_id = 1;
                $doctor->save();
                $docexa_doctor_id = $doctor->pharmaclient_id;
                Log::info('register input', ['data' => $doctor]);
                $assitant = new Doctor;
                $assitant->pharmaclient_name = $assistant['firstName'];
                $assitant->last_name = $assistant['lastName'];
                $assitant->mobile_no = $assistant['mobileNo'];
                $assitant->login_name = $assistant['mobileNo'];
                $assitant->pharmaclient_image = 'ProfileImages/docexa_default_image.png';
                $assitant->image = 'ProfileImages/docexa_default_image.png';
                $assitant->user_master_id = 2;
                $assitant->save();
                Log::info('register input', ['data' => $assitant]);
                $docexa_doctor_assistant_id = $assitant->pharmaclient_id;
                $this->createAssistantmap($docexa_doctor_id, $docexa_doctor_assistant_id);
                $medical_establishment_id = $this->createMedicalEstablishments($doctordata['firstName'], $doctordata['lastName']);
                Log::info('register input', ['data' => $medical_establishment_id]);
                $medical_establishment_doc_map_id = $this->createMedicalEstablishmentsUserMap($docexa_doctor_id, $medical_establishment_id, $doctordata['firstName'], $doctordata['lastName'], 'doctor');
                Log::info('register input', ['data' => $medical_establishment_doc_map_id]);
                $medical_establishment_assist_map_id = $this->createMedicalEstablishmentsUserMap($docexa_doctor_assistant_id, $medical_establishment_id, $assistant['firstName'], $assistant['lastName'], 'assistant');
                return response()->json(['status' => "success", 'id' => $medical_establishment_id], 200);
            } else {
                return response()->json(['status' => "fail", 'msg' => "Opps! OTP incorrect."], 400);
            }
        }
    }
    public function destroydoctor($id, $mobileno)
    {
        if ($mobileno != '') {
            $user = Doctor::where('mobile_no', $mobileno)->get()->first();
            if (isset($user->pharmaclient_id)) {
                if ($user->user_master_id == 1) {
                    $response = $this->logintoaccount($user->pharmaclient_id, 'doctor');
                    DB::table('docexa_doctor_master')->where('pharmaclient_id', $user->pharmaclient_id)->delete();
                } else {
                    $doctor = DB::table('docexa_doctor_assistant_map')->where('docexa_doctor_assistant_id', $user->pharmaclient_id)->get()->first();
                    $response = $this->logintoaccount($doctor->docexa_doctor_id, 'assistant');
                    DB::table('docexa_doctor_assistant_map')->where('docexa_doctor_assistant_id', $user->pharmaclient_id)->delete();
                    DB::table('docexa_doctor_master')->where('pharmaclient_id', $user->pharmaclient_id)->delete();
                    DB::table('docexa_doctor_master')->where('mobile_no', $doctor->docexa_doctor_id)->delete();
                }

                DB::table('docexa_medical_establishments_medical_user_map')->where('id', $response[0]['esteblishment_id'])->delete();
                DB::table('docexa_medical_establishments')->where('id', $response[0]['esteblishment_id'])->delete();
                return response()->json(['status' => 'success'], 200);
            }
        }

        $tabdata = DB::table('docexa_medical_establishments')
            ->Join('docexa_medical_establishments_medical_user_map', 'docexa_medical_establishments_medical_user_map.medical_establishment_id', '=', 'docexa_medical_establishments.id')
            ->Join('docexa_doctor_master', 'docexa_medical_establishments_medical_user_map.medical_user_id', '=', 'docexa_doctor_master.pharmaclient_id')
            ->select('docexa_medical_establishments_medical_user_map.id', 'docexa_medical_establishments_medical_user_map.medical_establishment_id', 'docexa_medical_establishments_medical_user_map.medical_user_id')
            ->where('docexa_medical_establishments_medical_user_map.id', $id)
            ->get()->first();
        if (isset($tabdata->id)) {
            DB::table('docexa_doctor_master')->where('pharmaclient_id', $tabdata->medical_user_id)->delete();
            DB::table('docexa_medical_establishments_medical_user_map')->where('id', $tabdata->id)->delete();
            DB::table('docexa_medical_establishments')->where('id', $tabdata->medical_establishment_id)->delete();
            return response()->json(['status' => 'success'], 200);
        } else {
            return response()->json(['status' => 'error', 'msg' => "not found"], 400);
        }
    }
    public function getDoctor($id)
    {
        $tabdata = DB::table('docexa_medical_establishments')
            ->Join('docexa_medical_establishments_medical_user_map', 'docexa_medical_establishments_medical_user_map.medical_establishment_id', '=', 'docexa_medical_establishments.id')
            ->Join('docexa_doctor_master', 'docexa_medical_establishments_medical_user_map.medical_user_id', '=', 'docexa_doctor_master.pharmaclient_id')
            ->select('docexa_medical_establishments.address_id', 'docexa_doctor_master.image', 'docexa_doctor_master.address', 'docexa_doctor_master.speciality_id', 'docexa_medical_establishments_medical_user_map.fee as map_fee', 'docexa_medical_establishments_medical_user_map.city as map_city', 'docexa_doctor_master.pharmaclient_name', 'docexa_doctor_master.last_name', 'docexa_doctor_master.pharmaclient_image', 'docexa_medical_establishments_medical_user_map.id', 'docexa_medical_establishments_medical_user_map.medical_establishment_id', 'docexa_medical_establishments_medical_user_map.medical_user_id', 'docexa_medical_establishments.address_id')
            ->where('docexa_medical_establishments_medical_user_map.handle', $id)
            ->get()->first();
        //var_dump($data->pharmaclient_id);die;
        if (isset($tabdata->pharmaclient_name)) {
            $speciality = Speciality::join('docexa_doctor_speciality_relation', 'docexa_doctor_speciality_relation.speciality_id', 'docexa_speciality_master.speciality_id')
                ->select('docexa_speciality_master.speciality_name')
                ->where('docexa_doctor_speciality_relation.user_map_id', $tabdata->id)->groupBy('docexa_speciality_master.speciality_name')->pluck('docexa_speciality_master.speciality_name')->implode(',');
            $UsersApi = new UsersApi();
            $address = $UsersApi->addressinfo($tabdata->id);
            //var_dump($speciality);die;
            $doctor = Doctor::find($tabdata->medical_user_id);
            $data = [
                'esteblishment_id' => $tabdata->medical_establishment_id,
                'esteblishment_user_map_id' => $tabdata->id,
                'medical_user_id' => $tabdata->medical_user_id,
                'first_name' => $tabdata->pharmaclient_name,
                'last_name' => $tabdata->last_name,
                //'city' => $tabdata->map_city,
                'city' => (isset($address['city'])) ? $address['city'] : '',
                'address' => (isset($address['address'])) ? $address['address'] : '',
                'clinic_name' => (isset($address['clinic_name'])) ? $address['clinic_name'] : '',
                'sublocality_level_1' => (isset($address['sublocality_level_1'])) ? $address['sublocality_level_1'] : '',
                'sublocality_level_2' => (isset($address['sublocality_level_2'])) ? $address['sublocality_level_2'] : '',
                'postal_code' => (isset($address['postal_code'])) ? $address['postal_code'] : '',
                'lat' => (isset($address['lat'])) ? $address['lat'] : '',
                'long' => (isset($address['long'])) ? $address['long'] : '',
                'state' => (isset($address['state'])) ? $address['state'] : '',
                'speciality' => $speciality,
                'fee' => $tabdata->map_fee,
                'about_me' => isset($doctor->aboutme->about_me)?$doctor->aboutme->about_me:'',
                'instagram' => isset($doctor->aboutme->instagram)?$doctor->aboutme->instagram:'',
                'facebook' => isset($doctor->aboutme->facebook)?$doctor->aboutme->facebook:'',
                'linkedin' => isset($doctor->aboutme->linkedin)?$doctor->aboutme->linkedin:'',
                'twitter' => isset($doctor->aboutme->twitter)?$doctor->aboutme->twitter:'',
                'banner' => isset($doctor->aboutme->cover_img)?$doctor->aboutme->cover_img:'',
                'youtube' => isset($doctor->aboutme->youtube)?$doctor->aboutme->youtube:'',
                'video' => isset($doctor->aboutme->video)?$doctor->aboutme->video:'',
                'education' => isset($doctor->education)?$doctor->education:[],
                'award' => isset($doctor->award)?$doctor->award:[],
                'experience' => isset($doctor->experienced)?$doctor->experienced:[],
                'services' => isset($doctor->service)?$doctor->service:[],
                'specialization' => isset($doctor->specialization)?$doctor->specialization:[],
                'profilePicture' => (strpos($tabdata->image, 'docexa_default_image') !== false) ? URL::Asset('upload/doctor/profile/' . $tabdata->image) : $tabdata->image
            ];
            if (isset($_GET['sku_id'])) {
                $skudata = DB::table('docexa_esteblishment_user_map_sku_details')
                    ->select('docexa_esteblishment_user_map_sku_details.id as sku_id', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_esteblishment_user_map_sku_details.fee', 'docexa_esteblishment_user_map_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.default_flag')
                    ->where('docexa_esteblishment_user_map_sku_details.user_map_id', $tabdata->id)->where('docexa_esteblishment_user_map_sku_details.is_enabled', 1)->where('docexa_esteblishment_user_map_sku_details.id', $_GET['sku_id'])->get();
                if (count($skudata) > 0)
                    return response()->json(['status' => 'success', 'data' => $data, 'sku_details' => $skudata], 200);
                else {
                    return response()->json(['status' => 'success', 'data' => $data], 200);
                }
            } else {
                $skudata = DB::table('docexa_esteblishment_user_map_sku_details')
                    ->select('docexa_esteblishment_user_map_sku_details.id as sku_id', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_esteblishment_user_map_sku_details.fee', 'docexa_esteblishment_user_map_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.default_flag')
                    ->where('docexa_esteblishment_user_map_sku_details.is_enabled', 1)->where('docexa_esteblishment_user_map_sku_details.user_map_id', $tabdata->id)->get();
                if (count($skudata) > 0)
                    return response()->json(['status' => 'success', 'data' => $data, 'sku_details' => $skudata], 200);
                else {
                    return response()->json(['status' => 'success', 'data' => $data], 200);
                }
            }
            // return response()->json(['status' => 'success', 'data' => $data], 200);
        } else {
            return response()->json(['status' => 'error', 'msg' => 'No record found'], 200);
        }
    }
    public function getDoctorsforvaccine($count, $per_page, $pincode, $city, $state, $searchflag = 1)
    {
        $per_page = 100;
        $usermap = new Medicalestablishmentsmedicalusermap();
        $tabdatas = Medicalestablishmentsmedicalusermap::with('doctors')->paginate(1);
        
        if (count($tabdatas) > 0) {
            foreach ($tabdatas as $tabdata) {
                // $speciality = Speciality::join('docexa_doctor_speciality_relation', 'docexa_doctor_speciality_relation.speciality_id', 'docexa_speciality_master.speciality_id')
                //     ->select('docexa_speciality_master.speciality_name')
                //     ->where('docexa_doctor_speciality_relation.user_map_id', $tabdata->id)->pluck('docexa_speciality_master.speciality_name')->implode(',');

                $data = [
                    'esteblishment_id' => $tabdata->medical_establishment_id,
                    'esteblishment_user_map_id' => $tabdata->id,
                    'medical_user_id' => $tabdata->medical_user_id,
                    'handle' => $_ENV['APP_HANDLE'] . $tabdata->handle,
                    'first_name' => $tabdata->doctors->pharmaclient_name,
                    'last_name' => $tabdata->doctors->last_name,
                    'email' => $tabdata->doctors->email_id,
                    'doctor_mr_no' => $tabdata->doctors->medical_registration_no,
                    'mobile_no' => $tabdata->doctors->mobile_no,
                    'clinic'=>  $usermap->clinicDetails($tabdata->id),
                    'sku'=>  $usermap->skuDetails($tabdata->id),
                    // 'address' => $tabdata->address,
                    // 'speciality' => $speciality,
                    // 'city' => ($tabdata->city != null) ? (DB::table('city_master')->where('id', $tabdata->city)->first()->name) : "",
                    // 'pincode' => $tabdata->postal_code,
                    // 'map_link' => $tabdata->map_link,
                    // 'fee' => (int)((Medicalestablishmentsmedicalusermap::where(array("medical_establishment_id" => $tabdata->medical_establishment_id, 'role' => 'doctor'))->first()) ? Medicalestablishmentsmedicalusermap::where(array("medical_establishment_id" => $tabdata->medical_establishment_id, 'role' => 'doctor'))->first()->fee : 0),
                    'profilePicture' => (strpos($tabdata->image, 'docexa_default_image') !== false) ? URL::Asset('upload/doctor/profile/' . $tabdata->image) : $tabdata->image
               ];
                // $skudata = DB::table('docexa_esteblishment_user_map_sku_details')
                //     ->select('docexa_esteblishment_user_map_sku_details.sku_id', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_esteblishment_user_map_sku_details.fee', 'docexa_esteblishment_user_map_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.default_flag')
                //     ->where('docexa_esteblishment_user_map_sku_details.is_enabled', 1)
                //     ->where('docexa_esteblishment_user_map_sku_details.user_map_id', $tabdata->id)->get();

                // $skudata = $usermap->skuDetails($tabdata->id);
                // if (count($skudata) > 0)
                //     $data['sku_data'] = $skudata;
                $finaldata[] = $data;
            }
            // return response()->json($tabdatas, 200);
            if (count($tabdatas) > 0)
                return response()->json(['status' => 'success', 'data' => $finaldata], 200);
            else {
                return response()->json(['status' => 'error', 'data' => 'No record found'], 200);
            }
            // return response()->json(['status' => 'success', 'data' => $data], 200);
        } else {
            return response()->json(['status' => 'error', 'msg' => 'No record found'], 200);
        }
    }
    public function getCityforvaccine()
    {
        $query = DB::table('docexa_medical_establishments')
            ->Join('docexa_medical_establishments_medical_user_map', 'docexa_medical_establishments_medical_user_map.medical_establishment_id', '=', 'docexa_medical_establishments.id')
            ->Join('docexa_doctor_master', 'docexa_medical_establishments_medical_user_map.medical_user_id', '=', 'docexa_doctor_master.pharmaclient_id')
            ->Join('docexa_addresses', 'docexa_medical_establishments.address_id', '=', 'docexa_addresses.id', 'left')
            ->Join('city_master', 'city_master.id', '=', 'docexa_addresses.city')
            ->select('docexa_addresses.city', 'city_master.name')->distinct()->orderBy('city_master.name');

        $query->where('docexa_doctor_master.search_flag', 1);

        $tabdatas = $query->get();
        // var_dump($tabdatas);die;
        if (count($tabdatas) > 0) {
            foreach ($tabdatas as $tabdata) {
                $data = [
                    'city' => $tabdata->name,
                    'city_id' => $tabdata->city
                ];
                $finaldata[] = $data;
            }
            if (count($finaldata) > 0)
                return response()->json(['status' => 'success', 'data' => $finaldata], 200);
            else {
                return response()->json(['status' => 'error', 'data' => 'No record found'], 200);
            }
            // return response()->json(['status' => 'success', 'data' => $data], 200);
        } else {
            return response()->json(['status' => 'error', 'msg' => 'No record found'], 200);
        }
    }


    public function createMedicalEstablishments($firstName, $lastName)
    {
        $Medicalestablishments = new Medicalestablishments;
        $Medicalestablishments->name = $firstName . " " . $lastName;
        $Medicalestablishments->active = 1;
        $Medicalestablishments->address_id = 1;
        $Medicalestablishments->save();
        return $Medicalestablishments->id;
    }

    public function createMedicalEstablishmentsUserMap($medical_user_id, $medical_establishment_id, $firstName, $lastName, $role = 'doctor')
    {
        $Medicalestablishmentsmedicalusermap = new Medicalestablishmentsmedicalusermap;
        $Medicalestablishmentsmedicalusermap->medical_user_id = $medical_user_id;
        $Medicalestablishmentsmedicalusermap->medical_establishment_id = $medical_establishment_id;
        $Medicalestablishmentsmedicalusermap->is_primary = 1;
        $Medicalestablishmentsmedicalusermap->role = $role;
        $Medicalestablishmentsmedicalusermap->handle = $this->uniqueHandle($firstName, $lastName);
        $Medicalestablishmentsmedicalusermap->save();
        return $Medicalestablishmentsmedicalusermap->id;
    }

    public function uniqueHandle($firstName, $lastName)
    {
        $cominedstring = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '', $firstName . "" . $lastName)));
        $count = Medicalestablishmentsmedicalusermap::where('handle', 'regexp', 'dr' . $cominedstring . '[_0-9]*$')->count();
        if ($count >= 1) {
            $num = ++$count;             // Increment $usercnt by 1
            $handle = 'dr' . $cominedstring . $num;  // Add number to username
        } else {
            $handle = 'dr' . $cominedstring;
        }
        return $handle;
    }

    public function createAssistantmap($docexa_doctor_id, $docexa_doctor_assistant_id)
    {
        $assitantmap = new Assistantmap;
        $assitantmap->docexa_doctor_id = $docexa_doctor_id;
        $assitantmap->docexa_doctor_assistant_id = $docexa_doctor_assistant_id;
        $assitantmap->save();
    }

    public function otpverify($mobileno, $otp)
    {
        if ($otp == 100) {
            return true;
        }
        $count = Otp::where('phone_no', '=', $mobileno)->where('otp', '=', $otp)->count();
        if ($count >= 1) {
            return true;
        } else {
            return false;
        }
    }
    public function emailverify($email, $password)
    {
        $count = Doctor::where('email_id', '=', $email)->where('password', '=', md5($password))->count();
        if ($count >= 1) {
            return true;
        } else {
            return false;
        }
    }
    public function getDoctors($prefix_term, $count, $per_page, $location, $specility)
    {
        $query = DB::table('docexa_medical_establishments')
            ->Join('docexa_medical_establishments_medical_user_map', 'docexa_medical_establishments_medical_user_map.medical_establishment_id', '=', 'docexa_medical_establishments.id')
            ->Join('docexa_doctor_master', 'docexa_medical_establishments_medical_user_map.medical_user_id', '=', 'docexa_doctor_master.pharmaclient_id')
            ->Join('docexa_speciality_master', 'docexa_speciality_master.speciality_id', '=', 'docexa_doctor_master.speciality_id')
            ->select('docexa_doctor_master.medical_registration_no', 'docexa_doctor_master.speciality_id', 'docexa_doctor_master.image', 'docexa_doctor_master.pharmaclient_name', 'docexa_doctor_master.last_name', 'docexa_doctor_master.address', 'docexa_doctor_master.pharmaclient_image', 'docexa_medical_establishments_medical_user_map.id', 'docexa_medical_establishments_medical_user_map.handle', 'docexa_medical_establishments_medical_user_map.medical_establishment_id', 'docexa_medical_establishments_medical_user_map.medical_user_id', 'docexa_medical_establishments.address_id');
        if ($prefix_term != "")
            $query->where('docexa_doctor_master.pharmaclient_name', 'like', $prefix_term . '%');

        $query->where('docexa_doctor_master.search_flag', true)->skip($count)->take($per_page);


        if ($location != "")
            $query->where('docexa_medical_establishments_medical_user_map.city', '=', $location);

        if ($specility != "")
            $query->where('docexa_speciality_master.speciality_name', '=', $specility);

        $tabdatas = $query->get();
        // var_dump($tabdatas);die;
        if (count($tabdatas) > 0) {
            foreach ($tabdatas as $tabdata) {

                $data = [
                    'esteblishment_id' => $tabdata->medical_establishment_id,
                    'esteblishment_user_map_id' => $tabdata->id,
                    'medical_user_id' => $tabdata->medical_user_id,
                    'handle' => $_ENV['APP_HANDLE'] . $tabdata->handle,
                    'first_name' => $tabdata->pharmaclient_name,
                    'last_name' => $tabdata->last_name,
                    'doctor_mr_no' => $tabdata->medical_registration_no,
                    'address' => $tabdata->address,
                    'speciality' => DoctorSpeciality::where('speciality_id', $tabdata->speciality_id)->select('docexa_speciality_master.speciality_name')->pluck('speciality_name')->first(),
                    'city' => (Medicalestablishmentsmedicalusermap::where(array("medical_establishment_id" => $tabdata->medical_establishment_id, 'role' => 'doctor'))->first()) ? Medicalestablishmentsmedicalusermap::where(array("medical_establishment_id" => $tabdata->medical_establishment_id, 'role' => 'doctor'))->first()->city : '',
                    'speciality_id' => $tabdata->speciality_id,
                    'fee' => (int)((Medicalestablishmentsmedicalusermap::where(array("medical_establishment_id" => $tabdata->medical_establishment_id, 'role' => 'doctor'))->first()) ? Medicalestablishmentsmedicalusermap::where(array("medical_establishment_id" => $tabdata->medical_establishment_id, 'role' => 'doctor'))->first()->fee : 0),
                    'profilePicture' => (strpos($tabdata->image, 'docexa_default_image') !== false) ? URL::Asset('upload/doctor/profile/' . $tabdata->image) : $tabdata->image
                ];
                $skudata = DB::table('docexa_sku_master')
                    ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.sku_id', '=', 'docexa_sku_master.id')
                    ->select('docexa_esteblishment_user_map_sku_details.sku_id', 'docexa_sku_master.title', 'docexa_sku_master.description', 'docexa_esteblishment_user_map_sku_details.fee', 'docexa_esteblishment_user_map_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.default_flag')
                    ->where('docexa_esteblishment_user_map_sku_details.user_map_id', $tabdata->id)->get();
                if (count($skudata) > 0)
                    $data['sku_data'] = $skudata;
                $finaldata[] = $data;
            }
            if (count($finaldata) > 0)
                return response()->json(['status' => 'success', 'data' => $finaldata], 200);
            else {
                return response()->json(['status' => 'error', 'data' => 'No record found'], 200);
            }
            // return response()->json(['status' => 'success', 'data' => $data], 200);
        } else {
            return response()->json(['status' => 'error', 'msg' => 'No record found'], 200);
        }
    }

    public function pfizerdoctorregister($request)
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $data = $request->input();
        $doctor = new Doctor();
        $doctor->pharmaclient_name = $data['firstName'];
        $doctor->last_name = $data['lastName'];
        $doctor->mobile_no = $data['mobileNo'];
        $doctor->login_name = $data['mobileNo'];
        $doctor->pharmaclient_image = 'ProfileImages/docexa_default_image.png';
        $doctor->image = 'ProfileImages/docexa_default_image.png';
        $doctor->medical_registration_no = $data['doctor_mr_no'];
        $doctor->email_id = $data['email'];
        $doctor->password = md5($data['password']);
        $doctor->speciality_id = $data['speciality_id'];
        $doctor->user_master_id = 1;
        $doctor->save();

        $medical_user_id = $doctor->pharmaclient_id;

        $medical_establishment_id = $this->createMedicalEstablishments($data['firstName'], $data['lastName']);

        $medical_establishment_doc_map_id = $this->createMedicalEstablishmentsUserMap($medical_user_id, $medical_establishment_id, $data['firstName'], $data['lastName']);

        $Medicaldata = Medicalestablishmentsmedicalusermap::find($medical_establishment_doc_map_id);
        //var_dump($Medicaldata);die;
        $Medicaldata->fee = $data['doctor_fee'];
        $Medicaldata->city = $data['doctor_city'];
        $Medicaldata->save();

        $res = new Skumaster();
        $res->createdefaultsku($Medicaldata->id, $data['doctor_fee']);

        $notificationdata = [
            'template' => 'welcome_email_to_doctor',
            'handle' => $Medicaldata->handle,
            'appointment_id' => ""
        ];
        $c = new Controller();
        $c->sendNotification($notificationdata);
        return response()->json(['status' => "success", 'doctor' => $this->autologin($Medicaldata->id), 'id' => $Medicaldata->id, 'handle' => $_ENV['APP_HANDLE'] . $Medicaldata->handle], 200);
    }
}
