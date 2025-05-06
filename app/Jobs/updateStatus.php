<?php

namespace App\Jobs;

use App\AppointmentDetails;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Queue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
class updateStatus extends Queue implements ShouldQueue
{
    use  InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $appt_id;
    public function __construct($appt_id)
    {
        error_log($appt_id);
        
        $this->appt_id = $appt_id;
        error_log($this->appt_id);
    }
    
    public function apptID()
    {
    $jobs = DB::table('jobs')->select()->get();

        #Loop through all the failed jobs and format them for json printing
     //   $flag = false;
        foreach ($jobs as $job) {
            $jsonpayload = json_decode($job->payload);
           // $job->payload = $jsonpayload;

            $data = unserialize($jsonpayload->data->command);
            if($data->appt_id == $this->appt_id){
             //   $flag = true;
                DB::table('jobs')->where('id', $job->id)->delete();
            }
            //return $data->appt_id;
            //$job->exception  = explode("\n", $job->exception);
        }
      //  return $flag;
        
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $appt = new AppointmentDetails();
        $appt->updatestatus($this->appt_id);
        error_log("handle process");
        //DB::table('docexa_patient_booking_details')->where('bookingidmd5', $this->appt_id)->limit(1)->update(array('status' => 5));
    }
}
