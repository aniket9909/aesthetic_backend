<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Log;

class Skumaster extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'docexa_sku_master';
    protected $primaryKey = 'id';
    public function getskudetails($esteblishmentUserMapID)
    {

        try {
            if ($esteblishmentUserMapID == '') {
                return response()->json(['status' => "fail", 'msg' => "Opps! esteblishment user map id not found."], 400);
            }

            $data = DB::table('docexa_esteblishment_user_map_sku_details')
                ->select('docexa_esteblishment_user_map_sku_details.*','docexa_esteblishment_user_map_sku_details.id as sku_id')
                ->where('docexa_esteblishment_user_map_sku_details.user_map_id', $esteblishmentUserMapID)
                ->orderBy('default_flag','DESC')->get();

                Log::info(['dataaaaaaaaaaaaa' ,$data]);
            return response()->json(['status' => "success", 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => "fail", 'msg' => "Something went wrong"], 400);
        }
    }
    public function gethospitalskudetails($esteblishmentUserMapID,$hospitalID)
    {

        try {
            if ($esteblishmentUserMapID == '') {
                return response()->json(['status' => "fail", 'msg' => "Opps! esteblishment user map id not found."], 400);
            }

            $data = DB::table('docexa_esteblishment_user_map_sku_details')
            ->select('docexa_esteblishment_user_map_sku_details.*','docexa_esteblishment_user_map_sku_details.id as sku_id')
            ->where('docexa_esteblishment_user_map_sku_details.user_map_id', $esteblishmentUserMapID)
            ->where('docexa_esteblishment_user_map_sku_details.hospital_id', $hospitalID)
            ->orderBy('default_flag','DESC')->get();
            return response()->json(['status' => "success", 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => "fail", 'msg' => "Something went wrong"], 400);
        }
    }
    public function setskudetails($esteblishmentUserMapID, $request)
    {

        try {
            if ($esteblishmentUserMapID == '') {
                return response()->json(['status' => "fail", 'msg' => "Opps! esteblishment user map id not found."], 400);
            }
            $input = $request->input();
            if($input['mode'] != 1 && $input['mode'] != 2 && $input['mode'] != 3)
                return response()->json(['status' => "fail", 'msg' => "Opps! Booking mode invalid."], 400);
            
            if($input['mode'] == 1){
                $array = [
                    'user_map_id' => $esteblishmentUserMapID,
                    'fee' => $input['price'],
                    'title' => $input['title'],
                    'clinic_id' => $input['clinic_id'],
                    'description' => $input['description'],
                    'booking_type' => "Online Consultation"
                ];
                DB::table('docexa_esteblishment_user_map_sku_details')->insert($array);
            }
            else if($input['mode'] == 2){
                $array = [
                    'user_map_id' => $esteblishmentUserMapID,
                    'fee' => $input['price'],
                    'title' => $input['title'],
                    'clinic_id' => $input['clinic_id'],
                    'description' => $input['description'],
                    'booking_type' => "In clinic Consultation"
                ];
                DB::table('docexa_esteblishment_user_map_sku_details')->insert($array);
            }

            
          //  DB::table('docexa_esteblishment_user_map_sku_details')->insert($array);

            $data = DB::table('docexa_esteblishment_user_map_sku_details')
                ->select('docexa_esteblishment_user_map_sku_details.*','docexa_esteblishment_user_map_sku_details.id as sku_id')
                ->where('docexa_esteblishment_user_map_sku_details.user_map_id', $esteblishmentUserMapID)->orderBy('default_flag','DESC')->get();
            return response()->json(['status' => "success", 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => "fail", 'msg' => "Something went wrong"], 400);
        }
    }
    public function editskudetails($esteblishmentUserMapID, $id, $request)
    {

        try {
            if ($esteblishmentUserMapID == '') {
                return response()->json(['status' => "fail", 'msg' => "Opps! esteblishment user map id not found."], 400);
            }

            $input = $request->input();
            
            $array = [
                'fee' => $input['price'],
                'title' => $input['title'],
                'description' => $input['description'],
                'booking_type' => ($input['mode']==1) ? "Online Consultation" : "In clinic Consultation",
                'isupdateview' => 1
                
            ];
                  
            DB::table('docexa_esteblishment_user_map_sku_details')->where('id', $id)->update($array);
            $data = DB::table('docexa_esteblishment_user_map_sku_details')
                ->select('docexa_esteblishment_user_map_sku_details.*','docexa_esteblishment_user_map_sku_details.id as sku_id')
                ->where('docexa_esteblishment_user_map_sku_details.user_map_id', $esteblishmentUserMapID)->orderBy('default_flag','DESC')->get();
            return response()->json(['status' => "success", 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => "fail", 'msg' => "Something went wrong"], 400);
        }
    }
    public function setdefaultskudetails($esteblishmentUserMapID, $id)
    {

        try {
            if ($esteblishmentUserMapID == '') {
                return response()->json(['status' => "fail", 'msg' => "Opps! esteblishment user map id not found."], 400);
            }
            $array = [
                'default_flag'=>0
            ];
            DB::table('docexa_esteblishment_user_map_sku_details')->where('user_map_id', $esteblishmentUserMapID)->update($array);
            $array = [
                'default_flag'=>1,
                'is_enabled'=>1
            ];
            DB::table('docexa_esteblishment_user_map_sku_details')->where('id', $id)->update($array);
            $data = DB::table('docexa_esteblishment_user_map_sku_details')
                ->select('docexa_esteblishment_user_map_sku_details.*','docexa_esteblishment_user_map_sku_details.id as sku_id')
                ->where('docexa_esteblishment_user_map_sku_details.user_map_id', $esteblishmentUserMapID)->orderBy('default_flag','DESC')->get();
            return response()->json(['status' => "success", 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => "fail", 'msg' => "Something went wrong"], 400);
        }
    }
    public function updatestatusenable($esteblishmentUserMapID, $id)
    {

        try {
            if ($esteblishmentUserMapID == '') {
                return response()->json(['status' => "fail", 'msg' => "Opps! esteblishment user map id not found."], 400);
            }
            $array = [
                'is_enabled'=>1,
            ];
            DB::table('docexa_esteblishment_user_map_sku_details')->where('id', $id)->update($array);
            $data = DB::table('docexa_esteblishment_user_map_sku_details')
                ->select('docexa_esteblishment_user_map_sku_details.*','docexa_esteblishment_user_map_sku_details.id as sku_id')
                ->where('docexa_esteblishment_user_map_sku_details.user_map_id', $esteblishmentUserMapID)->orderBy('default_flag','DESC')->get();
            return response()->json(['status' => "success", 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => "fail", 'msg' => "Something went wrong"], 400);
        }
    }
    public function updatestatusdisable($esteblishmentUserMapID, $id)
    {

        try {
            if ($esteblishmentUserMapID == '') {
                return response()->json(['status' => "fail", 'msg' => "Opps! esteblishment user map id not found."], 400);
            }
            $array = [
                'is_enabled'=>0,
            ];
            DB::table('docexa_esteblishment_user_map_sku_details')->where('id', $id)->update($array);
            $data = DB::table('docexa_esteblishment_user_map_sku_details')
                ->select('docexa_esteblishment_user_map_sku_details.*','docexa_esteblishment_user_map_sku_details.id as sku_id')
                ->where('docexa_esteblishment_user_map_sku_details.user_map_id', $esteblishmentUserMapID)->orderBy('default_flag','DESC')->get();
            return response()->json(['status' => "success", 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => "fail", 'msg' => "Something went wrong"], 400);
        }
    }
    public function deleteskudetails($esteblishmentUserMapID, $id)
    {

        try {
            if ($esteblishmentUserMapID == '') {
                return response()->json(['status' => "fail", 'msg' => "Opps! esteblishment user map id not found."], 400);
            }
            DB::table('docexa_esteblishment_user_map_sku_details')->where('id', $id)->delete();
            $data = DB::table('docexa_esteblishment_user_map_sku_details')
                ->select('docexa_esteblishment_user_map_sku_details.*','docexa_esteblishment_user_map_sku_details.id as sku_id')
                ->where('docexa_esteblishment_user_map_sku_details.user_map_id', $esteblishmentUserMapID)->orderBy('default_flag','DESC')->get();
            return response()->json(['status' => "success", 'data' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['status' => "fail", 'msg' => "Something went wrong"], 400);
        }
    }
    public function getskudetailsbyid($user_map_id, $sku_id)
    {
        Log::info(['skuuuu' , $sku_id]);
        $data = DB::table('docexa_esteblishment_user_map_sku_details')
            ->select('docexa_esteblishment_user_map_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.fee')
            ->where('docexa_esteblishment_user_map_sku_details.id', $sku_id)
            ->where('docexa_esteblishment_user_map_sku_details.user_map_id', $user_map_id)->get()->first();
        return $data;
    }
    public function gethospitalskudetailsbyid($user_map_id, $sku_id,$hospital_id)
    {
        $data = DB::table('docexa_esteblishment_user_map_sku_details')
        ->select('docexa_esteblishment_user_map_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.fee')
        ->where('docexa_esteblishment_user_map_sku_details.id', $sku_id)
        ->where('docexa_esteblishment_user_map_sku_details.user_map_id', $user_map_id)
        ->where('docexa_esteblishment_user_map_sku_details.hospital_id', $hospital_id)
        ->first();
        return $data;
    }
    public function createdefaultsku($esteblishmentUserMapID, $fee)
    {
        DB::table('docexa_esteblishment_user_map_sku_details')->insert(['user_map_id' => $esteblishmentUserMapID, 'booking_type' => 'Online Consultation','title'=>'Regular Consultation','description'=>'Any questions around vaccines and well being', 'fee' => $fee,'is_enabled'=>1,'default_flag'=>1]);
        DB::table('docexa_esteblishment_user_map_sku_details')->insert(['user_map_id' => $esteblishmentUserMapID, 'booking_type' => 'In clinic Consultation','title'=>'Regular Consultation','description'=>'Any questions around vaccines and well being', 'fee' => $fee,'is_enabled'=>1]);
        DB::table('docexa_esteblishment_user_map_sku_details')->insert(['user_map_id' => $esteblishmentUserMapID, 'booking_type' => 'In clinic Consultation','title'=>'Pneumococcal Vaccination','description'=>'We will administer your child the Pneumococcal Vaccine along with personalised 1:1 advice', 'fee' => '4100','is_enabled'=>0]);
        DB::table('docexa_esteblishment_user_map_sku_details')->insert(['user_map_id' => $esteblishmentUserMapID, 'booking_type' => 'Online Consultation','title'=>'Pneumococcal Vaccine Pre-Vaccination Advice','description'=>'Confused whether this vaccine is the right choice for your child? Get all your doubts resolved',  'fee' => '100','is_enabled'=>0]);
    }
    public function updatedefaultsku($esteblishmentUserMapID, $fee)
    {
        DB::table('docexa_esteblishment_user_map_sku_details')->where(['user_map_id' => $esteblishmentUserMapID, 'booking_type' => 'Online Consultation', 'sku_id' => 1])->update(['fee' => $fee]);
        DB::table('docexa_esteblishment_user_map_sku_details')->where(['user_map_id' => $esteblishmentUserMapID, 'booking_type' => 'Online Consultation', 'sku_id' => 2])->update(['fee' => $fee]);
    }
}
