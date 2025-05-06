<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class Prescription extends Model
{
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'docexa_e_prescription';
    protected $primaryKey = 'prescription_id';
    

    public function getSeenPrescription($request){
        $input = $request->all();
    // $today = date('Y-m-d');
    $today = $input['date'];

    $prescriptionArray = PrescriptionData::whereDate('created_at', $today)
                                            ->where('user_map_id' ,$input['user_map_id'] )
                                            ->orderBy('created_at' , 'desc')
                                         ->get();

         $formattedPrescriptions = [];

              foreach ($prescriptionArray as $prescription) {
                
                                             if ($prescription->booking_id){
                                                $patientData = DB::table('docexa_patient_booking_details')
                                                 ->where ('patient_id' , $prescription->patient_id)
                                                 ->where('user_map_id',$input['user_map_id'])
                                                 ->where('bookingidmd5' ,$prescription->booking_id )
                                                 ->first();
                                                //  dd($patientData);
                                            }else{
                                                $patientData =Patientmaster::find($prescription->patient_id);
                                            }
                                           

                   
                                        //     $bookingId = DB::table('docexa_patient_booking_details')
                                        //     ->whereRaw("md5(booking_id) = ?", $prescription->booking_id)
                                        //     ->latest()->first()->booking_id;
                                        //  if($bookingId){
                                        //     $billing_array = BillingModel :: where('appointment_id',$bookingId )->get();
                                        //     $billing_data =[];
                                        //     if (count($billing_array) > 0) {
                                        //         foreach ($billing_array as $bill) {
                                        //             $data2[] = $bill;
                                        //             Log::info(['billData' => $data2]);
                                        //         }
                                        //     } else {
                                        //         $data2 = [];
                                        //     }
                                        //  }

                                             $patient = Patientmaster::find($prescription->patient_id);
                                             
                                             if ($patient) {
                                                 $formattedPrescriptions[] = [
                                                     'age' => $patientData->age ?? 0,
                                                     'gender' => $patient->gender ?? 0,
                                                     'patient_id' => $prescription->patient_id,
                                                     'booking_id' => $prescription->booking_id,
                                                     'date' => $prescription->date,
                                                     'patient_name' => $patient->patient_name ?? 'Unknown',
                                                     'email' => $patient->email_id ?? '',
                                                     'mobile_no' => $patient->mobile_no ?? '',
                                                     'dob' => $patient->dob,
                                                     'flag' => $patient->flag
                                                    //  "billing_generated" =>    count($data2) > 0 ? true : false
                                                 ];
                                             }

                                         }
                                     
                                         return  $formattedPrescriptions;
                                     }






  
}