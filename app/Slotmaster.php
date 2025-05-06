<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DateTime;
use DatePeriod;
use DateInterval;
use Log;
use App\AppointmentDetails;

class Slotmaster extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'docexa_slot_master';
    protected $primaryKey = 'id';
    public function getslotdetailsv2($esteblishmentUserMapID)
    {

        try {
            if ($esteblishmentUserMapID == '') {
                return response()->json(['status' => "fail", 'msg' => "Opps! esteblishment user map id not found."], 400);
            }
            if ($_GET['date'] < date('Y-m-d')) {
                return response()->json(['status' => "fail", 'msg' => "Opps! Past dates are not allowed"], 400);
            }
            if (isset($_GET['date']) && $_GET['date'] != date('Y-m-d')) {
                $date = new DateTime(date('Y-m-d H:i', strtotime($_GET['date'])));
            } else {
                $date = new DateTime(date('Y-m-d H:i'));
            }
            $appt = new AppointmentDetails();
            if (isset($_GET['date'])) {
                $bookedslotdata = $appt->getallslot($esteblishmentUserMapID, $_GET['date']);
            } else {
                $bookedslotdata = $appt->getallslot($esteblishmentUserMapID);
            }
            $slotdata = Slotmaster::where('user_map_id', $esteblishmentUserMapID)->get()->first();
            // dd($slotdata);
            if (isset($slotdata->start_time)) {
                $start_time = $slotdata->start_time;
            } else {
                $start_time = '08:00:00';
            }
            if (isset($slotdata->end_time)) {
                $end_time = $slotdata->end_time;
            } else {
                $end_time = '24:00:00';
            }
            if (isset($slotdata->slot_size)) {
                $slot_size = $slotdata->slot_size;
            } else {
                $slot_size = (int)$_ENV['SLOT_SIZE'];
            }
            $data = $this->selectTimesofDay($start_time, $end_time, $date, $slot_size . ' minutes', $esteblishmentUserMapID);
            $slotdata = [
                "id" => $esteblishmentUserMapID,
                "user_map_id" => $esteblishmentUserMapID,
                "start_time" => $start_time,
                "end_time" => $end_time,
                "slot_size" => $slot_size,
                "slot_time" => $data
            ];


            return response()->json(['status' => "success", 'data' => array('slot' => $slotdata)], 200);
        } catch (Exception $e) {
            return response()->json(['status' => "fail", 'msg' => "Something went wrong"], 400);
        }
    }
    public function getslotdetails($esteblishmentUserMapID)
    {
        
        try {
            if ($esteblishmentUserMapID == '') {
                return response()->json(['status' => "fail", 'msg' => "Opps! esteblishment user map id not found."], 400);
            }
            if ($_GET['date'] < date('Y-m-d')) {
                return response()->json(['status' => "fail", 'msg' => "Opps! Past dates are not allowed"], 400);
            }
            if (isset($_GET['date']) && $_GET['date'] != date('Y-m-d')) {
                $date = new DateTime(date('Y-m-d H:i', strtotime($_GET['date'])));
            } else {
                $date = new DateTime(date('Y-m-d H:i'));
            }
            $appt = new AppointmentDetails();
            if (isset($_GET['date'])) {
                $bookedslotdata = $appt->getallslot($esteblishmentUserMapID, $_GET['date']);
            } else {
                $bookedslotdata = $appt->getallslot($esteblishmentUserMapID);
            }
            $weekdays = $date->format( 'N' );
            $weekdays = (int)$weekdays;
            if($weekdays == 0)
                $weekdays = 7;
            if(isset($_GET['clinicID'])){
                $slotdataArray = Slotmaster::where('user_map_id', $esteblishmentUserMapID)->where('clinicID', $_GET['clinicID'])->where('day_id', $weekdays)->get();
            }else{
                $slotdataArray = Slotmaster::where('user_map_id', $esteblishmentUserMapID)->where('day_id', $weekdays)->get();
            }
            $slotArray = [];
            $slotdata = [];

            foreach($slotdataArray as $slotdata){
            if (isset($slotdata->start_time)) {
                $start_time = $slotdata->start_time;
            } else {
                $start_time = '08:00:00';
            }
            if (isset($slotdata->end_time)) {
                $end_time = $slotdata->end_time;
            } else {
                $end_time = '24:00:00';
            }
            if (isset($slotdata->slot_size)) {
                $slot_size = $slotdata->slot_size;
            } else {
                $slot_size = (int)$_ENV['SLOT_SIZE'];
            }
            $slotdata = [
                "id" => $esteblishmentUserMapID,
                "user_map_id" => $esteblishmentUserMapID,
                "start_time" => $start_time,
                "end_time" => $end_time,
                "slot_size" => $slot_size
            ];
            $data = $this->selectTimesofDay($start_time, $end_time, $date, $slot_size . ' minutes', $esteblishmentUserMapID);
            
            foreach($data as $value){
                $slotArray[] = $value;
            }
        }
            return response()->json(['status' => "success", 'data' => $slotdata, 'slot' => $slotArray], 200);
        } catch (Exception $e) {
            return response()->json(['status' => "fail", 'msg' => "Something went wrong"], 400);
        }
    }
    public function gethospitalslotdetails($esteblishmentUserMapID,$hospitalID)
    {
        
        try {
            if ($esteblishmentUserMapID == '') {
                return response()->json(['status' => "fail", 'msg' => "Opps! esteblishment user map id not found."], 400);
            }
            if ($_GET['date'] < date('Y-m-d')) {
                return response()->json(['status' => "fail", 'msg' => "Opps! Past dates are not allowed"], 400);
            }
            if (isset($_GET['date']) && $_GET['date'] != date('Y-m-d')) {
                $date = new DateTime(date('Y-m-d H:i', strtotime($_GET['date'])));
            } else {
                $date = new DateTime(date('Y-m-d H:i'));
            }
            $appt = new AppointmentDetails();
            if (isset($_GET['date'])) {
                $bookedslotdata = $appt->getallslot($esteblishmentUserMapID, $_GET['date']);
            } else {
                $bookedslotdata = $appt->getallslot($esteblishmentUserMapID);
            }
            $weekdays = $date->format( 'N' );
            $weekdays = (int)$weekdays;
            if($weekdays == 0)
                $weekdays = 7;
            $slotdataArray = HospitalSlotmaster::where('user_map_id', $esteblishmentUserMapID)->where('hospital_id', $hospitalID)->where('day_id', $weekdays)->get();
            
            $slotArray = [];
            $slotdata = [];
            foreach($slotdataArray as $slotdata){
            if (isset($slotdata->start_time)) {
                $start_time = $slotdata->start_time;
            } else {
                $start_time = '08:00:00';
            }
            if (isset($slotdata->end_time)) {
                $end_time = $slotdata->end_time;
            } else {
                $end_time = '24:00:00';
            }
            if (isset($slotdata->slot_size)) {
                $slot_size = $slotdata->slot_size;
            } else {
                $slot_size = (int)$_ENV['SLOT_SIZE'];
            }
            $slotdata = [
                "id" => $esteblishmentUserMapID,
                "user_map_id" => $esteblishmentUserMapID,
                "start_time" => $start_time,
                "end_time" => $end_time,
                "slot_size" => $slot_size
            ];

            $data = $this->selectTimesofDay($start_time, $end_time, $date, $slot_size . ' minutes', $esteblishmentUserMapID);
            
            foreach($data as $value){
                $slotArray[] = $value;
            }
        }
            return response()->json(['status' => "success", 'data' => $slotdata, 'slot' => $slotArray], 200);
        } catch (Exception $e) {
            return response()->json(['status' => "fail", 'msg' => "Something went wrong"], 400);
        }
    }

    public function selectTimesofDay($start = false, $end = false, $setdate=0, $interval_get = '15 minutes', $esteblishmentUserMapID=0)
    {

        $interval = DateInterval::createFromDateString($interval_get);
        $rounding_interval = $interval->i * 60;
        
        $date = new DateTime(
            date('Y-m-d H:i', round(strtotime($start) / $rounding_interval) * $rounding_interval)
        );
        $end = new DateTime(
            date('Y-m-d H:i', round(strtotime($end) / $rounding_interval) * $rounding_interval)
        );

        $opts = array();
       // $date->add(DateInterval::createFromDateString($interval_get));
        
        while ($date < $end) {

            $data = $date->format('H:i');
            $tempdate = $date;
            $date->add($interval);
            $enddata = $date->format('H:i');
            
            $defaulttimeslot = $this->selectTimesofDaydefault($data, $enddata,$interval_get);
            $appt = new AppointmentDetails();
            if (isset($_GET['date'])) {
                $bookedslotdata = $appt->getallslot($esteblishmentUserMapID, $_GET['date']);
            } else {
                $bookedslotdata = $appt->getallslot($esteblishmentUserMapID);
            }
           // var_dump($bookedslotdata);die;
            $flag = 0;
            foreach ($bookedslotdata as $bookedslot) {
                
                $bookedtimeslot = $this->selectTimesofDaydefault($bookedslot->start_booking_time, $bookedslot->end_booking_time,$interval_get);
                Log::info("input",[$bookedslot]);
                Log::info("output",[$bookedtimeslot]);
                $countarray = array_intersect($defaulttimeslot, $bookedtimeslot);
                Log::info("countarray",[$countarray]);
                if (count($countarray) > 0) {
                    $flag = 1;  
                }
            }
            if ($flag == 0) {
                $datetime = new DateTime(date('Y-m-d H:i'));
              //  var_dump($setdate);die;
                $setdatetime = new DateTime(date('Y-m-d H:i',strtotime($setdate->format('Y-m-d')." ".$date->format('H:i'))));
                
                if ($setdatetime  <= $datetime->add($interval)) {
                    $opts[] = ["slot" => $data, "tag" => 'past'];
                } else {
                    $opts[] = ["slot" => $data, "tag" => 'available'];
                }
            } else {
                $opts[] = ["slot" => $data, "tag" => 'booked'];
            }
        }

        return $opts;
    }

    public function selectTimesofDaydefault($start = false, $end = false, $interval = '15 minutes')
    {
         
        $interval = DateInterval::createFromDateString($interval);
        //$rounding_interval = $interval->i * 60;
        $rounding_interval = 15 * 60;
        $date = new DateTime(
            date('Y-m-d H:i', round(strtotime($start) / $rounding_interval) * $rounding_interval)
        );
        $end = new DateTime(
            date('Y-m-d H:i', round(strtotime($end) / $rounding_interval) * $rounding_interval)
        );
        
        $opts = array();
        while ($date < $end) {
            $data = $date->format('H:i');
            $opts[] = $data;
            $date->add($interval);
        }

        return $opts;
    }


    public function getslotdetailsV3($esteblishmentUserMapID)
    {
        
        try {
            if ($esteblishmentUserMapID == '') {
                return response()->json(['status' => "fail", 'msg' => "Opps! esteblishment user map id not found."], 400);
            }
            if ($_GET['date'] < date('Y-m-d')) {
                return response()->json(['status' => "fail", 'msg' => "Opps! Past dates are not allowed"], 400);
            }
            if (isset($_GET['date']) && $_GET['date'] != date('Y-m-d')) {
                $date = new DateTime(date('Y-m-d H:i', strtotime($_GET['date'])));
            } else {
                $date = new DateTime(date('Y-m-d H:i'));
            }
            $appt = new AppointmentDetails();
            if (isset($_GET['date'])) {
                $bookedslotdata = $appt->getallslotV3($esteblishmentUserMapID, $_GET['date']);
            } else {
                $bookedslotdata = $appt->getallslotV3($esteblishmentUserMapID);
            }
            $weekdays = $date->format( 'N' );
            $weekdays = (int)$weekdays;
            if($weekdays == 0)
                $weekdays = 7;
            if(isset($_GET['clinicID'])){
                $slotdataArray = Slotmaster::where('user_map_id', $esteblishmentUserMapID)->where('clinicID', $_GET['clinicID'])->where('day_id', $weekdays)->get();
            }else{
                $slotdataArray = Slotmaster::where('user_map_id', $esteblishmentUserMapID)->where('day_id', $weekdays)->get();
            }
            $slotArray = [];
            $slotdata = [];

            foreach($slotdataArray as $slotdata){
            if (isset($slotdata->start_time)) {
                $start_time = $slotdata->start_time;
            } else {
                $start_time = '08:00:00';
            }
            if (isset($slotdata->end_time)) {
                $end_time = $slotdata->end_time;
            } else {
                $end_time = '24:00:00';
            }
            if (isset($slotdata->slot_size)) {
                $slot_size = $slotdata->slot_size;
            } else {
                $slot_size = (int)$_ENV['SLOT_SIZE'];
            }
            $slotdata = [
                "id" => $esteblishmentUserMapID,
                "user_map_id" => $esteblishmentUserMapID,
                "start_time" => $start_time,
                "end_time" => $end_time,
                "slot_size" => $slot_size
            ];
            $data = $this->selectTimesofDayV3($start_time, $end_time, $date, $slot_size . ' minutes', $esteblishmentUserMapID);
            foreach($data as $value){
                $slotArray[] = $value;
            }
        }
            return response()->json(['status' => "success", 'data' => $slotdata, 'slot' => $slotArray], 200);
        } catch (Exception $e) {
            return response()->json(['status' => "fail", 'msg' => "Something went wrong"], 400);
        }
    }

    public function selectTimesofDayV3($start = false, $end = false, $setdate=0, $interval_get = '15 minutes', $esteblishmentUserMapID=0)
    {

        $interval = DateInterval::createFromDateString($interval_get);
        $rounding_interval = $interval->i * 60;
        
        $date = new DateTime(
            date('Y-m-d H:i', round(strtotime($start) / $rounding_interval) * $rounding_interval)
        );
        $end = new DateTime(
            date('Y-m-d H:i', round(strtotime($end) / $rounding_interval) * $rounding_interval)
        );

        $opts = array();
       // $date->add(DateInterval::createFromDateString($interval_get));
        
        while ($date < $end) {

            $data = $date->format('H:i');
            $tempdate = $date;
            $date->add($interval);
            $enddata = $date->format('H:i');
            
            $defaulttimeslot = $this->selectTimesofDaydefaultV3($data, $enddata,$interval_get);
            $appt = new AppointmentDetails();
            if (isset($_GET['date'])) {
                $bookedslotdata = $appt->getallslotV3($esteblishmentUserMapID, $_GET['date']);
            } else {
                $bookedslotdata = $appt->getallslotV3($esteblishmentUserMapID);
            }
            // dd($bookedslotdata);
            // $bookedslotdata = $bookedslotdata['slots'];
            // dd($bookedslotdata);
        //    var_dump($bookedslotdata);die;
            $flag = 0;
            foreach ($bookedslotdata as $bookedslot) {
                // dd($bookedslotdata);
                // dd($bookedslot);
                $bookedtimeslot = $this->selectTimesofDaydefaultV3($bookedslot->start_booking_time, $bookedslot->end_booking_time,$interval_get);
                Log::info("input",[$bookedslot]);
                Log::info("output",[$bookedtimeslot]);
                $countarray = array_intersect($defaulttimeslot, $bookedtimeslot);
                Log::info("countarray",[$countarray]);
                if (count($countarray) > 0) {
                    $flag = 1;  
                }
            }

            $flag = 0;
            foreach ($bookedslotdata as $bookedslot) {
                $bookedtimeslot = $this->selectTimesofDaydefaultV3($bookedslot->start_booking_time, $bookedslot->end_booking_time, $interval_get);
                $countarray = array_intersect($defaulttimeslot, $bookedtimeslot);
                if (count($countarray) > 0) {
                    $flag = 1;
                    $booked_count = $bookedslot->booked_count ?$bookedslot->booked_count  : 0 ;
                    break;
                }
            }

            if ($flag == 0) {
                $datetime = new DateTime(date('Y-m-d H:i'));
              //  var_dump($setdate);die;
                $setdatetime = new DateTime(date('Y-m-d H:i',strtotime($setdate->format('Y-m-d')." ".$date->format('H:i'))));
                
                if ($setdatetime  <= $datetime->add($interval)) {
                    $opts[] = ["slot" => $data, "tag" => 'past' , "count" => 0];
                } else {
                    $opts[] = ["slot" => $data, "tag" => 'available' , "count" => 0];
                }
            } else {
                $opts[] = ["slot" => $data, "tag" => 'booked' ,"count" =>$booked_count];
            }
        }

        return $opts;
    }

    public function selectTimesofDaydefaultV3($start = false, $end = false, $interval = '15 minutes')
    {
         
        $interval = DateInterval::createFromDateString($interval);
        //$rounding_interval = $interval->i * 60;
        $rounding_interval = 15 * 60;
        $date = new DateTime(
            date('Y-m-d H:i', round(strtotime($start) / $rounding_interval) * $rounding_interval)
        );
        $end = new DateTime(
            date('Y-m-d H:i', round(strtotime($end) / $rounding_interval) * $rounding_interval)
        );
        
        $opts = array();
        while ($date < $end) {
            $data = $date->format('H:i');
            $opts[] = $data;
            $date->add($interval);
        }

        return $opts;
    }
public function bookedslotdata(){

}
}
