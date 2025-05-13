<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Log;
use App\Skumaster;
use App\Payment;
use App\Patientmasterphizer;
use App\Http\Controllers\Controller;
use App\Jobs\updateStatus;
use App\Jobs\updateHospitalStatus;
use App\Prescription;
use Carbon\Carbon;
use App\AssistantVital;


class AppointmentDetails extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'docexa_patient_booking_details';
    protected $primaryKey = 'id';
    public function getappointmentv2($request, $bookingID = 0)
    {
        //var_dump($bookingID,$bookingID != 0);die;
        if ($bookingID != 0 || $request == null) {
            $tabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.payment_mode', 'docexa_esteblishment_user_map_sku_details.id as sku_id', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost',)
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle,'/appointment/','" . $bookingID . "') as appointment_url")
                ->where('docexa_patient_booking_details.bookingidmd5', $bookingID)
                ->groupBy('docexa_patient_booking_details.bookingidmd5')
                ->get();
            $timingArray = DB::table('docexa_appointment_sku_details')->whereRaw("md5(booking_id) = ?", [$bookingID])->get();
            $prescriptionArray = Prescription::whereRaw("booking_id = ?", [$bookingID])->get();
            $data = [];
            foreach ($timingArray as $timing) {
                $data[] = ['start_booking_time' => $timing->start_booking_time, 'end_booking_time' => $timing->end_booking_time];
            }
            if (count($prescriptionArray) > 0) {
                foreach ($prescriptionArray as $pres) {
                    $data1[] = $pres->prescription_image;
                }
            } else {
                $data1 = [];
            }
            if (count($tabdata) > 0) {
                $tabdata[0]->timing_details = $data;
                if (count($data1) > 0) {
                    $tabdata[0]->prescription_flag = true;
                } else {
                    $tabdata[0]->prescription_flag = false;
                }
                $tabdata[0]->prescription_details = ["urls" => $data1];
                $tabdata[0]->appointment_url = Controller::urlshorten($tabdata[0]->appointment_url);
                $response = [
                    'appointment' => $tabdata[0]
                ];
            } else {
                $response = [
                    'appointment' => [],
                    'msg' => "no record found"
                ];
            }

            return $response;
        } else {
            $data = $request->input();
            $unscheduletabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.cost')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->where('docexa_patient_booking_details.date', null)
                ->whereIn('docexa_patient_booking_details.status', [1])
                ->whereNotNull('docexa_patient_booking_details.credit_history_id')
                ->latest('docexa_patient_booking_details.created_date')
                ->get();

            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.cost')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->where('docexa_patient_booking_details.date', '=', date('Y-m-d'))
                ->where('docexa_patient_booking_details.status', '!=', 3)
                ->where('docexa_patient_booking_details.status', '!=', 6)
                ->whereNotNull('docexa_patient_booking_details.credit_history_id')
                ->latest('docexa_patient_booking_details.start_time')
                ->get();

            $pasttabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.cost')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                //  ->whereNotNull('docexa_patient_booking_details.date')
                ->whereIn('docexa_patient_booking_details.status', [4, 3, 6])
                ->whereNotNull('docexa_patient_booking_details.credit_history_id')
                ->latest('docexa_patient_booking_details.created_date')
                ->get();
            $upcomingtabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.cost')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->whereDate('docexa_patient_booking_details.date', '>=', Carbon::parse(date('Y-m-d'))->startOfDay())

                ->whereIn('docexa_patient_booking_details.status', [2, 5])
                ->whereNotNull('docexa_patient_booking_details.credit_history_id')
                ->latest('docexa_appointment_sku_details.start_booking_time')
                ->get();
            // ->whereNotNull('docexa_patient_booking_details.date')


            $response = [
                'unscheduleappointment' => $unscheduletabdata,
                'todayappointment' => $todaystabdata,
                'pastappointment' => $pasttabdata,
                'upcomingappointment' => $upcomingtabdata
            ];
            return $response;
        }
    }
    public function getappointment($request, $bookingID = 0)
    {
        //var_dump($bookingID,$bookingID != 0);die;
        if ($bookingID != 0 || $request == null) {
            DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
            $tabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.source', 'docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.user_map_id', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.credit_history_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.booking_id as book_id', 'docexa_patient_booking_details.doctor_id', 'docexa_esteblishment_user_map_sku_details.id as sku_id', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle,'/appointment/','" . $bookingID . "') as appointment_url")
                ->where('docexa_patient_booking_details.bookingidmd5', $bookingID)
                ->groupBy('docexa_patient_booking_details.bookingidmd5')
                //->union($a)
                ->get();
            $timingArray = DB::table('docexa_appointment_sku_details')->whereRaw("md5(booking_id) = ?", [$bookingID])->get();
            // $prescriptionArray = PrescriptionData::whereRaw("md5(booking_id) = ?", [$bookingID])->get();
            $prescriptionArray = PrescriptionData::where('booking_id', $bookingID)->get();

            // Log::info(['ttttttttttttt', $tabdata]);

            $data = [];
            foreach ($timingArray as $timing) {
                $data[] = ['start_booking_time' => $timing->start_booking_time, 'end_booking_time' => $timing->end_booking_time];
            }

            if (count($prescriptionArray) > 0) {
                foreach ($prescriptionArray as $pres) {
                    $data1[] = $pres;
                }
            } else {
                $data1 = [];
            }

            if (count($tabdata) > 0) {
                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $tabdata[0]->user_map_id)->where('id', $tabdata[0]->clinic_id)->first();
            }
            if (count($tabdata) > 0) {
                $tabdata[0]->timing_details = $data;
                if (count($data1) > 0) {

                    $tabdata[0]->prescription_flag = true;
                } else {
                    $tabdata[0]->prescription_flag = false;
                }
                if (count($data1) > 0) {
                    $url = $_ENV['APP_URL'] . '/api/v3/prescription/view/' . $tabdata[0]->user_map_id . '/' . $tabdata[0]->patient_id . '/' . $bookingID;

                    $tabdata[0]->prescription_details = [
                        "urls" => $url
                    ];
                } else {
                    $tabdata[0]->prescription_details = [
                        "urls" => ''
                    ];
                }
                // Log::info(['url of the precription' => $tabdata[0]->prescription_details]);
                // $tabdata[0]->payment_url = Controller::urlshorten($_ENV['PAYMENT_URL_V1'] . $bookingID . '/pay');
                $tabdata[0]->payment_url = $_ENV['APP_URL'] . '/api/v3/payment/' . $bookingID . '/pay';
                $tabdata[0]->appointment_url = Controller::urlshorten($tabdata[0]->appointment_url);
                // $tabdata[0]->appointment_url = $tabdata[0]->appointment_url;

                $tabdata[0]->created_date = date('M d, Y h:i A', strtotime($tabdata[0]->created_date));
                $tabdata[0]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                $response = [
                    'appointment' => $tabdata
                ];
            } else {
                $response = [
                    'appointment' => $tabdata,
                    'msg' => "no record found"
                ];
            }
            return $response;
        } else {
            $data = $request->input();

            $unscheduletabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->where('docexa_patient_booking_details.date', null)
                ->whereIn('docexa_patient_booking_details.status', [1])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })
                ->latest('docexa_patient_booking_details.created_date')
                //->union($a) 
                ->get();

            // $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $unscheduletabdata[0]->user_map_id)->where('id',$unscheduletabdata[0]->clinic_id)->first();
            // $unscheduletabdata[0]->clinic_name =  $clinicName ?$clinicName->clinic_name  :null;

            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->where('docexa_patient_booking_details.date', '=', date('Y-m-d'))
                ->where('docexa_patient_booking_details.status', '!=', 3)
                ->where('docexa_patient_booking_details.status', '!=', 6)
                ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                //->union($b) 
                ->get();




            // $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $todaystabdata[0]->user_map_id)->where('id',$todaystabdata[0]->clinic_id)->first();
            // $todaystabdata[0]->clinic_name =  $clinicName ?$clinicName->clinic_name  :null;

            $pasttabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                // ->whereNotNull('docexa_patient_booking_details.date')
                ->whereIn('docexa_patient_booking_details.status', [4, 3, 6])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                //->union($c) 
                ->get();

            $upcomingtabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->whereDate('docexa_patient_booking_details.date', '>=', Carbon::parse(date('Y-m-d'))->startOfDay())
                ->whereIn('docexa_patient_booking_details.status', [1, 2, 5])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                // ->union($d) 
                ->get();

            // ->whereNotNull('docexa_patient_booking_details.date')

            if (count($unscheduletabdata) > 0) {
                foreach ($unscheduletabdata as $key => $value) {
                    $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $data['user_map_id'])->where('id', $value->clinic_id)->first();
                    $unscheduletabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                }
            }
            if (count($todaystabdata) > 0) {
                foreach ($todaystabdata as $key => $value) {
                    $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $data['user_map_id'])->where('id', $value->clinic_id)->first();
                    $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                }
            }

            if (count($pasttabdata) > 0) {
                foreach ($pasttabdata as $key => $value) {
                    $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $data['user_map_id'])->where('id', $value->clinic_id)->first();
                    $pasttabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                }
            }

            if (count($upcomingtabdata) > 0) {
                foreach ($upcomingtabdata as $key => $value) {
                    $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $data['user_map_id'])->where('id', $value->clinic_id)->first();
                    $upcomingtabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                }
            }


            $response = [
                'unscheduleappointment' => $unscheduletabdata,
                'todayappointment' => $todaystabdata,
                'pastappointment' => $pasttabdata,
                'upcomingappointment' => $upcomingtabdata
            ];
            return $response;
        }
    }
    public function gethospitalappointment($request, $bookingID = 0)
    {
        //var_dump($bookingID,$bookingID != 0);die;
        if ($bookingID != 0 || $request == null) {
            $tabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_hospital_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->Join('docexa_hospital_master', 'docexa_hospital_master.hospital_id', 'docexa_patient_booking_details.hospital_id')
                ->select('docexa_patient_booking_details.user_map_id', 'docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.user_map_id', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.credit_history_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.booking_id as book_id', 'docexa_patient_booking_details.doctor_id', 'docexa_esteblishment_user_map_sku_details.id as sku_id', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HOSPITAL_HANDLE'] . "',docexa_hospital_master.slug) as handle")
                ->selectRaw("concat('" . $_ENV['APP_HOSPITAL_HANDLE'] . "',docexa_hospital_master.slug,'/appointment/','" . $bookingID . "') as appointment_url")
                ->where('docexa_patient_booking_details.bookingidmd5', $bookingID)
                ->groupBy('docexa_patient_booking_details.bookingidmd5')
                ->get();
            $timingArray = DB::table('docexa_appointment_sku_details')->whereRaw("md5(booking_id) = ?", [$bookingID])->get();
            $prescriptionArray = Prescription::whereRaw("booking_id = ?", [$bookingID])->get();
            $data = [];
            foreach ($timingArray as $timing) {
                $data[] = ['start_booking_time' => $timing->start_booking_time, 'end_booking_time' => $timing->end_booking_time];
            }

            if (count($prescriptionArray) > 0) {
                foreach ($prescriptionArray as $pres) {
                    $data1[] = $pres->prescription_image;
                }
            } else {
                $data1 = [];
            }

            if (count($tabdata) > 0) {
                $tabdata[0]->timing_details = $data;
                if (count($data1) > 0) {
                    $tabdata[0]->prescription_flag = true;
                } else {
                    $tabdata[0]->prescription_flag = false;
                }
                $tabdata[0]->prescription_details = ["urls" => $data1];
                $tabdata[0]->payment_url = Controller::urlshorten($_ENV['PAYMENT_URL_V1'] . $bookingID . '/pay');
                $tabdata[0]->appointment_url = Controller::urlshorten($tabdata[0]->appointment_url);
                $tabdata[0]->created_date = date('M d, Y h:i A', strtotime($tabdata[0]->created_date));
                $response = [
                    'appointment' => $tabdata
                ];
            } else {
                $response = [
                    'appointment' => $tabdata,
                    'msg' => "no record found"
                ];
            }

            return $response;
        } else {
            $data = $request->input();
            $unscheduletabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_hospital_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.user_map_id', 'docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.cost')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->where('docexa_patient_booking_details.date', null)
                ->whereIn('docexa_patient_booking_details.status', [1])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })
                ->latest('docexa_patient_booking_details.created_date')
                ->get();
            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_hospital_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.user_map_id', 'docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->where('docexa_patient_booking_details.date', '=', date('Y-m-d'))
                ->where('docexa_patient_booking_details.status', '!=', 3)
                ->where('docexa_patient_booking_details.status', '!=', 6)
                ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })
                ->latest('docexa_patient_booking_details.start_time')
                ->get();

            $pasttabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_hospital_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.user_map_id', 'docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                // ->whereNotNull('docexa_patient_booking_details.date')
                ->whereIn('docexa_patient_booking_details.status', [4, 3, 6])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })
                ->latest('docexa_patient_booking_details.created_date')
                ->get();
            $upcomingtabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_hospital_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.user_map_id', 'docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->whereNotNull('docexa_patient_booking_details.date')
                ->whereIn('docexa_patient_booking_details.status', [1, 2, 5])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })
                ->latest('docexa_appointment_sku_details.start_booking_time')
                ->get();


            $response = [
                'unscheduleappointment' => $unscheduletabdata,
                'todayappointment' => $todaystabdata,
                'pastappointment' => $pasttabdata,
                'upcomingappointment' => $upcomingtabdata
            ];
            return $response;
        }
    }
    public function getappointmentv3($request, $bookingID = 0)
    {
        $tabdata = DB::table('docexa_patient_booking_details_phizer')
            ->Join('docexa_patient_details_phizer', 'docexa_patient_details_phizer.patient_id', '=', 'docexa_patient_booking_details_phizer.patient_id')
            ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details_phizer.status')
            ->select('docexa_patient_booking_details_phizer.patient_id', 'docexa_patient_booking_details_phizer.booking_id as book_id', 'docexa_patient_booking_details_phizer.location', 'docexa_patient_booking_details_phizer.latlong', 'docexa_patient_booking_details_phizer.cancellation_reason as reason', 'docexa_patient_booking_details_phizer.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details_phizer.schedule_remark', 'docexa_patient_booking_details_phizer.bookingidmd5 as booking_id', 'docexa_patient_booking_details_phizer.created_date', 'docexa_patient_booking_details_phizer.date', 'docexa_patient_booking_details_phizer.start_time', 'docexa_patient_details_phizer.patient_name', 'docexa_patient_details_phizer.email_id as email', 'docexa_patient_details_phizer.mobile_no', 'docexa_patient_booking_details_phizer.cost')
            ->selectRaw('+91 as country_code')
            ->where('docexa_patient_booking_details_phizer.bookingidmd5', $bookingID)
            ->groupBy('docexa_patient_booking_details_phizer.bookingidmd5')
            ->get();

        if (count($tabdata) > 0) {
            $response = [
                'appointment' => $tabdata
            ];
        } else {
            $response = [
                'appointment' => $tabdata,
                'msg' => "no record found"
            ];
        }

        return $response;
    }
    public function getallslot($user_map_id, $date = null)
    {
        if ($date == null) {
            $date = date('Y-m-d');
        }
        $response = DB::table('docexa_patient_booking_details')
            ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
            ->selectRaw('date_format(docexa_appointment_sku_details.start_booking_time,"%H:%i") as start_booking_time,date_format(docexa_appointment_sku_details.end_booking_time,"%H:%i") as end_booking_time')
            ->where('docexa_patient_booking_details.user_map_id', $user_map_id)
            ->where('docexa_patient_booking_details.date', '=', $date)
            ->where(function ($query) {
                $query->where("credit_history_id", "!=", null);
                $query->orWhere(function ($innerquery) {
                    $innerquery->where("payment_mode", "!=", "byPatient");
                    $innerquery->where("payment_mode", "!=", "");
                });
            })
            ->get();
        //  var_dump($response);die;
        return $response;
        // dd($response);
    }
    public function scheduleappointmentv2($request)
    {

        $data = $request->input();

        if ($data['scheduletimestamp'] != '') {
            $date = date('Y-m-d', strtotime($data['scheduletimestamp']));
            $time = date('H:i', strtotime($data['scheduletimestamp']));
            $data['scheduletimestamp'] = date('Y-m-d H:i:s', strtotime($data['scheduletimestamp']));
            $end_booking_time = date('Y-m-d H:i:s', strtotime('+' . $data['slot_size'] . ' minutes', strtotime($data['scheduletimestamp'])));
        } else {
            $data['scheduletimestamp'] = null;
            $end_booking_time = null;
            $date = null;
            $time = null;
        }
        if ($data['slot_size'] == '')
            $data['slot_size'] = 0;
        DB::table('docexa_patient_booking_details')->where('bookingidmd5', $data['bookingID'])->limit(1)->update(array('cancellation_reason' => $data['remark'], 'status' => $data['status'], 'date' => $date, 'start_time' => $time));
        $booking_id = DB::table('docexa_patient_booking_details')->where('bookingidmd5', $data['bookingID'])->get()->first()->booking_id;
        DB::table('docexa_appointment_sku_details')->where('booking_id', $booking_id)->limit(1)->update(array('start_booking_time' => $data['scheduletimestamp'], 'end_booking_time' => $end_booking_time, 'slot_size' => $data['slot_size']));
        $tabdata = $this->getappointmentv2(null, $data['bookingID']);
        $urlArray = parse_url($tabdata['appointment']->handle, PHP_URL_PATH);
        $segments = explode('/', $urlArray);
        $numSegments = count($segments);
        $currentSegment = $segments[$numSegments - 1];
        // var_dump($currentSegment);die;
        $c = new Controller();
        if ($data['status'] == 2) {
            $start = Carbon::parse($data['scheduletimestamp']);
            $start->setTimezone('Asia/Kolkata');
            $us = new updateStatus($data['bookingID']);
            $us->apptID();
            dispatch((new updateStatus($data['bookingID']))->delay($start->addMinutes($_ENV['QUEUE_TIME'])));
            $notificationdata = [
                'template' => 'appointment_accepted_notifier',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 4 && $tabdata['appointment']->payment_mode == '') {
            $notificationdata = [
                'template' => 'doc_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 4 && $tabdata['appointment']->payment_mode == 'byPatient') {
            $notificationdata = [
                'template' => 'doc_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 4 && $tabdata['appointment']->payment_mode == 'direct') {
            $notificationdata = [
                'template' => 'appointment_completed_intimation_pay_outside_DCX',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 4 && $tabdata['appointment']->payment_mode == 'free') {
            $notificationdata = [
                'template' => 'appointment_completed_intimation_FREE',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 3) {
            $notificationdata = [
                'template' => 'doc_appointment_cancelled_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_cancelled_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 7) {
            $start = Carbon::parse($data['scheduletimestamp']);
            $start->setTimezone('Asia/Kolkata');
            $us = new updateStatus($data['bookingID']);
            $us->apptID();
            dispatch((new updateStatus($data['bookingID']))->delay($start->addMinutes($_ENV['QUEUE_TIME'])));
            $notificationdata = [
                'template' => 'appointment_rescheduled_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 6) {
            $notificationdata = [
                'template' => 'doctor_appointment_request_rejected_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_request_rejected_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
        }
        return $tabdata;
    }

    public function updateappointment($booking_id, $request)
    {
        $data = $request->input();
        DB::table('docexa_patient_booking_details')->where('bookingidmd5', $booking_id)->limit(1)->update(array('schedule_remark' => $data['schedule_remark']));
    }
    public function scheduleappointment($request)
    {
        $data = $request->input();
        if ($data['scheduletimestamp'] != '') {
            $date = date('Y-m-d', strtotime($data['scheduletimestamp']));
            $time = date('H:i', strtotime($data['scheduletimestamp']));
            $data['scheduletimestamp'] = date('Y-m-d H:i:s', strtotime($data['scheduletimestamp']));
            $end_booking_time = date('Y-m-d H:i:s', strtotime('+' . $data['slot_size'] . ' minutes', strtotime($data['scheduletimestamp'])));
        } else {
            $data['scheduletimestamp'] = null;
            $end_booking_time = null;
            $date = null;
            $time = null;
        }
        if ($data['slot_size'] == '')
            $data['slot_size'] = 0;

        DB::table('docexa_patient_booking_details')->where('bookingidmd5', $data['bookingID'])->limit(1)->update(array('cancellation_reason' => $data['remark'], 'status' => $data['status'], 'date' => $date, 'start_time' => $time));
        $booking_id = DB::table('docexa_patient_booking_details')->where('bookingidmd5', $data['bookingID'])->get()->first()->booking_id;
        DB::table('docexa_appointment_sku_details')->where('booking_id', $booking_id)->limit(1)->update(array('start_booking_time' => $data['scheduletimestamp'], 'end_booking_time' => $end_booking_time, 'slot_size' => $data['slot_size']));
        $tabdata = $this->getappointment(null, $data['bookingID']);

        $urlArray = parse_url($tabdata['appointment'][0]->handle, PHP_URL_PATH);
        $segments = explode('/', $urlArray);
        $numSegments = count($segments);
        $currentSegment = $segments[$numSegments - 1];
        // var_dump($currentSegment);die;
        $c = new Controller();
        if ($data['status'] == 2) {
            $start = Carbon::parse($data['scheduletimestamp']);
            $start->setTimezone('Asia/Kolkata');
            $us = new updateStatus($data['bookingID']);
            $us->apptID();
            dispatch((new updateStatus($data['bookingID']))->delay($start->addMinutes($_ENV['QUEUE_TIME'])));
            $notificationdata = [
                'template' => 'appointment_accepted_notifier',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 4 && $tabdata['appointment'][0]->payment_mode == '') {
            $notificationdata = [
                'template' => 'doc_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 4 && $tabdata['appointment'][0]->payment_mode == 'byPatient') {
            $notificationdata = [
                'template' => 'doc_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 4 && $tabdata['appointment'][0]->payment_mode == 'direct') {
            $notificationdata = [
                'template' => 'appointment_completed_intimation_pay_outside_DCX',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 4 && $tabdata['appointment'][0]->payment_mode == 'free') {
            $notificationdata = [
                'template' => 'appointment_completed_intimation_FREE',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 3) {
            $notificationdata = [
                'template' => 'doc_appointment_cancelled_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_cancelled_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 7) {
            $start = Carbon::parse($data['scheduletimestamp']);
            $start->setTimezone('Asia/Kolkata');
            $us = new updateStatus($data['bookingID']);
            $us->apptID();
            dispatch((new updateStatus($data['bookingID']))->delay($start->addMinutes($_ENV['QUEUE_TIME'])));
            $notificationdata = [
                'template' => 'appointment_rescheduled_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 6) {
            $notificationdata = [
                'template' => 'doctor_appointment_request_rejected_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_request_rejected_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
        }
        //var_dump(json_encode($data));die;


        return $tabdata;
    }

    public function createappointment($request)
    {

        $data = $request->input();

        //var_dump($data);die;

        Log::info([$data]);
        if (!isset($data['age'])) {
            $data['age'] = 0;
        }
        if (!isset($data['gender'])) {
            $data['gender'] = 0;
        }
        if (!isset($data['schedule_remark'])) {
            $data['schedule_remark'] = '';
        }
        Log::info($data['gender']);

        $medicaldata = Medicalestablishmentsmedicalusermap::find($data['user_map_id'])->get()->first();
        $check = Patientmaster::where('mobile_no', $data['patient_mobile_no'])
            ->where('patient_name', $data['patient_name'])
            ->where('created_by_doctor', $data['user_map_id'])->first();
        Log::info($check);
        if (isset($check->patient_id)) {
            $patientdata = $check;
        } else {
            // $patientdata = new Patientmaster();
            // $patientdata->patient_name = $data['patient_name'];
            // $patientdata->email_id = $data['email'];
            // $patientdata->mobile_no = $data['patient_mobile_no'];
            // $patientdata->username = $data['patient_mobile_no'];
            // $patientdata->age = $data['age'];
            // $patientdata->gender = $data['gender'];
            // $patientdata->created_by_doctor = $data['user_map_id'];
            // $save = $patientdata->save();


            $input = $request->all();
            $existingPatientByMobile = Patientmaster::where('mobile_no', $data['patient_mobile_no'])->where('created_by_doctor', $data['user_map_id'])->get();
            $existingPatientByName = Patientmaster::where('patient_name', $data['patient_name'])->where('mobile_no', $data['patient_mobile_no'])->where('created_by_doctor', $data['user_map_id'])->get();

            if ($existingPatientByMobile->count() > 0 && $existingPatientByName->count() == 0) {
                // Save in patientmaster table
                $patientdata = new Patientmaster();
                $patientdata->patient_name = $data['patient_name'];
                $patientdata->gender = $data['gender'];
                $patientdata->mobile_no = $data['patient_mobile_no'];
                $patientdata->username = $data['patient_mobile_no'];
                $patientdata->created_by_doctor = $data['user_map_id'];
                $patientdata->dob = $data['dob'];
                $patientdata->flag = array_key_exists('flag', $data) ? ($data['flag'] ? $data['flag'] : null) : null;

                $save = $patientdata->save();
                $patientId = Patientmaster::where('mobile_no', $data['patient_mobile_no'])->where('patient_name', $data['patient_name'])->first()->patient_id;
                Log::info('create pateint');
                if ($save) {
                    Log::info(['pattientid added through the case 2' => $patientId]);

                    DB::insert('insert into docexa_patient_doctor_relation (doctor_id,user_map_id, patient_id) values(?,?,?)', [0, $data['user_map_id'], $patientId]);
                }
            } else if ($existingPatientByName->count() == 0 && $existingPatientByMobile->count() == 0) {
                $patientdata = new Patientmaster();
                $patientdata->patient_name = $data['patient_name'];
                $patientdata->gender = $data['gender'];
                $patientdata->mobile_no = $data['patient_mobile_no'];
                $patientdata->username = $data['patient_mobile_no'];
                $patientdata->created_by_doctor = $data['user_map_id'];
                $patientdata->dob = $data['dob'];
                $patientdata->flag = array_key_exists('flag', $data) ? ($data['flag'] ? $data['flag'] : null) : null;

                $save1 = $patientdata->save();
                $patientId = Patientmaster::where('mobile_no', $data['patient_mobile_no'])->where('patient_name', $data['patient_name'])->first()->patient_id;

                Log::info('create pateint');
                if ($save1) {
                    Log::info(['pattientid added through the case 2' => $patientId]);

                    DB::insert('insert into docexa_patient_doctor_relation (doctor_id,user_map_id, patient_id) values(?,?,?)', [0, $data['user_map_id'], $patientId]);
                }
                $superpatientmaster = new SuperPatients();
                $superpatientmaster->patient_name = $data['patient_name'];
                $superpatientmaster->gender = $data['gender'];
                $superpatientmaster->mobile_no = $data['patient_mobile_no'];
                $superpatientmaster->username = $data['patient_mobile_no'];
                $superpatientmaster->created_by_doctor = $data['user_map_id'];
                $superpatientmaster->dob = $data['dob'];
                $save2 = $superpatientmaster->save();
                $patientid = SuperPatients::where('mobile_no', $data['patient_mobile_no'])->where('patient_name', $data['patient_name'])->first()->patient_id;

                Log::info('create pateint');
                // if ($save2) {
                //   DB::insert('insert into docexa_patient_doctor_relation (doctor_id,user_map_id, patient_id) values(?,?,?)', [0, $data['user_map_id'], $patientid]);
                // }
            } else {
                Log::info('create pateint');
                // Save in both patientmaster and superpatientmaster tables
                $patientdata = new Patientmaster();
                $patientdata->patient_name = $data['patient_name'];
                $patientdata->gender = $data['gender'];
                $patientdata->dob = $data['dob'];
                $patientdata->mobile_no = $data['mobile'];
                $patientdata->state = $data['state'];
                $patientdata->username = $data['mobile'];
                $patientdata->created_by_doctor = $data['user_map_id'];
                $patientdata->dob = $data['dob'];
                $patientdata->flag = array_key_exists('flag', $data) ? ($data['flag'] ? $data['flag'] : null) : null;


                $save1 = $patientdata->save();
                $patientId = Patientmaster::where('mobile_no', $data['patient_mobile_no'])->first()->patient_id;

                Log::info('create pateint');
                if ($save1) {
                    Log::info(['pattientid added through the case 2' => $patientId]);

                    DB::insert('insert into docexa_patient_doctor_relation (doctor_id,user_map_id, patient_id) values(?,?,?)', [0, $data['user_map_id'], $patientId]);
                }
                $superpatientmaster = new SuperPatients();
                $superpatientmaster->patient_name = $data['patient_name'];
                $superpatientmaster->gender = $data['gender'];
                $superpatientmaster->mobile_no = $data['patient_mobile_no'];
                $superpatientmaster->username = $data['patient_mobile_no'];
                $superpatientmaster->created_by_doctor = $data['user_map_id'];
                $superpatientmaster->dob = $data['dob'];
                $save2 = $superpatientmaster->save();
                $patientid = SuperPatients::where('mobile_no', $data['patient_mobile_no'])->where('patient_name', $data['patient_name'])->first()->patient_id;

                Log::info('create pateint');
                //    if ($save2) {
                //      DB::insert('insert into docexa_patient_doctor_relation (doctor_id,user_map_id, patient_id) values(?,?,?)', [0, $data['user_map_id'], $patientid]);
                //    }
            }
        }
        $skuobj = new Skumaster();
        Log::info(['skudatidd', $data['sku_id']]);
        $skudata = $skuobj->getskudetailsbyid($data['user_map_id'], $data['sku_id']);
        Log::info(['skudataa', $skudata]);
        if (isset($data['payment_amount'])) {
            Log::info(['1']);
            $fee = $data['payment_amount'];
        } else {
            Log::info(['2']);

            $fee = $skudata->fee;
        }
        if (isset($data['slot_size']) && $data['slot_size'] != '') {
            Log::info(['3']);
            $slot_size = $data['slot_size'];
        } else {
            $slot_size = (int) $_ENV['SLOT_SIZE'];
            Log::info(['4']);
        }
        if (isset($data['schedule_date']) && $data['schedule_date'] != null) {
            Log::info(['status 2']);
            $status = 2;
            $date = date('Y-m-d', strtotime($data['schedule_date']));
            $time = date('H:i', strtotime($data['schedule_time']));
            $start_booking_time = date('Y-m-d H:i:s', strtotime($data['schedule_date'] . " " . $data['schedule_time']));
            $end_booking_time = date('Y-m-d H:i:s', strtotime('+' . $slot_size . ' minutes', strtotime($data['schedule_date'] . " " . $data['schedule_time'])));
        } else {
            Log::info(['statuschabged to 1']);
            $status = 1;
            $start_booking_time = null;
            $end_booking_time = null;
            $date = null;
            $time = null;
        }
        Log::info(['payyyyyyyyyyymentMode', isset($data['payment_mode'])]);
        if (isset($data['payment_mode'])) {
            $payment_mode = $data['payment_mode'];
            $created_by = 'doctor';
        } else {
            $payment_mode = "byPatient";
            $created_by = 'patient';
        }

        if (isset($data['source'])) {
            $source = $data['source'];
        } else {
            $source = 'NA';
        }

        $id = DB::table('docexa_patient_booking_details')->insertGetId([
            'source' => $source,
            'gender' => $data['gender'],
            'age' => $data['age'],
            'patient_name' => $data['patient_name'],
            'mobile_no' => $data['patient_mobile_no'],
            'email_id' => $data['email'],
            'date' => $date,
            'start_time' => $time,
            'payment_mode' => $payment_mode,
            'created_by' => $created_by,
            'status' => $status,
            'schedule_remark' => $data['schedule_remark'],
            'created_date' => date('Y-m-d H:i:s'),
            'user_map_id' => $data['user_map_id'],
            'cost' => $fee,
            'patient_id' => $patientdata->patient_id,
            'doctor_id' => $medicaldata->medical_user_id,
            'clinic_id' => $data['clinic_id']

        ]);
        //  Log::info(['id', DB::table('docexa_patient_booking_details')->where('id', $iudy)])
        Log::info(['idddddddddddd', $id]);
        //var_dump($id);die;
        DB::table('docexa_appointment_sku_details')->insertGetId(['start_booking_time' => $start_booking_time, 'end_booking_time' => $end_booking_time, 'slot_size' => $slot_size, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'booking_id' => $id, 'esteblishment_user_map_sku_id' => $data['sku_id'], 'cost' => $fee, 'payable_price' => $fee, 'discount' => 0, 'booking_type' => $skudata->booking_type]);

        $bookinggIdmd5 = md5($id);

        DB::table('docexa_patient_booking_details')->where('booking_id', $id)->limit(1)->update(array('bookingidmd5' => $bookinggIdmd5));
        $tabdata = $this->getappointment(null, $bookinggIdmd5);

        $res = new payment();
        $paymentdata = $res->createpayment($bookinggIdmd5);

        Log::info(['payyyy', $paymentdata]);

        $urlArray = parse_url($tabdata['appointment'][0]->handle, PHP_URL_PATH);
        $segments = explode('/', $urlArray);
        $numSegments = count($segments);
        $currentSegment = $segments[$numSegments - 1];
        $c = new Controller();
        Log::info("status", [$status, $payment_mode, $created_by]);

        if ($status == 2 && $payment_mode == 'byPatient' && $created_by == 'doctor') {
            $notificationdata = [
                'template' => 'appt_scheduled_created_notification',
                'handle' => $currentSegment,
                'appointment_id' => $bookinggIdmd5
            ];
            $c->sendNotification($notificationdata);
        } else if ($status == 1 && $payment_mode == 'byPatient' && $created_by == 'doctor') {
            $notificationdata = [
                'template' => 'appt_unscheduled_created_notification',
                'handle' => $currentSegment,
                'appointment_id' => $bookinggIdmd5
            ];
            $c->sendNotification($notificationdata);
        } else if ($status == 2 && $payment_mode == 'direct') {
            $notificationdata = [
                'template' => 'appt_scheduled_created_notification_pay_outside_DCX',
                'handle' => $currentSegment,
                'appointment_id' => $bookinggIdmd5
            ];
            $c->sendNotification($notificationdata);
        } else if ($status == 2 && $payment_mode == 'free') {
            $notificationdata = [
                'template' => 'appt_scheduled_created_notification_FREE',
                'handle' => $currentSegment,
                'appointment_id' => $bookinggIdmd5
            ];
            $c->sendNotification($notificationdata);
        }
        Log::info(['aptt' => $tabdata['appointment'], 'payment' => $paymentdata]);

        return ['appointment' => $tabdata['appointment'], 'payment' => $paymentdata, "appointment_id" => $id];
    }
    public function createappointmentforprescription($data)
    {

        // $data = $request->input();

        //var_dump($data);die;

        Log::info([$data]);
        if (!isset($data['age'])) {
            $data['age'] = 0;
        }
        if (!isset($data['gender'])) {
            $data['gender'] = 0;
        }
        if (!isset($data['schedule_remark'])) {
            $data['schedule_remark'] = '';
        }
        Log::info($data['gender']);

        $medicaldata = Medicalestablishmentsmedicalusermap::find($data['user_map_id'])->get()->first();


        $check = Patientmaster::where('mobile_no', $data['patient_mobile_no'])->where('created_by_doctor', $data['user_map_id'])->first();
        Log::info($check);
        if (isset($check->patient_id)) {
            $patientdata = $check;
        } else {
            $patientdata = new Patientmaster();
            $patientdata->patient_name = $data['patient_name'];
            $patientdata->email_id = $data['email'];
            $patientdata->mobile_no = $data['patient_mobile_no'];
            $patientdata->username = $data['patient_mobile_no'];
            $patientdata->age = $data['age'];
            $patientdata->gender = $data['gender'];
            $patientdata->created_by_doctor = $data['user_map_id'];
            $save = $patientdata->save();
            $patientId = Patientmaster::where('mobile_no', $data['patient_mobile_no'])->first();
            Log::info('create pateint');
            if ($save) {
                DB::insert('insert into docexa_patient_doctor_relation (doctor_id,user_map_id, patient_id) values(?,?,?)', [0, $data['user_map_id'], $patientId]);
            }
        }
        $skuobj = new Skumaster();
        $skudata = $skuobj->getskudetailsbyid($data['user_map_id'], $data['sku_id']);
        Log::info(['skudataa', $skudata]);
        if (isset($data['payment_amount'])) {
            Log::info(['1']);
            $fee = $data['payment_amount'];
        } else {
            Log::info(['2']);

            $fee = $skudata->fee;
        }
        if (isset($data['slot_size']) && $data['slot_size'] != '') {
            Log::info(['3']);
            $slot_size = $data['slot_size'];
        } else {
            $slot_size = (int) $_ENV['SLOT_SIZE'];
            Log::info(['4']);
        }
        if (isset($data['schedule_date']) && $data['schedule_date'] != null) {
            Log::info(['status 2']);
            $status = 2;
            $date = date('Y-m-d', strtotime($data['schedule_date']));
            $time = date('H:i', strtotime($data['schedule_time']));
            $start_booking_time = date('Y-m-d H:i:s', strtotime($data['schedule_date'] . " " . $data['schedule_time']));
            $end_booking_time = date('Y-m-d H:i:s', strtotime('+' . $slot_size . ' minutes', strtotime($data['schedule_date'] . " " . $data['schedule_time'])));
        } else {
            Log::info(['statuschabged to 1']);
            $status = 1;
            $start_booking_time = null;
            $end_booking_time = null;
            $date = null;
            $time = null;
        }
        Log::info(['payyyyyyyyyyymentMode', isset($data['payment_mode'])]);
        if (isset($data['payment_mode'])) {
            $payment_mode = $data['payment_mode'];
            $created_by = 'doctor';
        } else {
            $payment_mode = "byPatient";
            $created_by = 'patient';
        }

        if (isset($data['source'])) {
            $source = $data['source'];
        } else {
            $source = 'NA';
        }

        $id = DB::table('docexa_patient_booking_details')->insertGetId([
            'source' => $source,
            'gender' => $data['gender'],
            'age' => $data['age'],
            'patient_name' => $data['patient_name'],
            'mobile_no' => $data['patient_mobile_no'],
            'email_id' => $data['email'],
            'date' => $date,
            'start_time' => $time,
            'payment_mode' => $payment_mode,
            'created_by' => $created_by,
            'status' => $status,
            'schedule_remark' => $data['schedule_remark'],
            'created_date' => date('Y-m-d H:i:s'),
            'user_map_id' => $data['user_map_id'],
            'cost' => $fee,
            'patient_id' => $patientdata->patient_id,
            'doctor_id' => $medicaldata->medical_user_id,
            'clinic_id' => $data['clinic_id']

        ]);
        //  Log::info(['id', DB::table('docexa_patient_booking_details')->where('id', $iudy)])
        Log::info(['idddddddddddd', $id]);
        //var_dump($id);die;
        DB::table('docexa_appointment_sku_details')->insertGetId(['start_booking_time' => $start_booking_time, 'end_booking_time' => $end_booking_time, 'slot_size' => $slot_size, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'booking_id' => $id, 'esteblishment_user_map_sku_id' => $data['sku_id'], 'cost' => $fee, 'payable_price' => $fee, 'discount' => 0, 'booking_type' => $skudata->booking_type]);

        $bookinggIdmd5 = md5($id);

        DB::table('docexa_patient_booking_details')->where('booking_id', $id)->limit(1)->update(array('bookingidmd5' => $bookinggIdmd5));
        $tabdata = $this->getappointment(null, $bookinggIdmd5);

        $res = new payment();
        $paymentdata = $res->createpayment($bookinggIdmd5);

        Log::info(['payyyy', $paymentdata]);

        $urlArray = parse_url($tabdata['appointment'][0]->handle, PHP_URL_PATH);
        $segments = explode('/', $urlArray);
        $numSegments = count($segments);
        $currentSegment = $segments[$numSegments - 1];
        $c = new Controller();
        Log::info("status", [$status, $payment_mode, $created_by]);

        if ($status == 2 && $payment_mode == 'byPatient' && $created_by == 'doctor') {
            $notificationdata = [
                'template' => 'appt_scheduled_created_notification',
                'handle' => $currentSegment,
                'appointment_id' => $bookinggIdmd5
            ];
            $c->sendNotification($notificationdata);
        } else if ($status == 1 && $payment_mode == 'byPatient' && $created_by == 'doctor') {
            $notificationdata = [
                'template' => 'appt_unscheduled_created_notification',
                'handle' => $currentSegment,
                'appointment_id' => $bookinggIdmd5
            ];
            $c->sendNotification($notificationdata);
        } else if ($status == 2 && $payment_mode == 'direct') {
            $notificationdata = [
                'template' => 'appt_scheduled_created_notification_pay_outside_DCX',
                'handle' => $currentSegment,
                'appointment_id' => $bookinggIdmd5
            ];
            $c->sendNotification($notificationdata);
        } else if ($status == 2 && $payment_mode == 'free') {
            $notificationdata = [
                'template' => 'appt_scheduled_created_notification_FREE',
                'handle' => $currentSegment,
                'appointment_id' => $bookinggIdmd5
            ];
            $c->sendNotification($notificationdata);
        }
        Log::info(['aptt' => $tabdata['appointment'], 'payment' => $paymentdata]);

        return ['appointment' => $tabdata['appointment'], 'payment' => $paymentdata, "appointment_id" => $id];
    }
    public function createhospitalappointment($hospitalID, $esteblishmentusermapID, $request)
    {
        $data = $request->input();
        //var_dump($data);die;

        Log::info([$data]);
        if (!isset($data['age'])) {
            $data['age'] = 0;
        }
        if (!isset($data['gender'])) {
            $data['gender'] = 0;
        }
        if (!isset($data['schedule_remark'])) {
            $data['schedule_remark'] = '';
        }

        $medicaldata = Medicalestablishmentsmedicalusermap::find($data['user_map_id'])->get()->first();


        $check = Patientmaster::where('mobile_no', $data['patient_mobile_no'])->first();
        if (isset($check->patient_id)) {
            $patientdata = $check;
        } else {
            $patientdata = new Patientmaster();
            $patientdata->patient_name = $data['patient_name'];
            $patientdata->email_id = $data['email'];
            $patientdata->mobile_no = $data['patient_mobile_no'];
            $patientdata->username = $data['patient_mobile_no'];
            $patientdata->age = $data['age'];
            $patientdata->gender = $data['gender'];
            $patientdata->save();
        }
        $skuobj = new Skumaster();
        $skudata = $skuobj->gethospitalskudetailsbyid($data['user_map_id'], $data['sku_id'], $hospitalID);

        if (isset($data['payment_amount'])) {
            $fee = $data['payment_amount'];
        } else {
            $fee = $skudata->fee;
        }
        if (isset($data['slot_size']) && $data['slot_size'] != '')
            $slot_size = $data['slot_size'];
        else
            $slot_size = (int) $_ENV['SLOT_SIZE'];
        if (isset($data['schedule_date']) && $data['schedule_date'] != null) {
            $status = 2;
            $date = date('Y-m-d', strtotime($data['schedule_date']));
            $time = date('H:i', strtotime($data['schedule_time']));
            $start_booking_time = date('Y-m-d H:i:s', strtotime($data['schedule_date'] . " " . $data['schedule_time']));
            $end_booking_time = date('Y-m-d H:i:s', strtotime('+' . $slot_size . ' minutes', strtotime($data['schedule_date'] . " " . $data['schedule_time'])));
        } else {
            $status = 1;
            $start_booking_time = null;
            $end_booking_time = null;
            $date = null;
            $time = null;
        }
        if (isset($data['payment_mode'])) {
            $payment_mode = $data['payment_mode'];
            $created_by = 'doctor';
        } else {
            $payment_mode = "byPatient";
            $created_by = 'patient';
        }

        if (isset($data['status']) && $data['status'] != null) {
            $status = $data['status'];
        }

        $id = DB::table('docexa_patient_booking_details')->insertGetId(['gender' => $data['gender'], 'age' => $data['age'], 'patient_name' => $data['patient_name'], 'mobile_no' => $data['patient_mobile_no'], 'email_id' => $data['email'], 'date' => $date, 'start_time' => $time, 'payment_mode' => $payment_mode, 'created_by' => $created_by, 'status' => $status, 'schedule_remark' => $data['schedule_remark'], 'created_date' => date('Y-m-d H:i:s'), 'user_map_id' => $data['user_map_id'], 'cost' => $fee, 'age' => $data['age'], 'patient_id' => $patientdata->patient_id, 'doctor_id' => $medicaldata->medical_user_id, 'hospital_id' => $hospitalID]);
        //var_dump($id);die;
        DB::table('docexa_appointment_sku_details')->insertGetId(['start_booking_time' => $start_booking_time, 'end_booking_time' => $end_booking_time, 'slot_size' => $slot_size, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'booking_id' => $id, 'esteblishment_user_map_sku_id' => $data['sku_id'], 'cost' => $fee, 'payable_price' => $fee, 'discount' => 0, 'booking_type' => $skudata->booking_type]);


        DB::table('docexa_patient_booking_details')->where('booking_id', $id)->limit(1)->update(array('bookingidmd5' => md5($id)));
        $tabdata = $this->getappointment(null, md5($id));

        $res = new payment();
        $paymentdata = $res->createpayment(md5($id));

        $urlArray = parse_url($tabdata['appointment'][0]->handle, PHP_URL_PATH);
        $segments = explode('/', $urlArray);
        $numSegments = count($segments);
        $currentSegment = $segments[$numSegments - 1];
        $c = new Controller();
        Log::info("status", [$status, $payment_mode, $created_by]);
        if ($status == 2 && $payment_mode == 'byPatient' && $created_by == 'doctor') {
            $notificationdata = [
                'template' => 'appt_scheduled_created_notification',
                'handle' => $currentSegment,
                'appointment_id' => md5($id)
            ];
            $c->sendNotification($notificationdata);
        } else if ($status == 1 && $payment_mode == 'byPatient' && $created_by == 'doctor') {
            $notificationdata = [
                'template' => 'appt_unscheduled_created_notification',
                'handle' => $currentSegment,
                'appointment_id' => md5($id)
            ];
            $c->sendNotification($notificationdata);
        } else if ($status == 2 && $payment_mode == 'direct') {
            $notificationdata = [
                'template' => 'appt_scheduled_created_notification_pay_outside_DCX',
                'handle' => $currentSegment,
                'appointment_id' => md5($id)
            ];
            $c->sendNotification($notificationdata);
        } else if ($status == 2 && $payment_mode == 'free') {
            $notificationdata = [
                'template' => 'appt_scheduled_created_notification_FREE',
                'handle' => $currentSegment,
                'appointment_id' => md5($id)
            ];
            $c->sendNotification($notificationdata);
        }
        $templatedata = [
            'template' => 'appointment_requested',
            "booking_id" => $id,
            "type" => 'appointment'
        ];
        $c = new Controller();
        $c->sendNotificationBooking($templatedata);
        return ['appointment' => $tabdata['appointment'], 'payment' => $paymentdata];
    }
    public function createappointmentv2($request)
    {
        $data = $request->input();
        Log::info("pfizer", [$data]);
        $data['age'] = 0;
        if (!isset($data['location'])) {
            $data['location'] = 'NA';
        }
        if (!isset($data['latlong'])) {
            $data['latlong'] = 'NA';
        }
        if (!isset($data['city'])) {
            $data['city'] = 'NA';
        }
        $check = Patientmasterphizer::where('mobile_no', $data['patient_mobile_no'])->first();

        if (isset($check->patient_id)) {
            $patientdata = $check;
        } else {
            $patientdata = new Patientmasterphizer();
            $patientdata->patient_name = $data['patient_name'];
            $patientdata->city = $data['city'];
            $patientdata->mobile_no = $data['patient_mobile_no'];
            $patientdata->username = $data['patient_mobile_no'];
            $patientdata->save();
        }
        $id = DB::table('docexa_patient_booking_details_phizer')->insertGetId(['status' => 1, 'created_date' => date('Y-m-d H:i:s'), 'cost' => '100', 'latlong' => $data['latlong'], 'location' => $data['location'], 'age' => $data['age'], 'patient_id' => $patientdata->patient_id, 'doctor_id' => null]);
        DB::table('docexa_patient_booking_details_phizer')->where('booking_id', $id)->limit(1)->update(array('bookingidmd5' => md5($id)));
        $tabdata = $this->getappointmentv3(null, md5($id));
        $res = new payment();
        $paymentdata = $res->createpaymentv2(md5($id));
        return ['appointment' => $tabdata['appointment'], 'payment' => $paymentdata];
    }
    public function updatestatus($appt_id)
    {
        error_log("handle process");
        // DB::table('docexa_patient_booking_details')->where('bookingidmd5', $appt_id)->whereNotIn('status', [3, 6, 4])->limit(1)->update(array('status' => 5));
    }
    public function updatehospitalstatus($appt_id)
    {
        error_log("handle process");
        // DB::table('docexa_patient_booking_details')->where('bookingidmd5', $appt_id)->whereNotIn('status', [3, 6, 4])->limit(1)->update(array('status' => 5));
    }

    // select * from staging.docexa_patient_booking_details as booking join staging.docexa_patient_credit_history as credit on booking.booking_id = credit.booking_id where user_map_id=66059;


    public function getTranscationHistory($user_map_id, $from_date, $to_date)
    {
        $results = AppointmentDetails::whereBetween('docexa_patient_credit_history.transaction_date', [$from_date, $to_date])
            ->join('docexa_patient_credit_history', 'docexa_patient_booking_details.booking_id', '=', 'docexa_patient_credit_history.booking_id')
            ->where('user_map_id', $user_map_id)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $data = [];
        $refundData = [];
        $transcationhistory = [];
        $refundHistory = [];
        if (count($results) > 0) {
            foreach ($results as $result) {
                if ($result->transaction_type) {
                    $refundData = [
                        'patient_name' => $result->patient_name,
                        'mobile_no' => $result->mobile_no,
                        'transaction_type' => $result->transaction_type,
                        'refund_date' => $result->refund_date,
                        'refund_amount' => $result->refund_amount,
                        'booking_id' => $result->booking_id,
                    ];
                    if ($refundData) {
                        $refundHistory[] = $refundData;
                    }
                } else {
                    $data = [
                        'patient_name' => $result->patient_name,
                        'transaction_date' => $result->created_date,
                        'credit_point' => $result->credit_point,
                        'transaction_type' => $result->transaction_type,
                        'payment_mode' => $result->payment_mode,
                        'booking_id' => $result->booking_id,
                        'cost' => $result->cost,
                        'mobile_no' => $result->mobile_no,
                    ];
                    if ($data) {
                        $transcationhistory[] = $data;
                    }
                }
            }

            //  last 90 days earnings
            $start_date = Carbon::now();
            $end_date = $start_date->copy()->subDays(90);

            $results = AppointmentDetails::join('docexa_patient_credit_history', 'docexa_patient_booking_details.booking_id', '=', 'docexa_patient_credit_history.booking_id')
                ->whereBetween('docexa_patient_credit_history.transaction_date', [$end_date, $start_date])
                ->where('user_map_id', $user_map_id)
                ->orderBy('transaction_date', 'desc')
                ->selectRaw('SUM(CASE WHEN payment_mode = "direct" OR payment_mode = "bypatient" THEN credit_point ELSE 0 END) AS total_credit_points')
                ->selectRaw('SUM(CASE WHEN payment_mode = "direct" OR payment_mode = "bypatient" THEN refund_amount ELSE 0 END) AS refund_amount')
                ->first();

            $totalEarnings = $results->total_credit_points;
            $refund = $results->refund_amount;
            $savings = $totalEarnings - $refund;

            $withdrawl = new WithdraModel();
            $withdrawldata = $withdrawl->getwithdrawDetails($user_map_id);

            $totalWithdrawalAmount = WithdraModel::where('user_map_id', $user_map_id)
                ->where('status', 3)
                ->sum('withdrawl_amount');

            $history = [
                'transcations' => $transcationhistory,
                'refundHistory' => $refundHistory,
                'total_earnings' => $totalEarnings,
                'total_balance' => $savings,
                'total_withdrawl_amount' => $totalWithdrawalAmount,
                'refund_amount' => $refund,
                'withdrawl_data' => $withdrawldata,
            ];
            return $history;
        } else {
            return false;
        }
    }


    public function getTodaysAppointment($request)
    {
        $input = $request->all();
        $paymemtFlag = DB::table('docexa_doctor_precription_data')->where('user_map_id', $input['user_map_id'])->first()->payment_flag;

        if ($paymemtFlag == 1 || $paymemtFlag == 3) {
            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.date', '=', $input['date'])
                ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
                // ->where('docexa_patient_booking_details.status', '!=', 3)
                // ->where('docexa_patient_booking_details.status', '!=', 6)
                // ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    // $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                ->get();



            foreach ($todaystabdata as $key => $data) {

                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

                Log::info(['today' => $data->booking_id]);
                $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

                $data = [];
                if (count($prescriptionArray) > 0) {
                    foreach ($prescriptionArray as $pres) {
                        $data1[] = $pres;
                        Log::info(['pres' => $data1]);
                    }
                } else {
                    $data1 = [];
                }
                $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

                if (count($todaystabdata) > 0) {
                    if (count($data1) > 0) {
                        $todaystabdata[$key]->prescription_flag = true;
                    } else {
                        $todaystabdata[$key]->prescription_flag = false;
                    }
                }
            }



            return $todaystabdata;
        } elseif ($paymemtFlag == 2) {
            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.date', '=', $input['date'])
                ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
                // ->where('docexa_patient_booking_details.status', '!=', 3)
                // ->where('docexa_patient_booking_details.status', '!=', 6)
                // ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                ->get();



            foreach ($todaystabdata as $key => $data) {

                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

                Log::info(['today' => $data->booking_id]);
                $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

                $data = [];
                if (count($prescriptionArray) > 0) {
                    foreach ($prescriptionArray as $pres) {
                        $data1[] = $pres;
                        Log::info(['pres' => $data1]);
                    }
                } else {
                    $data1 = [];
                }
                $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

                if (count($todaystabdata) > 0) {
                    if (count($data1) > 0) {
                        $todaystabdata[$key]->prescription_flag = true;
                    } else {
                        $todaystabdata[$key]->prescription_flag = false;
                    }
                }
            }
            return $todaystabdata;
        }
    }

    public function scheduleappointmentv9($request)
    {
        $data = $request->input();
        if ($data['scheduletimestamp'] != '') {
            $date = date('Y-m-d', strtotime($data['scheduletimestamp']));
            $time = date('H:i', strtotime($data['scheduletimestamp']));
            $data['scheduletimestamp'] = date('Y-m-d H:i:s', strtotime($data['scheduletimestamp']));
            $end_booking_time = date('Y-m-d H:i:s', strtotime('+' . $data['slot_size'] . ' minutes', strtotime($data['scheduletimestamp'])));
        } else {
            $data['scheduletimestamp'] = null;
            $end_booking_time = null;
            $date = null;
            $time = null;
        }
        if ($data['slot_size'] == '')
            $data['slot_size'] = 0;

        Log::info(['update']);
        DB::table('docexa_patient_booking_details')->where('bookingidmd5', $data['bookingID'])->limit(1)->update(array('cancellation_reason' => $data['remark'], 'status' => $data['status'], 'date' => $date, 'start_time' => $time));
        Log::info("afterupdate");

        $booking_id = DB::table('docexa_patient_booking_details')->where('bookingidmd5', $data['bookingID'])->get()->first()->booking_id;
        DB::table('docexa_appointment_sku_details')->where('booking_id', $booking_id)->limit(1)->update(array('start_booking_time' => $data['scheduletimestamp'], 'end_booking_time' => $end_booking_time, 'slot_size' => $data['slot_size']));
        $tabdata = $this->getappointment(null, $data['bookingID']);

        $urlArray = parse_url($tabdata['appointment'][0]->handle, PHP_URL_PATH);
        $segments = explode('/', $urlArray);
        $numSegments = count($segments);
        $currentSegment = $segments[$numSegments - 1];
        // var_dump($currentSegment);die;
        $c = new Controller();
        if ($data['status'] == 2) {
            $start = Carbon::parse($data['scheduletimestamp']);
            $start->setTimezone('Asia/Kolkata');
            $us = new updateStatus($data['bookingID']);
            $us->apptID();
            dispatch((new updateStatus($data['bookingID']))->delay($start->addMinutes($_ENV['QUEUE_TIME'])));
            $notificationdata = [
                'template' => 'appointment_accepted_notifier',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 3) {
            $notificationdata = [
                'template' => 'doc_appointment_cancelled_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_cancelled_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 4 && $tabdata['appointment'][0]->payment_mode == '') {
            $notificationdata = [
                'template' => 'doc_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 4 && $tabdata['appointment'][0]->payment_mode == 'byPatient') {
            $notificationdata = [
                'template' => 'doc_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 4 && $tabdata['appointment'][0]->payment_mode == 'direct') {
            $notificationdata = [
                'template' => 'appointment_completed_intimation_pay_outside_DCX',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 4 && $tabdata['appointment'][0]->payment_mode == 'free') {
            $notificationdata = [
                'template' => 'appointment_completed_intimation_FREE',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_completed_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];

            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 7) {
            $start = Carbon::parse($data['scheduletimestamp']);
            $start->setTimezone('Asia/Kolkata');
            $us = new updateStatus($data['bookingID']);
            $us->apptID();
            dispatch((new updateStatus($data['bookingID']))->delay($start->addMinutes($_ENV['QUEUE_TIME'])));
            $notificationdata = [
                'template' => 'appointment_rescheduled_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
        } else if ($data['status'] == 6) {
            $notificationdata = [
                'template' => 'doctor_appointment_request_rejected_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
            $notificationdata = [
                'template' => 'patient_appointment_request_rejected_intimation',
                'handle' => $currentSegment,
                'appointment_id' => $data['bookingID']
            ];
            $c->sendNotification($notificationdata);
        }
        //var_dump(json_encode($data));die;


        return $tabdata;
    }





    public function getTodaysPrescriptionSave($request)
    {
        $input = $request->all();

        $todaysDate = Carbon::now();

        $todaystabdata = DB::table('docexa_patient_booking_details')
            ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
            ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
            ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
            ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
            ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
            ->join('prescription', 'prescription.patient_id', '=', 'docexa_patient_booking_details.patient_id')

            ->select(
                'docexa_patient_booking_details.age',
                'docexa_patient_details.gender',
                'docexa_patient_booking_details.booking_id as appt_id',
                'docexa_patient_booking_details.patient_id',
                'docexa_patient_booking_details.payment_mode',
                'docexa_patient_booking_details.cancellation_reason as reason',
                'docexa_patient_booking_details.status',
                'docexa_appointment_status_master.status_text',
                'docexa_patient_booking_details.status',
                'docexa_patient_booking_details.schedule_remark',
                'docexa_appointment_sku_details.booking_type',
                'docexa_esteblishment_user_map_sku_details.title',
                'docexa_esteblishment_user_map_sku_details.description',
                'docexa_patient_booking_details.bookingidmd5 as booking_id',
                'docexa_patient_booking_details.created_date',
                'docexa_patient_booking_details.date',
                'docexa_patient_booking_details.start_time',
                'docexa_patient_booking_details.patient_name',
                'docexa_patient_booking_details.email_id as email',
                'docexa_patient_booking_details.mobile_no',
                'docexa_patient_booking_details.cost',
                'docexa_patient_booking_details.clinic_id',
                'prescription.created_at'

            )

            ->selectRaw('+91 as country_code')
            ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
            ->whereDate('docexa_patient_booking_details.date', '=', $todaysDate)
            ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
            ->whereDate('prescription.created_at', $todaysDate)

            // ->where('docexa_patient_booking_details.status', '!=', 3)
            // ->where('docexa_patient_booking_details.status', '!=', 6)
            // ->where('docexa_patient_booking_details.status', '!=', 4)
            ->where(function ($query) {
                $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
            })->latest('prescription.created_at')
            ->get();

        $filteredData = [];

        foreach ($todaystabdata as $key => $data) {

            $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

            Log::info(['today' => $data->booking_id]);
            $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

            $data = [];
            if (count($prescriptionArray) > 0) {
                foreach ($prescriptionArray as $pres) {
                    $data1[] = $pres;
                    Log::info(['pres' => $data1]);
                }
            } else {
                $data1 = [];
            }
            $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

            if (count($todaystabdata) > 0) {
                if (count($data1) > 0) {
                    $todaystabdata[$key]->prescription_flag = true;
                } else {
                    $todaystabdata[$key]->prescription_flag = false;
                }
            }
        }
        foreach ($todaystabdata as $data) {
            if ($data->prescription_flag === true) {
                $filteredData[] = $data;
            }
        }
        return $filteredData;
    }

    public function getallslotV3($user_map_id, $date = null)
    {
        if ($date == null) {
            $date = date('Y-m-d');
        }

        // Fetch the booked slots for the given date and user_map_id
        $response = DB::table('docexa_patient_booking_details')
            ->join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
            ->selectRaw('date_format(docexa_appointment_sku_details.start_booking_time,"%H:%i") as start_booking_time, date_format(docexa_appointment_sku_details.end_booking_time,"%H:%i") as end_booking_time')
            ->where('docexa_patient_booking_details.user_map_id', $user_map_id)
            ->where('docexa_patient_booking_details.date', '=', $date)
            ->where(function ($query) {
                $query->where("credit_history_id", "!=", null);
                $query->orWhere(function ($innerquery) {
                    $innerquery->where("payment_mode", "!=", "byPatient");
                    $innerquery->where("payment_mode", "!=", "");
                });
            })
            ->get();

        // Process each slot to add the booked count
        $slotsWithCount = $response->map(function ($slot) use ($user_map_id, $date) {
            // Count the number of appointments for this particular slot
            $count = DB::table('docexa_patient_booking_details')
                ->join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->where('docexa_patient_booking_details.user_map_id', $user_map_id)
                ->where('docexa_patient_booking_details.date', '=', $date)
                ->where(function ($query) {
                    $query->where("credit_history_id", "!=", null);
                    $query->orWhere(function ($innerquery) {
                        $innerquery->where("payment_mode", "!=", "byPatient");
                        $innerquery->where("payment_mode", "!=", "");
                    });
                })
                ->whereRaw('date_format(docexa_appointment_sku_details.start_booking_time,"%H:%i") = ?', [$slot->start_booking_time])
                ->whereRaw('date_format(docexa_appointment_sku_details.end_booking_time,"%H:%i") = ?', [$slot->end_booking_time])
                ->count();

            // Add the count to the slot object
            $slot->booked_count = $count;
            return $slot;
        });

        // Return the slots with the added booked count
        return $slotsWithCount;
    }

    public function createappointmentV4($request)
    {

        $data = $request->input();
        // Log::info([$data]);
        if (!isset($data['age'])) {
            $data['age'] = 0;
        }
        if (!isset($data['gender'])) {
            $data['gender'] = 0;
        }
        if (!isset($data['schedule_remark'])) {
            $data['schedule_remark'] = '';
        }
        // Log::info($data['gender']);

        $medicaldata = Medicalestablishmentsmedicalusermap::find($data['user_map_id'])->get()->first();

        $skuobj = new Skumaster();
        // Log::Info(['skuid', $data['sku_id']]);
        $skudata = $skuobj->getskudetailsbyid($data['user_map_id'], $data['sku_id']);
        // Log::info(['skudataa', $skudata]);

        if (isset($data['payment_amount'])) {
            // Log::info(['1']);
            $fee = $data['payment_amount'];
        } else {
            // Log::info(['2']);

            $fee = $skudata->fee ?? "0";
        }
        if (isset($data['slot_size']) && $data['slot_size'] != '') {
            // Log::info(['3']);
            $slot_size = $data['slot_size'];
        } else {
            $slot_size = (int) $_ENV['SLOT_SIZE'];
            // Log::info(['4']);
        }
        if (isset($data['schedule_date']) && $data['schedule_date'] != null) {
            // Log::info(['status 2']);
            $status = 2;
            $date = date('Y-m-d', strtotime($data['schedule_date']));
            $time = date('H:i', strtotime($data['schedule_time']));
            $start_booking_time = date('Y-m-d H:i:s', strtotime($data['schedule_date'] . " " . $data['schedule_time']));
            $end_booking_time = date('Y-m-d H:i:s', strtotime('+' . $slot_size . ' minutes', strtotime($data['schedule_date'] . " " . $data['schedule_time'])));
        } else {
            // Log::info(['statuschabged to 1']);
            $status = 1;
            $start_booking_time = null;
            $end_booking_time = null;
            $date = null;
            $time = null;
        }
        // dd($start_booking_time, $end_booking_time, $slot_size, $data['schedule_date'], $data['schedule_time']);
        // Log::info(['payyyyyyyyyyymentMode', isset($data['payment_mode'])]);
        if (isset($data['payment_mode'])) {
            $payment_mode = $data['payment_mode'];
            $created_by = 'doctor';
        } else {
            $payment_mode = "byPatient";
            $created_by = 'patient';
        }

        if (isset($data['source'])) {
            $source = $data['source'];
        } else {
            $source = 'NA';
        }

        $id = DB::table('docexa_patient_booking_details')->insertGetId([
            'source' => $source,
            'gender' => $data['gender'],
            'age' => $data['age'],
            'patient_name' => $data['patient_name'],
            'mobile_no' => $data['patient_mobile_no'] ?? null,
            'email_id' => $data['email'],
            'date' => $date,
            'start_time' => $time,
            'payment_mode' => $payment_mode,
            'created_by' => $created_by,
            'status' => $status,
            'schedule_remark' => $data['schedule_remark'],
            'created_date' => date('Y-m-d H:i:s'),
            'user_map_id' => $data['user_map_id'],
            'cost' => $fee,
            'patient_id' => $data['patient_id'],
            'doctor_id' => $medicaldata->medical_user_id,
            'clinic_id' => $data['clinic_id'],
            // 'flag' =>  array_key_exists('flag', $data) ?( $data['flag'] ?  $data['flag'] : null) :null


        ]);
        //  Log::info(['id', DB::table('docexa_patient_booking_details')->where('id', $iudy)])
        // Log::info(['idddddddddddd', $id]);
        //var_dump($id);die;
        DB::table('docexa_appointment_sku_details')->insertGetId(['start_booking_time' => $start_booking_time, 'end_booking_time' => $end_booking_time, 'slot_size' => $slot_size, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'booking_id' => $id, 'esteblishment_user_map_sku_id' => $data['sku_id'], 'cost' => $fee, 'payable_price' => $fee, 'discount' => 0, 'booking_type' => $skudata->booking_type ?? null]);

        $bookinggIdmd5 = md5($id);

        DB::table('docexa_patient_booking_details')->where('booking_id', $id)->limit(1)->update(array('bookingidmd5' => $bookinggIdmd5));
        $tabdata = $this->getappointment(null, $bookinggIdmd5);

        $res = new payment();
        // $paymentdata = $res->createpayment($bookinggIdmd5);
        $paymentdata = [];

        // Log::info(['payyyy', $paymentdata]);

        $urlArray = parse_url($tabdata['appointment'][0]->handle, PHP_URL_PATH);
        $segments = explode('/', $urlArray);
        $numSegments = count($segments);
        $currentSegment = $segments[$numSegments - 1];
        $c = new Controller();
        // Log::info("status", [$status, $payment_mode, $created_by]);

        if ($status == 2 && $payment_mode == 'byPatient' && $created_by == 'doctor') {
            $notificationdata = [
                'template' => 'appt_scheduled_created_notification',
                'handle' => $currentSegment,
                'appointment_id' => $bookinggIdmd5
            ];
            $c->sendNotification($notificationdata);
        } else if ($status == 1 && $payment_mode == 'byPatient' && $created_by == 'doctor') {
            $notificationdata = [
                'template' => 'appt_unscheduled_created_notification',
                'handle' => $currentSegment,
                'appointment_id' => $bookinggIdmd5
            ];
            $c->sendNotification($notificationdata);
        } else if ($status == 2 && $payment_mode == 'direct') {
            $notificationdata = [
                'template' => 'appt_scheduled_created_notification_pay_outside_DCX',
                'handle' => $currentSegment,
                'appointment_id' => $bookinggIdmd5
            ];
            $c->sendNotification($notificationdata);
        } else if ($status == 2 && $payment_mode == 'free') {
            $notificationdata = [
                'template' => 'appt_scheduled_created_notification_FREE',
                'handle' => $currentSegment,
                'appointment_id' => $bookinggIdmd5
            ];
            $c->sendNotification($notificationdata);
        }
        // Log::info(['aptt' => $tabdata['appointment'], 'payment' => $paymentdata]);

        return ['appointment' => $tabdata['appointment'], 'payment' => $paymentdata, "appointment_id" => $id];
    }




    public function getCancelledApt($request): mixed
    {
        $input = $request->all();
        $paymemtFlag = DB::table('docexa_doctor_precription_data')->where('user_map_id', $input['user_map_id'])->first()->payment_flag;

        if ($paymemtFlag == 1 || $paymemtFlag == 3) {
            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.date', '=', $input['date'])
                ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
                ->where('docexa_patient_booking_details.status', 3)
                // ->where('docexa_patient_booking_details.status', '!=', 6)
                // ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    // $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                ->get();



            foreach ($todaystabdata as $key => $data) {

                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

                Log::info(['today' => $data->booking_id]);
                $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

                $data = [];
                if (count($prescriptionArray) > 0) {
                    foreach ($prescriptionArray as $pres) {
                        $data1[] = $pres;
                        Log::info(['pres' => $data1]);
                    }
                } else {
                    $data1 = [];
                }
                $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

                if (count($todaystabdata) > 0) {
                    if (count($data1) > 0) {
                        $todaystabdata[$key]->prescription_flag = true;
                    } else {
                        $todaystabdata[$key]->prescription_flag = false;
                    }
                }
            }
            return $todaystabdata;
        } elseif ($paymemtFlag == 2) {
            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.date', '=', $input['date'])
                ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
                // ->where('docexa_patient_booking_details.status', '!=', 3)
                // ->where('docexa_patient_booking_details.status', '!=', 6)
                // ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                ->get();



            foreach ($todaystabdata as $key => $data) {

                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

                Log::info(['today' => $data->booking_id]);
                $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

                $data = [];
                if (count($prescriptionArray) > 0) {
                    foreach ($prescriptionArray as $pres) {
                        $data1[] = $pres;
                        Log::info(['pres' => $data1]);
                    }
                } else {
                    $data1 = [];
                }
                $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

                if (count($todaystabdata) > 0) {
                    if (count($data1) > 0) {
                        $todaystabdata[$key]->prescription_flag = true;
                    } else {
                        $todaystabdata[$key]->prescription_flag = false;
                    }
                }
            }
            return $todaystabdata;
        }
    }

    public function getTodaysAppointmentV1($request)
    {
        $input = $request->all();
        $paymemtFlag = DB::table('docexa_doctor_precription_data')->where('user_map_id', $input['user_map_id'])->first()->payment_flag;

        if ($paymemtFlag == 1 || $paymemtFlag == 3) {
            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.date', '=', $input['date'])
                ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
                ->where('docexa_patient_booking_details.status', '!=', 3)
                ->where('docexa_patient_booking_details.status', '!=', 6)
                ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    // $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                ->get();



            foreach ($todaystabdata as $key => $data) {

                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

                Log::info(['today' => $data->booking_id]);
                $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

                $data = [];
                if (count($prescriptionArray) > 0) {
                    foreach ($prescriptionArray as $pres) {
                        $data1[] = $pres;
                        Log::info(['pres' => $data1]);
                    }
                } else {
                    $data1 = [];
                }
                $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

                if (count($todaystabdata) > 0) {
                    if (count($data1) > 0) {
                        $todaystabdata[$key]->prescription_flag = true;
                    } else {
                        $todaystabdata[$key]->prescription_flag = false;
                    }
                }
            }
            return $todaystabdata;
        } elseif ($paymemtFlag == 2) {
            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.date', '=', $input['date'])
                ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
                ->where('docexa_patient_booking_details.status', '!=', 3)
                ->where('docexa_patient_booking_details.status', '!=', 6)
                ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                ->get();



            foreach ($todaystabdata as $key => $data) {

                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

                Log::info(['today' => $data->booking_id]);
                $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

                $data = [];
                if (count($prescriptionArray) > 0) {
                    foreach ($prescriptionArray as $pres) {
                        $data1[] = $pres;
                        Log::info(['pres' => $data1]);
                    }
                } else {
                    $data1 = [];
                }
                $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

                if (count($todaystabdata) > 0) {
                    if (count($data1) > 0) {
                        $todaystabdata[$key]->prescription_flag = true;
                    } else {
                        $todaystabdata[$key]->prescription_flag = false;
                    }
                }
            }
            return $todaystabdata;
        }
    }

    public function getTodaySearchAppointmentV1($request, $key, $value)
    {
        $input = $request->all();
        $paymemtFlag = DB::table('docexa_doctor_precription_data')->where('user_map_id', $input['user_map_id'])->first()->payment_flag;

        if ($paymemtFlag == 1 || $paymemtFlag == 3) {
            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.date', '=', $input['date'])
                ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
                // ->where('docexa_patient_booking_details.status', '!=', 3)
                // ->where('docexa_patient_booking_details.status', '!=', 6)
                ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    // $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date');

            $todaystabdata = $todaystabdata->where('docexa_patient_details.patient_name', 'LIKE', '%' . $value . '%')->get();


            foreach ($todaystabdata as $key => $data) {

                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

                Log::info(['today' => $data->booking_id]);
                $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

                $data = [];
                if (count($prescriptionArray) > 0) {
                    foreach ($prescriptionArray as $pres) {
                        $data1[] = $pres;
                        Log::info(['pres' => $data1]);
                    }
                } else {
                    $data1 = [];
                }
                $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

                if (count($todaystabdata) > 0) {
                    if (count($data1) > 0) {
                        $todaystabdata[$key]->prescription_flag = true;
                    } else {
                        $todaystabdata[$key]->prescription_flag = false;
                    }
                }
            }
            return $todaystabdata;
        } elseif ($paymemtFlag == 2) {
            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.date', '=', $input['date'])
                ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
                // ->where('docexa_patient_booking_details.status', '!=', 3)
                // ->where('docexa_patient_booking_details.status', '!=', 6)
                // ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                ->get();



            foreach ($todaystabdata as $key => $data) {

                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

                Log::info(['today' => $data->booking_id]);
                $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

                $data = [];
                if (count($prescriptionArray) > 0) {
                    foreach ($prescriptionArray as $pres) {
                        $data1[] = $pres;
                        Log::info(['pres' => $data1]);
                    }
                } else {
                    $data1 = [];
                }
                $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

                if (count($todaystabdata) > 0) {
                    if (count($data1) > 0) {
                        $todaystabdata[$key]->prescription_flag = true;
                    } else {
                        $todaystabdata[$key]->prescription_flag = false;
                    }
                }
            }
            return $todaystabdata;
        }
    }

    public function getappointmentupdated($request, $bookingID = 0)
    {
        //var_dump($bookingID,$bookingID != 0);die;
        if ($bookingID != 0 || $request == null) {
            $a = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_hospital_sku_details', 'docexa_esteblishment_user_map_hospital_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.user_map_id', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.credit_history_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.booking_id as book_id', 'docexa_patient_booking_details.doctor_id', 'docexa_esteblishment_user_map_hospital_sku_details.id as sku_id', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_hospital_sku_details.title', 'docexa_esteblishment_user_map_hospital_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle,'/appointment/','" . $bookingID . "') as appointment_url")
                ->where('docexa_patient_booking_details.bookingidmd5', $bookingID);
            $tabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.source', 'docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.user_map_id', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.credit_history_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.booking_id as book_id', 'docexa_patient_booking_details.doctor_id', 'docexa_esteblishment_user_map_sku_details.id as sku_id', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle,'/appointment/','" . $bookingID . "') as appointment_url")
                ->where('docexa_patient_booking_details.bookingidmd5', $bookingID)
                ->groupBy('docexa_patient_booking_details.bookingidmd5')
                //->union($a)
                ->get();
            $timingArray = DB::table('docexa_appointment_sku_details')->whereRaw("md5(booking_id) = ?", [$bookingID])->get();
            // $prescriptionArray = PrescriptionData::whereRaw("md5(booking_id) = ?", [$bookingID])->get();
            $prescriptionArray = PrescriptionData::where('booking_id', $bookingID)->get();

            Log::info(['ttttttttttttt', $tabdata]);

            $data = [];
            foreach ($timingArray as $timing) {
                $data[] = ['start_booking_time' => $timing->start_booking_time, 'end_booking_time' => $timing->end_booking_time];
            }

            if (count($prescriptionArray) > 0) {
                foreach ($prescriptionArray as $pres) {
                    $data1[] = $pres;
                }
            } else {
                $data1 = [];
            }

            if (count($tabdata) > 0) {
                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $tabdata[0]->user_map_id)->where('id', $tabdata[0]->clinic_id)->first();
            }
            if (count($tabdata) > 0) {
                $tabdata[0]->timing_details = $data;
                if (count($data1) > 0) {

                    $tabdata[0]->prescription_flag = true;
                } else {
                    $tabdata[0]->prescription_flag = false;
                }
                if (count($data1) > 0) {
                    $url = $_ENV['APP_URL'] . '/api/v3/prescription/view/' . $tabdata[0]->user_map_id . '/' . $tabdata[0]->patient_id . '/' . $bookingID;

                    $tabdata[0]->prescription_details = [
                        "urls" => $url
                    ];
                } else {
                    $tabdata[0]->prescription_details = [
                        "urls" => ''
                    ];
                }
                Log::info(['url of the precription' => $tabdata[0]->prescription_details]);
                // $tabdata[0]->payment_url = Controller::urlshorten($_ENV['PAYMENT_URL_V1'] . $bookingID . '/pay');
                $tabdata[0]->payment_url = $_ENV['APP_URL'] . '/api/v3/payment/' . $bookingID . '/pay';
                $tabdata[0]->appointment_url = Controller::urlshorten($tabdata[0]->appointment_url);
                // $tabdata[0]->appointment_url = $tabdata[0]->appointment_url;

                $tabdata[0]->created_date = date('M d, Y h:i A', strtotime($tabdata[0]->created_date));
                $tabdata[0]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                $response = [
                    'appointment' => $tabdata
                ];
            } else {
                $response = [
                    'appointment' => $tabdata,
                    'msg' => "no record found"
                ];
            }
            return $response;
        } else {
            $data = $request->input();
            $a = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_hospital_sku_details', 'docexa_esteblishment_user_map_hospital_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_hospital_sku_details.title', 'docexa_esteblishment_user_map_hospital_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->where('docexa_patient_booking_details.date', null)
                ->whereIn('docexa_patient_booking_details.status', [1])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })
                ->latest('docexa_patient_booking_details.created_date');
            $unscheduletabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->where('docexa_patient_booking_details.date', null)
                ->whereIn('docexa_patient_booking_details.status', [1])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })
                ->latest('docexa_patient_booking_details.created_date')
                //->union($a) 
                ->get();
            $b = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_hospital_sku_details', 'docexa_esteblishment_user_map_hospital_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_hospital_sku_details.title', 'docexa_esteblishment_user_map_hospital_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->where('docexa_patient_booking_details.date', '=', date('Y-m-d'))
                ->where('docexa_patient_booking_details.status', '!=', 3)
                ->where('docexa_patient_booking_details.status', '!=', 6)
                ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date');

            // $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $unscheduletabdata[0]->user_map_id)->where('id',$unscheduletabdata[0]->clinic_id)->first();
            // $unscheduletabdata[0]->clinic_name =  $clinicName ?$clinicName->clinic_name  :null;

            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->where('docexa_patient_booking_details.date', '=', date('Y-m-d'))
                ->where('docexa_patient_booking_details.status', '!=', 3)
                ->where('docexa_patient_booking_details.status', '!=', 6)
                ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                //->union($b) 
                ->get();


            $c = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_hospital_sku_details', 'docexa_esteblishment_user_map_hospital_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_hospital_sku_details.title', 'docexa_esteblishment_user_map_hospital_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                // ->whereNotNull('docexa_patient_booking_details.date')
                ->whereIn('docexa_patient_booking_details.status', [4, 3, 6])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date');

            // $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $todaystabdata[0]->user_map_id)->where('id',$todaystabdata[0]->clinic_id)->first();
            // $todaystabdata[0]->clinic_name =  $clinicName ?$clinicName->clinic_name  :null;

            $pasttabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                // ->whereNotNull('docexa_patient_booking_details.date')
                ->whereIn('docexa_patient_booking_details.status', [4, 3, 6])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                //->union($c) 
                ->get();
            $d = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_hospital_sku_details', 'docexa_esteblishment_user_map_hospital_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_hospital_sku_details.title', 'docexa_esteblishment_user_map_hospital_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->whereNotNull('docexa_patient_booking_details.date')
                ->whereIn('docexa_patient_booking_details.status', [2, 5])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date');
            $upcomingtabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->whereDate('docexa_patient_booking_details.date', '>=', Carbon::parse(date('Y-m-d'))->startOfDay())
                ->whereIn('docexa_patient_booking_details.status', [1, 2, 5])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                // ->union($d) 
                ->get();

            // ->whereNotNull('docexa_patient_booking_details.date')

            if (count($unscheduletabdata) > 0) {
                foreach ($unscheduletabdata as $key => $value) {
                    $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $data['user_map_id'])->where('id', $value->clinic_id)->first();
                    $unscheduletabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                }
            }
            if (count($todaystabdata) > 0) {
                foreach ($todaystabdata as $key => $value) {
                    $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $data['user_map_id'])->where('id', $value->clinic_id)->first();
                    $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                }
            }

            if (count($pasttabdata) > 0) {
                foreach ($pasttabdata as $key => $value) {
                    $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $data['user_map_id'])->where('id', $value->clinic_id)->first();
                    $pasttabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                }
            }

            if (count($upcomingtabdata) > 0) {
                foreach ($upcomingtabdata as $key => $value) {
                    $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $data['user_map_id'])->where('id', $value->clinic_id)->first();
                    $upcomingtabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                }
            }
            $updatedPastTabData = $pasttabdata->map(function ($pastapt) use ($data) {
                // Check if a billing record exists for the specific appointment
                $exist = DB::table('billing')
                    ->where('usermap_id', $data['user_map_id'])
                    ->where('appointment_id', $pastapt->appt_id)
                    ->exists();

                // Return each past appointment with an added 'billing_status' field
                return array_merge((array) $pastapt, ['billing_status' => $exist ? 'true' : 'false']);
            });



            $response = [
                'unscheduleappointment' => $unscheduletabdata,
                'todayappointment' => $todaystabdata,
                'pastappointment' => $updatedPastTabData,
                'upcomingappointment' => $upcomingtabdata
            ];
            return $response;
        }
    }




    public function getTodaysAppointmentv2($request)
    {
        $input = $request->all();
        $paymemtFlag = DB::table('docexa_doctor_precription_data')->where('user_map_id', $input['user_map_id'])->first()->payment_flag;

        if ($paymemtFlag == 1 || $paymemtFlag == 3) {
            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_details.gender', 'docexa_patient_details.flag', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.date', '=', $input['date'])
                ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
                // ->where('docexa_patient_booking_details.status', '!=', 3)
                // ->where('docexa_patient_booking_details.status', '!=', 6)
                // ->where('docexa_patient_booking_details.status', '!=', 4)
                // ->where(function ($query) {
                //     // $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                //     $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                //     $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                //     $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                // })
                ->latest('docexa_patient_booking_details.created_date')
                ->get();



            foreach ($todaystabdata as $key => $data) {

                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

                Log::info(['today' => $data->booking_id]);
                $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

                $data = [];
                if (count($prescriptionArray) > 0) {
                    foreach ($prescriptionArray as $pres) {
                        $data1[] = $pres;
                        Log::info(['pres' => $data1]);
                    }
                } else {
                    $data1 = [];
                }
                $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

                if (count($todaystabdata) > 0) {
                    if (count($data1) > 0) {
                        $todaystabdata[$key]->prescription_flag = true;
                    } else {
                        $todaystabdata[$key]->prescription_flag = false;
                    }
                }
            }

            foreach ($todaystabdata as $key => $data) {
                $billing_array = BillingModel::where('appointment_id', $data->appt_id)->get();

                $billing_data = [];
                if (count($billing_array) > 0) {
                    foreach ($billing_array as $bill) {
                        $data2[] = $bill;

                        Log::info(['billData' => $data2]);
                    }
                } else {
                    $data2 = [];
                }
                if (count($todaystabdata) > 0) {
                    if (count($data2) > 0) {
                        $todaystabdata[$key]->billing_generated = true;
                    } else {
                        $todaystabdata[$key]->billing_generated = false;
                    }
                }
            }




            return $todaystabdata;
        } elseif ($paymemtFlag == 2) {
            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.date', '=', $input['date'])
                ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
                // ->where('docexa_patient_booking_details.status', '!=', 3)
                // ->where('docexa_patient_booking_details.status', '!=', 6)
                // ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    // $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    // $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                    // $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    // $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                ->get();



            foreach ($todaystabdata as $key => $data) {

                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

                Log::info(['today' => $data->booking_id]);
                $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

                $data = [];
                if (count($prescriptionArray) > 0) {
                    foreach ($prescriptionArray as $pres) {
                        $data1[] = $pres;
                        Log::info(['pres' => $data1]);
                    }
                } else {
                    $data1 = [];
                }
                $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

                if (count($todaystabdata) > 0) {
                    if (count($data1) > 0) {
                        $todaystabdata[$key]->prescription_flag = true;
                    } else {
                        $todaystabdata[$key]->prescription_flag = false;
                    }
                }
            }

            foreach ($todaystabdata as $key => $data) {
                $billing_array = BillingModel::where('appointment_id', $data->appt_id)->get();

                $billing_data = [];
                if (count($billing_array) > 0) {
                    foreach ($billing_array as $bill) {
                        $data2[] = $bill;

                        Log::info(['billData' => $data2]);
                    }
                } else {
                    $data2 = [];
                }
                if (count($todaystabdata) > 0) {
                    if (count($data2) > 0) {
                        $todaystabdata[$key]->billing_generated = true;
                    } else {
                        $todaystabdata[$key]->billing_generated = false;
                    }
                }
            }

            return $todaystabdata;
        }
    }

    public function getTodaysAppointmentv2ByPagination($request, $page, $limit)
    {
        $offset = ($page - 1) * $limit;

        $input = $request->all();
        $paymemtFlag = DB::table('docexa_doctor_precription_data')->where('user_map_id', $input['user_map_id'])->first()->payment_flag;

        if ($paymemtFlag == 1 || $paymemtFlag == 3) {
            $query = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.date', '=', $input['date'])
                ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
                ->where('docexa_patient_booking_details.status', '!=', 3)
                ->where('docexa_patient_booking_details.status', '!=', 6)
                ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    // $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    // $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                    // $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    // $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                });
            // ->latest('docexa_patient_booking_details.created_date')
            // ->offset($offset)->limit($limit)->get();



            if (!empty($input['key'] == 'mobile')) {
                $query->where('docexa_patient_booking_details.mobile_no', 'LIKE', '%' . $input['value'] . '%');
            }
            if (!empty($input['key'] == 'patient_name')) {
                $query->where('docexa_patient_booking_details.patient_name', 'LIKE', '%' . $input['value'] . '%');
            }

            $totalCount = $query->latest('docexa_patient_booking_details.created_date')->count();

            $todaystabdata = $query->latest('docexa_patient_booking_details.created_date')
                ->offset($offset)
                ->limit($limit)
                ->get();



            foreach ($todaystabdata as $key => $data) {

                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

                Log::info(['today' => $data->booking_id]);
                $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

                $data = [];
                if (count($prescriptionArray) > 0) {
                    foreach ($prescriptionArray as $pres) {
                        $data1[] = $pres;
                        Log::info(['pres' => $data1]);
                    }
                } else {
                    $data1 = [];
                }
                $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

                if (count($todaystabdata) > 0) {
                    if (count($data1) > 0) {
                        $todaystabdata[$key]->prescription_flag = true;
                    } else {
                        $todaystabdata[$key]->prescription_flag = false;
                    }
                }
            }

            foreach ($todaystabdata as $key => $data) {
                $billing_array = BillingModel::where('appointment_id', $data->appt_id)->get();

                $billing_data = [];
                if (count($billing_array) > 0) {
                    foreach ($billing_array as $bill) {
                        $data2[] = $bill;

                        Log::info(['billData' => $data2]);
                    }
                } else {
                    $data2 = [];
                }
                if (count($todaystabdata) > 0) {
                    if (count($data2) > 0) {
                        $todaystabdata[$key]->billing_generated = true;
                    } else {
                        $todaystabdata[$key]->billing_generated = false;
                    }
                }
            }
            $response = [
                'todaystabdata' => $todaystabdata,
                'totalCount' => $totalCount
            ];



            return $response;
        } elseif ($paymemtFlag == 2) {
            $query = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.date', '=', $input['date'])
                ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
                ->where('docexa_patient_booking_details.status', '!=', 3)
                ->where('docexa_patient_booking_details.status', '!=', 6)
                ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where(function ($query) {
                    // $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    // $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                    // $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    // $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                });
            // ->latest('docexa_patient_booking_details.created_date')
            // ->offset($offset)->limit($limit)->get();

            if (!empty($input['key'] == 'mobile')) {
                $query->where('docexa_patient_booking_details.mobile_no', 'LIKE', '%' . $input['value'] . '%');
            }
            if (!empty($input['key'] == 'patient_name')) {
                $query->where('docexa_patient_booking_details.patient_name', 'LIKE', '%' . $input['value'] . '%');
            }

            $totalCount = $query->latest('docexa_patient_booking_details.created_date')->count();

            $todaystabdata = $query->latest('docexa_patient_booking_details.created_date')
                ->offset($offset)
                ->limit($limit)
                ->get();


            foreach ($todaystabdata as $key => $data) {

                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

                Log::info(['today' => $data->booking_id]);
                $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

                $data = [];
                if (count($prescriptionArray) > 0) {
                    foreach ($prescriptionArray as $pres) {
                        $data1[] = $pres;
                        Log::info(['pres' => $data1]);
                    }
                } else {
                    $data1 = [];
                }
                $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

                if (count($todaystabdata) > 0) {
                    if (count($data1) > 0) {
                        $todaystabdata[$key]->prescription_flag = true;
                    } else {
                        $todaystabdata[$key]->prescription_flag = false;
                    }
                }
            }

            foreach ($todaystabdata as $key => $data) {
                $billing_array = BillingModel::where('appointment_id', $data->appt_id)->get();

                $billing_data = [];
                if (count($billing_array) > 0) {
                    foreach ($billing_array as $bill) {
                        $data2[] = $bill;

                        Log::info(['billData' => $data2]);
                    }
                } else {
                    $data2 = [];
                }
                if (count($todaystabdata) > 0) {
                    if (count($data2) > 0) {
                        $todaystabdata[$key]->billing_generated = true;
                    } else {
                        $todaystabdata[$key]->billing_generated = false;
                    }
                }
            }

            $response = [
                'todaystabdata' => $todaystabdata,
                'totalCount' => $totalCount
            ];
        }
    }



    public function getPastAppointmentv2ByPagination($request, $page, $limit)
    {
        $offset = ($page - 1) * $limit;

        $data = $request->input();


        $c = DB::table('docexa_patient_booking_details')
            ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
            ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
            ->Join('docexa_esteblishment_user_map_hospital_sku_details', 'docexa_esteblishment_user_map_hospital_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
            ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
            ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
            ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_hospital_sku_details.title', 'docexa_esteblishment_user_map_hospital_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
            ->selectRaw('+91 as country_code')
            ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
            ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
            // ->whereNotNull('docexa_patient_booking_details.date')
            ->whereIn('docexa_patient_booking_details.status', [4, 3, 6])
            ->where(function ($query) {
                // $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                // $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                // $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
            })->latest('docexa_patient_booking_details.created_date');


        $query = DB::table('docexa_patient_booking_details')
            ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
            ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
            ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
            ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
            ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
            ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
            ->selectRaw('+91 as country_code')
            ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
            ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
            // ->whereNotNull('docexa_patient_booking_details.date')
            ->whereIn('docexa_patient_booking_details.status', [4, 3, 6])
            ->where(function ($query) {
                // $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                // $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                // $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
            });
        // ->latest('docexa_patient_booking_details.created_date');
        //->union($c) 
        // ->get();
        // ->offset($offset)->limit($limit)->get();

        if (!empty($data['key'] == 'mobile')) {
            $query->where('docexa_patient_booking_details.mobile_no', 'LIKE', '%' . $data['value'] . '%');
        }
        if (!empty($data['key'] == 'patient_name')) {
            $query->where('docexa_patient_booking_details.patient_name', 'LIKE', '%' . $data['value'] . '%');
        }

        $totalCount = $query->latest('docexa_patient_booking_details.created_date')->count();

        $pasttabdata = $query->latest('docexa_patient_booking_details.created_date')
            ->offset($offset)
            ->limit($limit)
            ->get();


        if (count($pasttabdata) > 0) {
            foreach ($pasttabdata as $key => $value) {
                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $data['user_map_id'])->where('id', $value->clinic_id)->first();
                $pasttabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
            }
        }
        $updatedPastTabData = $pasttabdata->map(function ($pastapt) use ($data) {
            $exist = DB::table('billing')
                ->where('usermap_id', $data['user_map_id'])
                ->where('appointment_id', $pastapt->appt_id)
                ->exists();

            return array_merge((array) $pastapt, ['billing_status' => $exist ? 'true' : 'false']);
        });



        $response = [
            'pastappointment' => $updatedPastTabData,
            'totalCount' => $totalCount
        ];
        return $response;
    }

    public function getupcomingappointmentByPagination($request, $page, $limit)
    {
        $offset = ($page - 1) * $limit;

        $data = $request->input();

        $d = DB::table('docexa_patient_booking_details')
            ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
            ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
            ->Join('docexa_esteblishment_user_map_hospital_sku_details', 'docexa_esteblishment_user_map_hospital_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
            ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
            ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
            ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_hospital_sku_details.title', 'docexa_esteblishment_user_map_hospital_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
            ->selectRaw('+91 as country_code')
            ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
            ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
            ->whereNotNull('docexa_patient_booking_details.date')
            ->whereIn('docexa_patient_booking_details.status', [2, 5])
            ->where(function ($query) {
                // $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                // $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                // $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
            })->latest('docexa_patient_booking_details.created_date');
        $query = DB::table('docexa_patient_booking_details')
            ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
            ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
            ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
            ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
            ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
            ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
            ->selectRaw('+91 as country_code')
            ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
            ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
            ->whereDate('docexa_patient_booking_details.date', '>=', Carbon::parse(date('Y-m-d'))->startOfDay())
            ->whereIn('docexa_patient_booking_details.status', [1, 2, 5])
            ->where(function ($query) {
                // $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                // $query->orWhere('docexa_patient_booking_details.payment_mode', 'byPatient');
                // $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                // $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
            });
        // ->latest('docexa_patient_booking_details.created_date')
        // ->union($d) 
        // ->get();
        // ->offset($offset)->limit($limit)->get();



        if (!empty($data['key'] == 'mobile')) {
            $query->where('docexa_patient_booking_details.mobile_no', 'LIKE', '%' . $data['value'] . '%');
        }
        if (!empty($data['key'] == 'patient_name')) {
            $query->where('docexa_patient_booking_details.patient_name', 'LIKE', '%' . $data['value'] . '%');
        }

        $totalCount = $query->latest('docexa_patient_booking_details.created_date')->count();
        $upcomingtabdata = $query->latest('docexa_patient_booking_details.created_date')
            ->offset($offset)
            ->limit($limit)
            ->get();

        // ->whereNotNull('docexa_patient_booking_details.date')




        if (count($upcomingtabdata) > 0) {
            foreach ($upcomingtabdata as $key => $value) {
                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $data['user_map_id'])->where('id', $value->clinic_id)->first();
                $upcomingtabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
            }
        }




        $response = [
            'upcomingappointment' => $upcomingtabdata,
            'totalCount' => $totalCount
        ];
        return $response;
    }


    public function createAppointmentwalkinV5($request)
    {

        // age : "18 Y" clinic_id : 65840 email : null gender : 1 patient_id : 2323 patient_mobile_no : "8788676554" patient_name : "Babu Genu" payment_mode : "direct" schedule_date : "2025-04-16" schedule_remark : "" schedule_time : "11:30" sku_id : 200178 user_map_id : 70671

        $data = $request->input();
        Log::info([$data]);
        $medicaldata = Medicalestablishmentsmedicalusermap::find($data['user_map_id'])->get()->first();

        $skuobj = new Skumaster();
        $skudata = $skuobj->getskudetailsbyid($data['user_map_id'], $data['sku_id']);
        Log::info(['skudataa', $skudata]);
        $fee = $skudata->fee;
        $slot_size = (int) $_ENV['SLOT_SIZE'];

        if (isset($data['schedule_date']) && $data['schedule_date'] != null) {
            Log::info(['status 2']);
            $status = 2;
            $date = date('Y-m-d', strtotime($data['schedule_date']));
            $time = date('H:i', strtotime($data['schedule_time']));
            $start_booking_time = date('Y-m-d H:i:s', strtotime($data['schedule_date'] . " " . $data['schedule_time']));
            $end_booking_time = date('Y-m-d H:i:s', strtotime('+' . $slot_size . ' minutes', strtotime($data['schedule_date'] . " " . $data['schedule_time'])));
        } else {
            Log::info(['statuschabged to 1']);
            $status = 1;
            $start_booking_time = null;
            $end_booking_time = null;
            $date = null;
            $time = null;
        }

        $payment_mode = $data['payment_mode'];
        $created_by = 'doctor';
        $source = 'NA';


        $id = DB::table('docexa_patient_booking_details')->insertGetId([
            'source' => $source,
            'gender' => $data['gender'],
            'age' => $data['age'],
            'patient_name' => $data['patient_name'],
            'mobile_no' => $data['patient_mobile_no'],
            'email_id' => $data['email'],
            'date' => $date,
            'start_time' => $time,
            'payment_mode' => $payment_mode,
            'created_by' => $created_by,
            'status' => $status,
            'schedule_remark' => $data['schedule_remark'],
            'created_date' => date('Y-m-d H:i:s'),
            'user_map_id' => $data['user_map_id'],
            'cost' => $fee,
            'patient_id' => $data['patient_id'],
            'doctor_id' => $medicaldata->medical_user_id,
            'clinic_id' => $data['clinic_id'],


        ]);
        DB::table('docexa_appointment_sku_details')->insertGetId(['start_booking_time' => $start_booking_time, 'end_booking_time' => $end_booking_time, 'slot_size' => $slot_size, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'booking_id' => $id, 'esteblishment_user_map_sku_id' => $data['sku_id'], 'cost' => $fee, 'payable_price' => $fee, 'discount' => 0, 'booking_type' => $skudata->booking_type]);

        $bookinggIdmd5 = md5($id);
        DB::table('docexa_patient_booking_details')->where('booking_id', $id)->limit(1)->update(array('bookingidmd5' => $bookinggIdmd5));
        // $tabdata = $this->getappointment(null, $bookinggIdmd5);
        // 'appointment' => $tabdata['appointment'],




        $vitals = isset($data['vitals']) ? $data['vitals'] : [];

        foreach ($vitals as $v):
            $vital = new AssistantVital();
            $vital->vital_name = isset($v['name']) ? $v['name'] : null;
            $vital->value = isset($v['vitals']) ? $v['vitals'] : null;
            $vital->unit = isset($v['unit']) ? $v['unit'] : null;
            $vital->patient_id =  $data['patient_id'];
            $vital->user_map_id = $data['user_map_id'];
            $vital->appointment_id = $id;
            $vital->save();
        endforeach;
        $vitals = AssistantVital::where("appointment_id", $id)->orderBy('created_at', 'desc')->get();
        // return response()->json(['status' => true, "data" => $vitals, 'code' => 200], 200);

        return ["vitals" => $vitals, "appointment_id" => $id];
    }


    public function getTodaysApppointmentsForParticularPatient($request)
    {
        $input = $request->all();
        $paymemtFlag = DB::table('docexa_doctor_precription_data')->where('user_map_id', $input['user_map_id'])->first()->payment_flag;

        if ($paymemtFlag == 1 || $paymemtFlag == 3) {
            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.date', '=', $input['date'])
                ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
                ->where('docexa_patient_booking_details.status', '!=', 3)
                ->where('docexa_patient_booking_details.status', '!=', 6)
                ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where('docexa_patient_booking_details.patient_id', $input['patient_id'])
                ->where(function ($query) {
                    // $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                ->get();



            foreach ($todaystabdata as $key => $data) {

                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

                Log::info(['today' => $data->booking_id]);
                $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

                $data = [];
                if (count($prescriptionArray) > 0) {
                    foreach ($prescriptionArray as $pres) {
                        $data1[] = $pres;
                        Log::info(['pres' => $data1]);
                    }
                } else {
                    $data1 = [];
                }
                $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

                if (count($todaystabdata) > 0) {
                    if (count($data1) > 0) {
                        $todaystabdata[$key]->prescription_flag = true;
                    } else {
                        $todaystabdata[$key]->prescription_flag = false;
                    }
                }
            }
            return $todaystabdata;
        } elseif ($paymemtFlag == 2) {
            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.date', '=', $input['date'])
                ->where('docexa_patient_booking_details.user_map_id', $input['user_map_id'])
                ->where('docexa_patient_booking_details.status', '!=', 3)
                ->where('docexa_patient_booking_details.status', '!=', 6)
                ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where('docexa_patient_booking_details.patient_id', $input['patient_id'])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->where('docexa_patient_booking_details.payment_mode', 'byPatient');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                ->get();



            foreach ($todaystabdata as $key => $data) {

                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $input['user_map_id'])->where('id', $data->clinic_id)->first();

                Log::info(['today' => $data->booking_id]);
                $prescriptionArray = PrescriptionData::where('booking_id', $data->booking_id)->get();

                $data = [];
                if (count($prescriptionArray) > 0) {
                    foreach ($prescriptionArray as $pres) {
                        $data1[] = $pres;
                        Log::info(['pres' => $data1]);
                    }
                } else {
                    $data1 = [];
                }
                $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;

                if (count($todaystabdata) > 0) {
                    if (count($data1) > 0) {
                        $todaystabdata[$key]->prescription_flag = true;
                    } else {
                        $todaystabdata[$key]->prescription_flag = false;
                    }
                }
            }
            return $todaystabdata;
        }
    }

    public function listappointmentupdatedForParticularPatient($request, $bookingID = 0)
    {
        //var_dump($bookingID,$bookingID != 0);die;
        if ($bookingID != 0 || $request == null) {

            $tabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.source', 'docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.user_map_id', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.credit_history_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.booking_id as book_id', 'docexa_patient_booking_details.doctor_id', 'docexa_esteblishment_user_map_sku_details.id as sku_id', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle,'/appointment/','" . $bookingID . "') as appointment_url")
                ->where('docexa_patient_booking_details.bookingidmd5', $bookingID)
                ->where('docexa_patient_booking_details.patient_id', $request['patient_id'])
                ->groupBy('docexa_patient_booking_details.bookingidmd5')
                //->union($a)
                ->get();
            $timingArray = DB::table('docexa_appointment_sku_details')->whereRaw("md5(booking_id) = ?", [$bookingID])->get();
            // $prescriptionArray = PrescriptionData::whereRaw("md5(booking_id) = ?", [$bookingID])->get();
            $prescriptionArray = PrescriptionData::where('booking_id', $bookingID)->get();

            Log::info(['ttttttttttttt', $tabdata]);

            $data = [];
            foreach ($timingArray as $timing) {
                $data[] = ['start_booking_time' => $timing->start_booking_time, 'end_booking_time' => $timing->end_booking_time];
            }

            if (count($prescriptionArray) > 0) {
                foreach ($prescriptionArray as $pres) {
                    $data1[] = $pres;
                }
            } else {
                $data1 = [];
            }

            if (count($tabdata) > 0) {
                $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $tabdata[0]->user_map_id)->where('id', $tabdata[0]->clinic_id)->first();
            }
            if (count($tabdata) > 0) {
                $tabdata[0]->timing_details = $data;
                if (count($data1) > 0) {

                    $tabdata[0]->prescription_flag = true;
                } else {
                    $tabdata[0]->prescription_flag = false;
                }
                if (count($data1) > 0) {
                    $url = $_ENV['APP_URL'] . '/api/v3/prescription/view/' . $tabdata[0]->user_map_id . '/' . $tabdata[0]->patient_id . '/' . $bookingID;

                    $tabdata[0]->prescription_details = [
                        "urls" => $url
                    ];
                } else {
                    $tabdata[0]->prescription_details = [
                        "urls" => ''
                    ];
                }
                Log::info(['url of the precription' => $tabdata[0]->prescription_details]);
                // $tabdata[0]->payment_url = Controller::urlshorten($_ENV['PAYMENT_URL_V1'] . $bookingID . '/pay');
                $tabdata[0]->payment_url = $_ENV['APP_URL'] . '/api/v3/payment/' . $bookingID . '/pay';
                $tabdata[0]->appointment_url = Controller::urlshorten($tabdata[0]->appointment_url);
                // $tabdata[0]->appointment_url = $tabdata[0]->appointment_url;

                $tabdata[0]->created_date = date('M d, Y h:i A', strtotime($tabdata[0]->created_date));
                $tabdata[0]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                $response = [
                    'appointment' => $tabdata
                ];
            } else {
                $response = [
                    'appointment' => $tabdata,
                    'msg' => "no record found"
                ];
            }
            return $response;
        } else {
            $data = $request->input();

            $unscheduletabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->where('docexa_patient_booking_details.date', null)
                ->where('docexa_patient_booking_details.patient_id', $data['patient_id'])

                ->whereIn('docexa_patient_booking_details.status', [1])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })
                ->latest('docexa_patient_booking_details.created_date')
                //->union($a) 
                ->get();


            // $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $unscheduletabdata[0]->user_map_id)->where('id',$unscheduletabdata[0]->clinic_id)->first();
            // $unscheduletabdata[0]->clinic_name =  $clinicName ?$clinicName->clinic_name  :null;

            $todaystabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->where('docexa_patient_booking_details.date', '=', date('Y-m-d'))
                ->where('docexa_patient_booking_details.status', '!=', 3)
                ->where('docexa_patient_booking_details.status', '!=', 6)
                ->where('docexa_patient_booking_details.status', '!=', 4)
                ->where('docexa_patient_booking_details.patient_id', $data['patient_id'])

                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                //->union($b) 
                ->get();




            // $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $todaystabdata[0]->user_map_id)->where('id',$todaystabdata[0]->clinic_id)->first();
            // $todaystabdata[0]->clinic_name =  $clinicName ?$clinicName->clinic_name  :null;

            $pasttabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->where('docexa_patient_booking_details.patient_id', $data['patient_id'])

                // ->whereNotNull('docexa_patient_booking_details.date')
                ->whereIn('docexa_patient_booking_details.status', [4, 3, 6])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                //->union($c) 
                ->get();

            $upcomingtabdata = DB::table('docexa_patient_booking_details')
                ->Join('docexa_appointment_sku_details', 'docexa_patient_booking_details.booking_id', '=', 'docexa_appointment_sku_details.booking_id')
                ->Join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'docexa_patient_booking_details.patient_id')
                ->Join('docexa_esteblishment_user_map_sku_details', 'docexa_esteblishment_user_map_sku_details.id', '=', 'docexa_appointment_sku_details.esteblishment_user_map_sku_id')
                ->Join('docexa_appointment_status_master', 'docexa_appointment_status_master.id', '=', 'docexa_patient_booking_details.status')
                ->Join('docexa_medical_establishments_medical_user_map', 'docexa_patient_booking_details.user_map_id', '=', 'docexa_medical_establishments_medical_user_map.id')
                ->select('docexa_patient_booking_details.age', 'docexa_patient_booking_details.gender', 'docexa_patient_booking_details.booking_id as appt_id', 'docexa_patient_booking_details.patient_id', 'docexa_patient_booking_details.payment_mode', 'docexa_patient_booking_details.cancellation_reason as reason', 'docexa_patient_booking_details.status', 'docexa_appointment_status_master.status_text', 'docexa_patient_booking_details.status', 'docexa_patient_booking_details.schedule_remark', 'docexa_appointment_sku_details.booking_type', 'docexa_esteblishment_user_map_sku_details.title', 'docexa_esteblishment_user_map_sku_details.description', 'docexa_patient_booking_details.bookingidmd5 as booking_id', 'docexa_patient_booking_details.created_date', 'docexa_patient_booking_details.date', 'docexa_patient_booking_details.start_time', 'docexa_patient_booking_details.patient_name', 'docexa_patient_booking_details.email_id as email', 'docexa_patient_booking_details.mobile_no', 'docexa_patient_booking_details.cost', 'docexa_patient_booking_details.clinic_id')
                ->selectRaw('+91 as country_code')
                ->selectRaw("concat('" . $_ENV['APP_HANDLE'] . "',docexa_medical_establishments_medical_user_map.handle) as handle")
                ->where('docexa_patient_booking_details.user_map_id', $data['user_map_id'])
                ->where('docexa_patient_booking_details.patient_id', $data['patient_id'])
                ->whereDate('docexa_patient_booking_details.date', '>=', Carbon::parse(date('Y-m-d'))->startOfDay())
                ->whereIn('docexa_patient_booking_details.status', [1, 2, 5])
                ->where(function ($query) {
                    $query->whereNotNull('docexa_patient_booking_details.credit_history_id');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'free');
                    $query->orWhere('docexa_patient_booking_details.payment_mode', 'direct');
                })->latest('docexa_patient_booking_details.created_date')
                // ->union($d) 
                ->get();

            // ->whereNotNull('docexa_patient_booking_details.date')

            if (count($unscheduletabdata) > 0) {
                foreach ($unscheduletabdata as $key => $value) {
                    $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $data['user_map_id'])->where('id', $value->clinic_id)->first();
                    $unscheduletabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                }
            }
            if (count($todaystabdata) > 0) {
                foreach ($todaystabdata as $key => $value) {
                    $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $data['user_map_id'])->where('id', $value->clinic_id)->first();
                    $todaystabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                }
            }

            if (count($pasttabdata) > 0) {
                foreach ($pasttabdata as $key => $value) {
                    $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $data['user_map_id'])->where('id', $value->clinic_id)->first();
                    $pasttabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                }
            }

            if (count($upcomingtabdata) > 0) {
                foreach ($upcomingtabdata as $key => $value) {
                    $clinicName = DB::table('docexa_clinic_user_map')->where('user_map_id', $data['user_map_id'])->where('id', $value->clinic_id)->first();
                    $upcomingtabdata[$key]->clinic_name = $clinicName ? $clinicName->clinic_name : null;
                }
            }
            $updatedPastTabData = $pasttabdata->map(function ($pastapt) use ($data) {
                // Check if a billing record exists for the specific appointment
                $exist = DB::table('billing')
                    ->where('usermap_id', $data['user_map_id'])
                    ->where('appointment_id', $pastapt->appt_id)
                    ->exists();

                // Return each past appointment with an added 'billing_status' field
                return array_merge((array) $pastapt, ['billing_status' => $exist ? 'true' : 'false']);
            });



            $response = [
                'unscheduleappointment' => $unscheduletabdata,
                'todayappointment' => $todaystabdata,
                'pastappointment' => $updatedPastTabData,
                'upcomingappointment' => $upcomingtabdata
            ];
            return $response;
        }
    }
}
//                 // 1 status added in getapt of line 397
