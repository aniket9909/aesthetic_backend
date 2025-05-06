<?php


namespace App\Http\Controllers;

use App\BillingModel;
use App\BillMasterModel;
use App\ClinicBillTemplate;
use App\MedicalCertificateTemplateModel;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use DB;
use App\Doctor;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Patientmaster;



use Log;

class AnalyticsApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {
    }
    public function getPatientAnalyticsCount(Request $request)
    {
        try {
            $input = $request->all();

            $startDate = $input['startDate'] . ' 00:00:00';
            $endDate = $input['endDate'] . ' 23:59:59';

            $totalPatientCount = DB::table('docexa_patient_doctor_relation')->where('user_map_id', $input['user_map_id'])->whereBetween('created_date', [$startDate, $endDate])->count();

            $totalVisits = DB::table('docexa_patient_booking_details')->where('user_map_id', $input['user_map_id'])
                ->whereIn('status', [2, 4])
                ->whereBetween('created_date', [$startDate, $endDate])->count();

            $prescriptionreated = DB::table('prescription')->where('user_map_id', $input['user_map_id'])->count();



            $last7DaysRecordsOfSeenPrescription = DB::table('docexa_patient_booking_details')
            ->where('status', 4)
            ->where('user_map_id', $input['user_map_id'])
            ->whereRaw('created_date >= CURDATE() - INTERVAL 7 DAY')
            ->count();

            $last7daysRecordsOfTotalSeenPrescriptionWhethereOrNot = DB :: table('prescription')
            ->where('user_map_id' , $input['user_map_id'])
            ->whereRaw('created_at >= CURDATE() - INTERVAL 7 DAY')
            ->count();

        $last7DaysRecordOfAppointmentsTotal = DB::table('docexa_patient_booking_details')
            ->whereIn('status', [2, 4])
            ->where('user_map_id', $input['user_map_id'])
            ->whereRaw('created_date >= CURDATE() - INTERVAL 7 DAY')
            ->count();

        $last7DaysRecordOfNewPatient = DB::table('docexa_patient_details')
            ->where('created_by_doctor', $input['user_map_id'])
            ->whereRaw('created_at >= CURDATE() - INTERVAL 7 DAY')
            ->count();

            $data = [
                "total_patient" => $totalPatientCount,
                "total_visits" => $totalVisits,
                "prescription_created" => $prescriptionreated,
                "last7dayRecordsOfSeenPrescription" => $last7daysRecordsOfTotalSeenPrescriptionWhethereOrNot,
                "last7DaysRecordOfAppointmentsTotal" => $last7DaysRecordOfAppointmentsTotal,
                "last7DaysRecordOfNewPatient" => $last7DaysRecordOfNewPatient,
            ];

            return response()->json(['status' => true, 'message' => "Data retrived successfully", 'code' => 200, 'data' => $data], 200);

        } catch (\Throwable $th) {
            Log::info(["error" => $th]);
            return response()->json(['status' => false, 'message' => "Internal server error", 'error' => $th->getMessage()], 500);
        }
    }

    public function getRevenueByModeOfBilling(Request $request)
    {
        try {
            $input = $request->all();
            $startDate = $input['startDate'] . ' 00:00:00';
            $endDate = $input['endDate'] . ' 23:59:59';

            $paymentModes = [
                'Online payment',
                'Cash payment',
                'Google Pay',
                'PhonePe',
                'Paytm',
                'BHIM UPI',
                'Credit Card',
                'Debit Card',
                'Net Banking'
            ];
            $totalsAll = DB::table('billing')
                ->select('mode_of_payment', DB::raw('SUM(total_price) as total'))
                ->whereIn('mode_of_payment', $paymentModes)
                ->where('usermap_id', $input['user_map_id'])
                ->whereBetween('created_at', [
                    $startDate,
                    $endDate

                ])
                ->groupBy('mode_of_payment')
                ->pluck('total', 'mode_of_payment')
                ->toArray();
            $sum = 0;
            foreach ($paymentModes as $mode) {
                $label[] = $mode;
                $total[] = $totalsAll[$mode] ?? 0;
                $sum = $sum + ($totalsAll[$mode] ?? 0);
            }
            $data = [
                'label' => $label,
                'data' => $total,
                'total' => $sum
            ];

            if ($data) {
                return response()->json(['status' => true, 'message' => 'data retrived successfully', 'data' => $data, 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'data not found', 'data' => [], 'code' => 200], 200);
            }

        } catch (\Throwable $th) {
            Log::info(["error" => $th]);
            return response()->json(['status' => false, 'message' => "Internal server error", 'error' => $th->getMessage()], 500);
        }
    }

    public function getRevenueByTypeOfBilling(Request $request)
    {
        try {

            $input = $request->all();
            $startDate = $input['startDate'] . ' 00:00:00';
            $endDate = $input['endDate'] . ' 23:59:59';

            $billings = DB::table('billing')
                ->whereNotNull('items')->where('usermap_id', $input['user_map_id'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $allVaccinationBrands = DB::table('bill_master')
                ->pluck('item_name')
                ->toArray();

            $sum = 0;

            $vaccinationTotals = [];
            $labels = [];
            if (count($billings) > 0) {
                foreach ($billings as $billing) {
                    $items = json_decode($billing->items, true);
                    if ($items) {
                        foreach ($items as $item) {
                            $brandName = $item['item_name'];
                            if (in_array($brandName, $allVaccinationBrands)) {
                                if (!isset($vaccinationTotals[$brandName])) {
                                    $vaccinationTotals[$brandName] = 0;
                                    $labels[] = $brandName;
                                }
                                $vaccinationTotals[$brandName] += $item['item_price'];
                                $sum = $sum + $item['item_price'];
                            }

                        }
                    }
                }
                $data = array_values($vaccinationTotals);
            }
            $data = [
                'labels' => $labels,
                'data' => $data,
                'total' => $sum
            ];
            if ($data) {
                return response()->json(['status' => true, 'message' => 'data retrived successfully', 'data' => $data, 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'data not found', 'data' => [], 'code' => 200], 200);

            }
        } catch (\Throwable $th) {
            Log::info(["error" => $th]);
            return response()->json(['status' => false, 'message' => "Internal server error", 'error' => $th->getMessage()], 500);
        }
    }
    public function getTopSymptoms(Request $request)
    {
        try {
            $input = $request->all();
            $startDate = $input['startDate'] . ' 00:00:00';
            $endDate = $input['endDate'] . ' 23:59:59';

            $prescriptions = DB::table('prescription')
                ->select('complaints', 'patient_id')
                ->whereNotNull('complaints')
                ->where('user_map_id', $input['user_map_id'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $summary = [];

            foreach ($prescriptions as $prescription) {
                $complaints = array_map('trim', explode(',', $prescription->complaints));
                foreach ($complaints as $complaint) {
                    if ($complaint === '')
                        continue;

                    if (!isset($summary[$complaint])) {
                        $summary[$complaint] = [
                            'symptom' => $complaint,
                            'count' => 0,
                            'patients' => [],
                        ];
                    } else {
                        $summary[$complaint]['count']++;
                        $summary[$complaint]['patients'][] = $prescription->patient_id;
                    }

                }
            }
            foreach ($summary as &$item) {
                $patient = array_values(array_unique($item['patients']));
                $item['patients'] = Patientmaster::whereIn('patient_id', $patient)->get();
            }
            unset($item);

            $result = array_values($summary);
            // arsort($result);
            // dd($result);
            $counts = array_column($result, 'count');
            array_multisort($counts, SORT_DESC, $result);
            $total = count($result);
            $offset = ($input['page'] - 1) * $input['limit'];
            $paginated = array_slice($result, $offset, $input['limit']);

            return response()->json([
                'current_page' => $input['page'],
                'per_page' => $input['limit'],
                'total' => $total,
                'data' => $paginated
            ]);

        } catch (\Throwable $th) {
            Log::info(["error" => $th]);
            return response()->json(['status' => false, 'message' => "Internal server error", 'error' => $th->getMessage()], 500);
        }

    }

    public function getTopdiagnosis(Request $request)
    {
        try {
            $input = $request->all();
            $startDate = $input['startDate'] . ' 00:00:00';
            $endDate = $input['endDate'] . ' 23:59:59';

            $prescriptions = DB::table('prescription')
                ->select('diagnosis', 'patient_id')
                ->whereNotNull('diagnosis')
                ->where('user_map_id', $input['user_map_id'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $summary = [];


            foreach ($prescriptions as $prescription) {
                $diagnosiss = array_map('trim', explode(',', $prescription->diagnosis));
                foreach ($diagnosiss as $diagnosis) {
                    if ($diagnosis === '')
                        continue;

                    if (!isset($summary[$diagnosis])) {
                        $summary[$diagnosis] = [
                            'diagnosis' => $diagnosis,
                            'count' => 0,
                            'patients' => []
                        ];
                    } else {
                        $summary[$diagnosis]['count']++;
                        $summary[$diagnosis]['patients'][] = $prescription->patient_id;
                    }

                }
            }

            foreach ($summary as &$item) {
                $patient = array_values(array_unique($item['patients']));
                $item['patients'] = Patientmaster::whereIn('patient_id', $patient)->get();
            }
            unset($item);

            $result = array_values($summary);

            $counts = array_column($result, 'count');
            array_multisort($counts, SORT_DESC, $result);
            $total = count($result);
            $offset = ($input['page'] - 1) * $input['limit'];
            $paginated = array_slice($result, $offset, $input['limit']);

            return response()->json([
                'current_page' => $input['page'],
                'per_page' => $input['limit'],
                'total' => $total,
                'data' => $paginated
            ]);
        } catch (\Throwable $th) {
            Log::info(["error" => $th]);
            return response()->json(['status' => false, 'message' => "Internal server error", 'error' => $th->getMessage()], 500);
        }
    }


    public function getTopInvestigation(Request $request)
    {
        try {
            $input = $request->all();
            $startDate = $input['startDate'] . ' 00:00:00';
            $endDate = $input['endDate'] . ' 23:59:59';

            $prescriptions = DB::table('prescription')
                ->select('test_requested', 'patient_id')
                ->whereNotNull('test_requested')
                ->where('user_map_id', $input['user_map_id'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $summary = [];


            foreach ($prescriptions as $prescription) {
                $test_requesteds = array_map('trim', explode(',', $prescription->test_requested));
                foreach ($test_requesteds as $test_requested) {
                    if ($test_requested === '')
                        continue;

                    if (!isset($summary[$test_requested])) {
                        $summary[$test_requested] = [
                            'diagnosis' => $test_requested,
                            'count' => 0,
                            'patients' => []
                        ];
                    } else {
                        $summary[$test_requested]['count']++;
                        $summary[$test_requested]['patients'][] = $prescription->patient_id;
                    }

                }
            }

            foreach ($summary as &$item) {
                $patient = array_values(array_unique($item['patients']));
                $item['patients'] = Patientmaster::whereIn('patient_id', $patient)->get();
            }
            unset($item);

            $result = array_values($summary);

            $counts = array_column($result, 'count');
            array_multisort($counts, SORT_DESC, $result);
            $total = count($result);
            $offset = ($input['page'] - 1) * $input['limit'];
            $paginated = array_slice($result, $offset, $input['limit']);

            return response()->json([
                'current_page' => $input['page'],
                'per_page' => $input['limit'],
                'total' => $total,
                'data' => $paginated
            ]);
        } catch (\Throwable $th) {
            Log::info(["error" => $th]);
            return response()->json(['status' => false, 'message' => "Internal server error", 'error' => $th->getMessage()], 500);
        }
    }

    public function getTopMedicines(Request $request)
    {
        try {
            $input = $request->all();
            $startDate = $input['startDate'] . ' 00:00:00';
            $endDate = $input['endDate'] . ' 23:59:59';


            // select
//   p.patient_id,m.medication_name,pi.medication_id, count(pi.medication_id) as count 
//   from prescription_items as pi  
//   join prescription as p on p.id =pi.prescription_id 
//   join medication as m on m.id = pi.medication_id
//   where p.user_map_id=70671
//   group by
//       m.medication_name,
//       p.patient_id
//   order by count desc;

            $medications = DB::table('prescription_items as pi')
                ->join('prescription as p', 'p.id', '=', 'pi.prescription_id')
                ->join('medication as m', 'm.id', '=', 'pi.medication_id')
                ->where('p.user_map_id', $input['user_map_id'])
                ->whereNotNull('pi.medication_id')
                ->whereBetween('pi.created_at', [$startDate, $endDate])
                ->select(
                    'p.patient_id',
                    'm.medication_name',
                    'pi.medication_id',
                    'pi.prescription_id',
                    DB::raw('COUNT(pi.medication_id) as count')
                )
                ->groupBy('m.medication_name', 'pi.prescription_id', 'p.patient_id')
                ->orderByDesc('count')
                ->get();

            $summary = [];

            foreach ($medications as $medication) {
                $medicationval = $medication->medication_name;
                if ($medicationval === '')
                    continue;

                if (!isset($summary[$medicationval])) {
                    $summary[$medicationval] = [
                        'medication' => $medicationval,
                        'count' => $medication->count,
                        'patients' => [$medication->patient_id]
                    ];
                } else {
                    $summary[$medicationval]['count'] = $summary[$medicationval]['count'] + $medication->count;
                    $summary[$medicationval]['patients'][] = $medication->patient_id;
                }

            }

            foreach ($summary as &$item) {
                $patient = array_values(array_unique($item['patients']));
                // dd($patient);
                $item['patients'] = Patientmaster::whereIn('patient_id', $patient)->get();
            }
            unset($item);

            $result = array_values($summary);

            $counts = array_column($result, 'count');
            array_multisort($counts, SORT_DESC, $result);
            $total = count($result);
            $offset = ($input['page'] - 1) * $input['limit'];
            $paginated = array_slice($result, $offset, $input['limit']);

            return response()->json([
                'current_page' => $input['page'],
                'per_page' => $input['limit'],
                'total' => $total,
                'data' => $paginated
            ]);

        } catch (\Throwable $th) {
            Log::info(["error" => $th]);
            return response()->json(['status' => false, 'message' => "Internal server error", 'error' => $th->getMessage()], 500);
        }
    }

    public function getTopVaccinationbyBrand(Request $request)
    {
        try {
            $input = $request->all();
            $startDate = $input['startDate'] . ' 00:00:00';
            $endDate = $input['endDate'] . ' 23:59:59';

            $Vaccinations = DB::table('vaccination_details')
                ->select('brand_name', 'patient_id', DB::raw('COUNT(brand_name) as brandCount'))
                ->whereNotNull('brand_name')
                ->where('brand_name', '!=', '')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('user_map_id', $input['user_map_id'])
                ->groupBy('brand_name', 'patient_id')
                ->get();
            // select brand_name, patient_id, count(brand_name) as brandCount from vaccination_details where  brand_name is not null and user_map_id =70671 and brand_name!=""  group by brand_name order by brandCount desc;


            $summary = [];
            // dd($Vaccinations);

            foreach ($Vaccinations as $vaccination) {
                $vaccinationval = $vaccination->brand_name;
                if ($vaccinationval === '')
                    continue;

                if (!isset($summary[$vaccinationval])) {
                    $summary[$vaccinationval] = [
                        'vaccination' => $vaccinationval,
                        'count' => $vaccination->brandCount,
                        'patients' => [$vaccination->patient_id]
                    ];
                    // dd($summary);
                } else {
                    $summary[$vaccinationval]['count'] = $summary[$vaccinationval]['count'] + $vaccination->brandCount;
                    $summary[$vaccinationval]['patients'][] = $vaccination->patient_id;
                }

            }

            foreach ($summary as &$item) {
                $patient = array_values(array_unique($item['patients']));
                // dd($patient);
                $item['patients'] = Patientmaster::whereIn('patient_id', $patient)->get();
            }
            unset($item);

            $result = array_values($summary);

            $counts = array_column($result, 'count');
            array_multisort($counts, SORT_DESC, $result);
            $total = count($result);
            $offset = ($input['page'] - 1) * $input['limit'];
            $paginated = array_slice($result, $offset, $input['limit']);

            return response()->json([
                'current_page' => $input['page'],
                'per_page' => $input['limit'],
                'total' => $total,
                'data' => $paginated
            ]);

        } catch (\Throwable $th) {
            Log::info(["error" => $th]);
            return response()->json(['status' => false, 'message' => "Internal server error", 'error' => $th->getMessage()], 500);
        }
    }

    public function getTopVaccinationbyGroup(Request $request)
    {
        try {
            $input = $request->all();
            $startDate = $input['startDate'] . ' 00:00:00';
            $endDate = $input['endDate'] . ' 23:59:59';

            $Vaccinations = DB::table('vaccination_details')
                ->select('vaccine_name', 'patient_id', DB::raw('COUNT(vaccine_name) as vaccineCount'))
                ->whereNotNull('vaccine_name')
                ->where('vaccine_name', '!=', '')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('user_map_id', $input['user_map_id'])
                ->groupBy('vaccine_name', 'patient_id')
                ->get();

                $summary = [];
    
                foreach ($Vaccinations as $vaccination) {
                    $vaccinationval = $vaccination->vaccine_name;
                    if ($vaccinationval === '')
                        continue;
    
                    if (!isset($summary[$vaccinationval])) {
                        $summary[$vaccinationval] = [
                            'vaccination' => $vaccinationval,
                            'count' => $vaccination->vaccineCount,
                            'patients' => [$vaccination->patient_id]
                        ];
                        // dd($summary);
                    } else {
                        $summary[$vaccinationval]['count'] = $summary[$vaccinationval]['count'] + $vaccination->vaccineCount;
                        $summary[$vaccinationval]['patients'][] = $vaccination->patient_id;
                    }
    
                }

                foreach ($summary as &$item) {
                    $patient = array_values(array_unique($item['patients']));
                    // dd($patient);
                    $item['patients'] = Patientmaster::whereIn('patient_id', $patient)->get();
                }
                unset($item);
    
                $result = array_values($summary);
    
                $counts = array_column($result, 'count');
                array_multisort($counts, SORT_DESC, $result);
                $total = count($result);
                $offset = ($input['page'] - 1) * $input['limit'];
                $paginated = array_slice($result, $offset, $input['limit']);
    
                return response()->json([
                    'current_page' => $input['page'],
                    'per_page' => $input['limit'],
                    'total' => $total,
                    'data' => $paginated
                ]);


    
            } catch (\Throwable $th) {
            Log::info(["error" => $th]);
            return response()->json(['status' => false, 'message' => "Internal server error", 'error' => $th->getMessage()], 500);
        }
    }


}