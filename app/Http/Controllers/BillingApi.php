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
use App\Models\BillingLogModel;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;



use Log;

class BillingApi extends Controller
{

    /**
     * Constructor
     */
    public function __construct() {}

    public function addBill(Request $request)
    {
        try {
            $input = $request->all();
            $Bill = new BillMasterModel();
            $Bill->item_name = $input['item_name'];
            $Bill->price = $input['price'];
            $Bill->type = $input['type'];
            $Bill->clinic_id = $input['clinic_id'];
            $Bill->usermap_id = $input['usermap_id'];
            $save = $Bill->save();

            if ($save) {
                return response()->json(['status' => true, 'message' => 'data saved successfully', 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'something went wrong', 'code' => 400], 400);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }

    public function EditBill(Request $request, $id)
    {
        try {
            $input = $request->all();
            $Bill = BillMasterModel::where('id', $id)->first();
            if ($Bill) {
                $Bill->item_name = $input['item_name'];
                $Bill->price = $input['price'];
                $Bill->type = $input['type'];
                $Bill->clinic_id = $input['clinic_id'];
                $Bill->usermap_id = $input['usermap_id'];
                $save = $Bill->save();
                if ($save) {
                    return response()->json(['status' => true, 'message' => 'data saved successfully', 'code' => 200], 200);
                } else {
                    return response()->json(['status' => false, 'message' => 'something went wrong', 'code' => 400], 400);
                }
            } else {
                return response()->json(['status' => false, 'message' => 'Bill not found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }



    public function getBiilMaster($usermap_id, $clinic_id): mixed
    {
        try {
            $billMaster = BillMasterModel::where('usermap_id', $usermap_id)->where('clinic_id', $clinic_id)->get();
            if (count($billMaster) > 0) {
                return response()->json(['status' => true, 'message' => 'data retrived successfully', 'data' => $billMaster, 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'data not found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }
    public function getBiilMasterByPageLimit($usermap_id, $clinic_id, $page, $limit): mixed
    {
        try {
            $offset = ($page - 1) * $limit;

            $billMasterQuery = BillMasterModel::where('usermap_id', $usermap_id)
                ->where('clinic_id', $clinic_id);

            $totalItems = $billMasterQuery->count();
            $billMaster = $billMasterQuery->offset($offset)->limit($limit)->get();

            // $billMaster =BillMasterModel::where('usermap_id' , $usermap_id)->where('clinic_id' , $clinic_id)-> get();
            if ($totalItems > 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'data retrived successfully',
                    'data' => $billMaster,
                    'pagination' => [
                        'total' => $totalItems,
                        'current_page' => $page,
                        'per_page' => $limit,
                    ],
                    'code' => 200
                ], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'data not found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }

    public function billSearch($usermap_id, $clinic_id, $key, $value, $page, $limit)
    {
        try {
            $offset = ($page - 1) * $limit;

            $billMasterQuery = BillMasterModel::where('usermap_id', $usermap_id)
                ->where('clinic_id', $clinic_id);

            if ($key === 'item_name') {
                $billMasterQuery->where('item_name', 'LIKE', '%' . $value . '%');
            } else if ($key == 'price') {
                $billMasterQuery->where('price', 'LIKE', '%' . $value . '%');
            } else if ($key = 'type') {
                $billMasterQuery->where('type', 'LIKE', '%' . $value . '%');
            } else {
                return response()->json(['status' => false, 'message' => 'enter the key value correctly , it can be item_name ,price, type ', 'code' => 400], 200);
            }

            $totalItems = $billMasterQuery->count();

            $billMaster = $billMasterQuery->offset($offset)->limit($limit)->get();

            $dataByPagination = BillMasterModel::where('usermap_id', $usermap_id)
                ->where('clinic_id', $clinic_id)->offset($offset)->limit($limit)->get();


            if ($totalItems > 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'Data retrieved successfully',
                    'search_result' => $billMaster,
                    'data_by_pagination' => $dataByPagination,
                    'pagination' => [
                        'total' => $totalItems,
                        'current_page' => $page,
                        'per_page' => $limit,
                    ],
                    'code' => 200
                ], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'No data found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }

    public function billSearchv2(Request $request)
    {
        try {
            $usermap_id = $request->query('usermap_id');
            $clinic_id = $request->query('clinic_id');
            $key = $request->query('key');
            $value = $request->query('value');
            $page = $request->query('page');
            $limit = $request->query('limit');
            // dd($request->all());
            $offset = ($page - 1) * $limit;

            $billMasterQuery = BillMasterModel::where('usermap_id', $usermap_id)
                ->where('clinic_id', $clinic_id);
            $totalItems = $billMasterQuery->count();


            if ($key === 'item_name') {
                $billMasterQuery->where('item_name', 'LIKE', '%' . $value . '%');
            } else if ($key == 'price') {
                $billMasterQuery->where('price', 'LIKE', '%' . $value . '%');
            } else if ($key = 'type') {
                $billMasterQuery->where('type', 'LIKE', '%' . $value . '%');
            } else {
                return response()->json(['status' => false, 'message' => 'enter the key value correctly , it can be item_name ,price, type ', 'code' => 400], 200);
            }


            $billMaster = $billMasterQuery->orderBy('created_at', 'desc')
                ->offset($offset)->limit($limit)->get();

            $dataByPagination = BillMasterModel::where('usermap_id', $usermap_id)
                ->where('clinic_id', $clinic_id)->offset($offset)->limit($limit)->get();


            if ($totalItems > 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'Data retrieved successfully',
                    'search_result' => $billMaster,
                    // 'data_by_pagination' =>  $dataByPagination,
                    'pagination' => [
                        'total' => $totalItems,
                        'current_page' => $page,
                        'per_page' => $limit,
                    ],
                    'code' => 200
                ], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'No data found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }

    public function addclinicTemplate(Request $request)
    {
        try {
            // id, clinic_id, usermap_id, bill_no, receipt_no, gstin_number, clinic_name, clinic_contact, clinic_address, default_note, signuture, created_at, updated_at, right_margin, left_margin, top_margin, bottom_margin, letter_head           
            $input = $request->all();
            $Bill = new ClinicBillTemplate();
            $Bill->clinic_id = $input['clinic_id'];
            $Bill->usermap_id = $input['usermap_id'];
            $Bill->bill_no = $input['bill_no'];
            $Bill->receipt_no = $input['receipt_no'];
            $Bill->gstin_number = $input['gstin_number'];
            $Bill->clinic_name = $input['clinic_name'];
            $Bill->clinic_contact = $input['clinic_contact'];
            $Bill->clinic_address = $input['clinic_address'];
            $Bill->default_note = $input['default_note'];
            $Bill->signuture = $input['signuture'];
            $Bill->right_margin = $input['right_margin'];
            $Bill->left_margin = $input['left_margin'];
            $Bill->top_margin = $input['top_margin'];
            $Bill->bottom_margin = $input['bottom_margin'];
            $Bill->letter_head = $input['letter_head'];
            $save = $Bill->save();
            if ($save) {
                return response()->json(['status' => true, 'message' => 'data saved successfully', 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'something went wrong', 'code' => 400], 400);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }
    public function getclinicTemplate($usermap_id, $clinic_id): mixed
    {
        try {
            $clinicTemplate = ClinicBillTemplate::where('usermap_id', $usermap_id)->where('clinic_id', $clinic_id)->get();
            if (count($clinicTemplate) > 0) {
                return response()->json(['status' => true, 'message' => 'data retrived successfully', 'data' => $clinicTemplate, 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'data not found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }


    public function createInvoice(Request $request)
    {
        try {
            // id, clinic_id, patient_id, usermap_id, appointment_id, total_price, mode_of_payment, paid_amount, balanced_amount, created_at, updated_at, items
            $input = $request->all();
            $Invoice = new BillingModel();
            $Invoice->clinic_id = $input['clinic_id'];
            $Invoice->patient_id = $input['patient_id'];
            $Invoice->usermap_id = $input['usermap_id'];
            $Invoice->appointment_id = $input['appointment_id'];
            $Invoice->mode_of_payment = $input['mode_of_payment'];
            $Invoice->paid_amount = $input['paid_amount'];
            $Invoice->total_price = $input['total_price'];
            $Invoice->bill_no = $input['bill_no'];
            $Invoice->receipt_no = $input['receipt_no'];

            $Invoice->items = json_encode($input['items']);
            $totalPrice = 0;
            foreach ($input['items'] as $item) {
                $totalPrice += $item['item_price'];
            }
            // $Invoice->total_price = (float) $totalPrice;

            $Invoice->balanced_amount = $input['total_price'] - $input['paid_amount'];;

            $save = $Invoice->save();
            if ($save) {
                return response()->json(['status' => true, 'message' => 'data saved successfully', 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'something went wrong', 'code' => 400], 400);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }

    public function getInvoice($usermap_id, $clinic_id): mixed
    {
        try {
            $invoice = BillingModel::where('usermap_id', $usermap_id)->where('clinic_id', $clinic_id)->get();
            if (count($invoice) > 0) {
                return response()->json(['status' => true, 'message' => 'data retrived successfully', 'data' => $invoice, 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'data not found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }
    public function getLastInvoice($usermap_id, $clinic_id): mixed
    {
        try {
            $invoice = BillingModel::where('usermap_id', $usermap_id)->where('clinic_id', $clinic_id)
                ->latest('created_at')
                ->first();
            if ($invoice) {
                return response()->json(['status' > true, 'message' => 'data retrived successfully', 'data' => $invoice, 'code' => 200], 200);
            } else {
                return response()->json(['status' > false, 'message' => 'data not found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }

    public function editClinicTemplate(Request $request, $id)
    {
        try {
            $input = $request->all();
            $Bill = ClinicBillTemplate::where('id', $id)->first();
            if ($Bill) {
                $Bill->clinic_id = $input['clinic_id'];
                $Bill->usermap_id = $input['usermap_id'];
                $Bill->bill_no = $input['bill_no'];
                $Bill->receipt_no = $input['receipt_no'];
                $Bill->gstin_number = $input['gstin_number'];
                $Bill->clinic_name = $input['clinic_name'];
                $Bill->clinic_contact = $input['clinic_contact'];
                $Bill->clinic_address = $input['clinic_address'];
                $Bill->default_note = $input['default_note'];
                $Bill->signuture = $input['signuture'];
                $Bill->right_margin = $input['right_margin'];
                $Bill->left_margin = $input['left_margin'];
                $Bill->top_margin = $input['top_margin'];
                $Bill->bottom_margin = $input['bottom_margin'];
                $Bill->letter_head = $input['letter_head'];
                $save = $Bill->save();
            } else {
                $Bill = new ClinicBillTemplate();
                $Bill->clinic_id = $input['clinic_id'];
                $Bill->usermap_id = $input['usermap_id'];
                $Bill->bill_no = $input['bill_no'];
                $Bill->receipt_no = $input['receipt_no'];
                $Bill->gstin_number = $input['gstin_number'];
                $Bill->clinic_name = $input['clinic_name'];
                $Bill->clinic_contact = $input['clinic_contact'];
                $Bill->clinic_address = $input['clinic_address'];
                $Bill->default_note = $input['default_note'];
                $Bill->signuture = $input['signuture'];
                $Bill->right_margin = $input['right_margin'];
                $Bill->left_margin = $input['left_margin'];
                $Bill->top_margin = $input['top_margin'];
                $Bill->bottom_margin = $input['bottom_margin'];
                $Bill->letter_head = $input['letter_head'];
                $save = $Bill->save();
            }

            if ($save) {
                return response()->json(['status' => true, 'message' => 'data saved successfully', 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'something went wrong', 'code' => 400], 400);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }

    public function billSearchOverAll(Request $request)
    {
        try {
            $usermap_id = $request->query('usermap_id');
            $clinic_id = $request->query('clinic_id');
            $key = $request->query('key');
            $value = $request->query('value');

            // dd($request->all());

            $billMasterQuery = BillMasterModel::where('usermap_id', $usermap_id)
                ->where('clinic_id', $clinic_id);
            $totalItems = $billMasterQuery->count();


            if ($key === 'item_name') {
                $billMasterQuery->where('item_name', 'LIKE', '%' . $value . '%');
            } else if ($key == 'price') {
                $billMasterQuery->where('price', 'LIKE', '%' . $value . '%');
            } else if ($key = 'type') {
                $billMasterQuery->where('type', 'LIKE', '%' . $value . '%');
            } else {
                return response()->json(['status' => false, 'message' => 'enter the key value correctly , it can be item_name ,price, type ', 'code' => 400], 200);
            }


            $billMaster = $billMasterQuery->get();


            if ($totalItems > 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'Data retrieved successfully',
                    'search_result' => $billMaster,
                    'code' => 200
                ], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'No data found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }

    public function getPatientInvoice($usermap_id, $clinic_id, $patient_id): mixed
    {
        try {
            $invoice = BillingModel::where('usermap_id', $usermap_id)->where('clinic_id', $clinic_id)->where('patient_id', $patient_id)->get();
            if (count($invoice) > 0) {
                return response()->json(['status' => true, 'message' => 'data retrived successfully', 'data' => $invoice, 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'data not found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }

    public function getPatientIvoices($usermap_id, $patient_id, $page, $limit): mixed
    {
        try {

            $offset = ($page - 1) * $limit;

            $total_count = BillingModel::where('usermap_id', $usermap_id)->where('patient_id', $patient_id)->count();
            $invoices = BillingModel::where('usermap_id', $usermap_id)->where('patient_id', $patient_id)
                ->orderBy('created_at', 'desc')
                ->offset($offset)->limit($limit)->get();
                // dd($invoices);s
            if (count($invoices) > 0) {
                foreach ($invoices as $invoice) {
                    $invoice->items = json_decode(json: $invoice->items);
                }

                return response()->json(['status' => true, 'message' => 'data retrived successfully', 'data' => $invoices, 'total_count' => $total_count, 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'data not found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }
    public function getPatientInvoiceByaptId($usermap_id, $clinic_id, $patient_id, $apt_id)
    {
        try {
            $invoice = BillingModel::where('usermap_id', $usermap_id)
                ->where('clinic_id', $clinic_id)
                ->where('patient_id', $patient_id)
                ->where('appointment_id', $apt_id)
                ->first();

            if ($invoice) {
                $invoice->items = json_decode($invoice->items);
                return response()->json(['status' => true, 'message' => 'data retrived successfully', 'data' => $invoice, 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'data not found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage(), 'code' => 500], 500);
        }
    }

    public function getPatientInvoiceByPagination($usermap_id, $clinic_id, $patient_id, $page, $limit): mixed
    {
        try {
            $offset = ($page - 1) * $limit;

            $total_count = BillingModel::where('usermap_id', $usermap_id)->where('clinic_id', $clinic_id)->where('patient_id', $patient_id)->count();
            $invoice = BillingModel::where('usermap_id', $usermap_id)->where('clinic_id', $clinic_id)->where('patient_id', $patient_id)->offset($offset)->limit($limit)->get();
            if (count($invoice) > 0) {
                return response()->json(['status' => true, 'message' => 'data retrived successfully', 'data' => $invoice, "total" => $total_count, 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'data not found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }


    public function getPricedetails($user_map_id, Request $request)
    {
        try {
            $input = $request->all();
            $clinicId = DB::table('docexa_clinic_user_map')->where('user_map_id', $user_map_id)->first()->id;
            // $fees = DB::table('docexa_esteblishment_user_map_sku_details')->where('user_map_id' , $user_map_id)->where('clinic_id' , $clinicId)->first()->fee;
            $clinicId = isset($input['clinic_id']) ? $input['clinic_id'] : $clinicId;
            Log::info(['ckinicid', $clinicId]);
            $consultation_fee = DB::table('docexa_esteblishment_user_map_sku_details')->where('user_map_id', $user_map_id)
                ->where('default_flag', 1)
                ->where('clinic_id', $clinicId)->where('booking_type', 'In clinic Consultation')->first()->fee;


            Log::info(['consultation_fee', $consultation_fee]);
            $vaccine_prices = DB::table('bill_master')
                ->where('usermap_id', $user_map_id)
                ->where('clinic_id', $clinicId)
                ->whereIn('item_name', $input['data'])
                ->select('item_name', 'price')
                ->get();
            $data = [];
            $item_counts = array_count_values($input['data']);

            $total_price = 0;

            if (count($vaccine_prices) > 0) {
                foreach ($vaccine_prices as $vaccine_price) {
                    for ($i = 0; $i < $item_counts[$vaccine_price->item_name]; $i++) {
                        $data[] = [
                            'item_name' => $vaccine_price->item_name,
                            'item_price' => round((float) $vaccine_price->price)
                        ];
                        $total_price = $total_price + round((float) $vaccine_price->price);
                    }
                }
            }

            $total_price = $total_price + $consultation_fee;
            $data[] = [
                'item_name' => 'Consultation',
                'item_price' => $consultation_fee
            ];


            // if (count($vaccine_prices) > 0) {
            //     foreach ($vaccine_prices as $vaccine_price) {
            //         $data[] = [
            //             'item_name' => $vaccine_price->item_name,
            //             'price' => $vaccine_price->price
            //         ];
            //     }
            // }

            if ($vaccine_prices) {
                return response()->json(['status' => true, 'message' => 'data retrived successfully', 'items' => $data, 'total_price' => $total_price, 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'data not found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }


    public function getSummaryOfTranscation($user_map_id, $page, $limit, Request $request)
    {
        try {
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
            $input = $request->all();
            $offset = ($page - 1) * $limit;


            $startDate = $input['startDate'] . ' 00:00:00';
            $endDate = $input['endDate'] . ' 23:59:59';

            $billings = DB::table('billing')
                ->whereNotNull('items')->where('usermap_id', $user_map_id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            // $totalItems = $billingsquery->count();
            // $billings = $billingsquery->offset($offset)->limit($limit)->get();

            $vaccinationTotal = 0;
            if (count($billings) > 0) {
                foreach ($billings as $billing) {
                    $items = json_decode($billing->items, true);
                    if ($items) {
                        $itemNames = array_column($items, 'item_name');


                        $vaccinationItems = DB::table('vaccination_brand_name')
                            ->whereIn('brand_name', $itemNames)
                            ->pluck('brand_name')
                            ->toArray();
                        foreach ($items as $item) {
                            // var_dump($item['item_price'] , $item['item_name']);
                            if (in_array($item['item_name'], haystack: $vaccinationItems)) {

                                $vaccinationTotal = $vaccinationTotal + $item['item_price'];
                                // var_dump('d',$vaccinationTotal);

                            }
                        }
                    }
                }
            }

            $totalsAll = DB::table('billing')
                ->select('mode_of_payment', DB::raw('SUM(total_price) as total'))
                ->whereIn('mode_of_payment', $paymentModes)
                ->where('usermap_id', $user_map_id)
                ->whereBetween('created_at', [
                    $startDate,
                    $endDate

                ])
                ->groupBy('mode_of_payment')
                ->pluck('total', 'mode_of_payment')
                ->toArray();
            $this->paymentModes = $paymentModes;
            $this->totalsAll = $totalsAll;
            $this->vaccinationTotal = $vaccinationTotal;

            foreach ($this->paymentModes as $mode) {
                $this->overAllResult[] = [
                    'payment_mode' => $mode,
                    'total' => $this->totalsAll[$mode] ?? 0,
                ];
            }

            $this->overAllResult[] = [
                'payment_mode' => 'Vaccination',
                'total' => $this->vaccinationTotal
            ];

            $totalquery
                = DB::table('billing')
                ->select(
                    'billing.id',
                    'billing.patient_id',
                    'docexa_patient_details.patient_name',
                    'docexa_patient_details.dob',
                    'docexa_patient_details.age',
                    'docexa_patient_details.gender',
                    'billing.mode_of_payment',
                    'billing.total_price'
                    // DB::raw('SUM(billing.total_price) as total')
                )
                ->join('docexa_patient_details', 'docexa_patient_details.patient_id', '=', 'billing.patient_id')
                ->whereIn('billing.mode_of_payment', $paymentModes)
                ->where('billing.usermap_id', $user_map_id)
                ->whereBetween('billing.created_at', [
                    $startDate,
                    $endDate
                ])
                ->groupBy('billing.patient_id', 'billing.mode_of_payment', 'docexa_patient_details.patient_name', 'docexa_patient_details.age', 'docexa_patient_details.gender', 'docexa_patient_details.dob')
                ->orderBy('billing.created_at', 'desc');

            $totalItems = $totalquery->get()->count();
            $totals = $totalquery->offset($offset)->limit($limit)->get();








            // $patientWiseTotals = [];
            // foreach ($totals as $row) {
            //     $patientWiseTotals[$row->patient_id][$row->mode_of_payment] = $row->total;
            // }
            // foreach ($patientWiseTotals as $patientId => &$modes) {
            //     foreach ($paymentModes as $mode) {
            //         if (!isset($modes[$mode])) {
            //             $modes[$mode] = 0;
            //         }
            //     }

            // }

            // $result = [];
            // foreach ($patientWiseTotals as $patientId => $modes) {
            //     $billings = DB::table('billing')
            //         ->select(
            //             'billing.*',
            //             'docexa_patient_details.patient_name',
            //             'docexa_patient_details.dob',
            //             'docexa_patient_details.gender'
            //         )
            //         ->join('docexa_patient_details', 'billing.patient_id', '=', 'docexa_patient_details.patient_id')
            //         ->whereNotNull('billing.items')
            //         ->where('billing.usermap_id', $user_map_id)
            //         ->whereBetween('billing.created_at', [
            //             $startDate
            //             ,
            //             $endDate
            //         ])
            //         ->where('billing.patient_id', $patientId)
            //         ->get();


            //     $vaccinationTotal = 0;
            //     $vaccinationTotal = 0;
            //     $patientName = null;
            //     $patientAge = null;
            //     $patientGender = null;

            //     if (count($billings) > 0) {
            //         $patientName = $billings[0]->patient_name;
            //         $patientdob
            //             = $billings[0]->dob;
            //         $patientGender = $billings[0]->gender;

            //         foreach ($billings as $billing) {

            //             $patientName = $billings[0]->patient_name;
            //             $ppatientdob = $billings[0]->dob;
            //             $patientGender = $billings[0]->gender;
            //             $items = json_decode($billing->items, true);
            //             if ($items) {
            //                 $itemNames = array_column($items, 'item_name');


            //                 $vaccinationItems = DB::table('vaccination_brand_name')
            //                     ->whereIn('brand_name', $itemNames)
            //                     ->pluck('brand_name')
            //                     ->toArray();
            //                 foreach ($items as $item) {
            //                     if (in_array($item['item_name'], haystack: $vaccinationItems)) {
            //                         $vaccinationTotal = $vaccinationTotal + $item['item_price'];

            //                     }
            //                 }
            //             }

            //         }
            //     }

            //     $result[] = [
            //         'patient_id' => $patientId,
            //         'payments' => $modes,
            //         'patient_name' => $patientName,
            //         'dob' => $ppatientdob,
            //         'gender' => $patientGender,
            //         'Vaccination' => $vaccinationTotal
            //     ];
            // }

            return response()->json([
                'status' => true,
                'data' => $this->overAllResult,
                'patient_wise_data' => $totals,
                'totalItems' => $totalItems
            ]);
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }
    // {usermapid}/{patientId}/{clinicId}/{aptId}
    public function updateBillingDetails($id, Request $request)
    {
        try {
            $input = $request->all();
            $billing = BillingModel::find($id);
            $paidAmount = isset($input['paid_amount']) ? $input['paid_amount'] : 0;
            // $billing = BillingModel::where('usermap_id', $usermapid)->where('patient_id' ,$patientId)->where('clinic_id' ,$clinicId) ->where('appointment_id' , $aptId)->latest()
            // ->first();
            // id, clinic_id, patient_id, usermap_id, appointment_id, total_price, mode_of_payment, paid_amount, balanced_amount, created_at, updated_at, items, receipt_no, bill_no
            if ($billing) {
                $billing->mode_of_payment = isset($input['mode_of_payment']) ? $input['mode_of_payment'] : $billing->mode_of_payment;
                $billing->paid_amount = $billing->paid_amount + $paidAmount;
                $billing->balanced_amount = $billing->total_price - $billing->paid_amount;

                $billing->bill_no = isset($input['bill_no']) ? $input['bill_no'] : $billing->bill_no;
                $billing->receipt_no = isset($input['receipt_no']) ? $input['receipt_no'] : $billing->receipt_no;

                // if (isset($input['items'])) {
                //     $billing->items = json_encode($input['items']);
                //     $totalPrice = 0;
                //     foreach ($input['items'] as $item) {
                //         $totalPrice += $item['item_price'];
                //     }
                //     // $billing->total_price = (float) $totalPrice;
                //     $billing->balanced_amount = $input['total_price'] - $input['paid_amount'];
                // } else {
                //     $billing->items = $billing->items;
                // }

                $save = $billing->save();

                if ($save) {

                    BillingLogModel::insert([
                        'billing_id' => $id,
                        'paid_amount' => $paidAmount,
                        'payment_date' => Carbon::now(),
                        'mode_of_payment' => $billing->mode_of_payment,
                        'balanced_amount' => $billing->balanced_amount,
                        'remarks' => 'Upadate the billing details',
                        'created_at' => Carbon::now()
                    ]);


                    return response()->json(['status' => true, 'message' => 'Data update successfully', 'code' => 200], 200);
                } else {
                    return response()->json(['status' => false, 'message' => 'Data update failed', 'code' => 400], 400);
                }
            } else {
                return response()->json(['status' => false, 'message' => 'Billing not found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }


    public function settleBillingAmount(Request $request)
    {
        // Manual validation for billing_id
        if (!$request->has('billing_id')) {
            return response()->json([
                'status' => false,
                'message' => 'The billing_id field is required and must be an integer.',
            ], 400);
        }
        // Optionally, check if billing_id exists in the billing table
        // if (!BillingModel::where('id', $request->billing_id)->exists()) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'The selected billing_id is invalid.',
        //     ], 400);
        // }

        DB::beginTransaction();
        try {
            $billing = BillingModel::findOrFail($request->billing_id);
            if (!$billing) {
                return response()->json([
                    'status' => false,
                    'message' => 'Billing record not found.',
                ], 404);
            }
            if ($billing->balanced_amount <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No balance amount to settle.',
                ], 400);
            }

            $settleAmount = $billing->balanced_amount;

            // Update billing table
            $billing->settle_amount = $settleAmount;
            $billing->balanced_amount = 0;
            if ($billing->save()) {
                // Create payment log without using create()
                $log = new BillingLogModel();
                $log->billing_id = $billing->id;
                $log->paid_amount = $settleAmount;
                $log->payment_date = Carbon::now();
                $log->mode_of_payment = 'settled'; // or you can allow custom value
                $log->balanced_amount = 0;
                $log->remarks = $request->remark != null ? $request->remark : 'Amount settled manually';
                $log->created_at = Carbon::now();
                $log->settle_amount = $settleAmount;
                $log->save();
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update billing record.',
                ], 500);
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Billing amount settled successfully.',
                'settled_amount' => $settleAmount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error settling amount: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function getBillById($id)
    {
        try {
            $data = BillingModel::where('id', $id)->get();
            if (count($data) > 0) {
                return response()->json(['status' => true, 'message' => 'data retrived successfully', 'data' => $data, 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'data not found', 'data' => [], 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }

    public function addCertificateTemplate($user_map_id, Request $request)
    {
        try {
            $input = $request->all();

            $data = MedicalCertificateTemplateModel::where('usermapid', $user_map_id)->where('clinic_id', $input['clinic_id'])->first();

            if ($data) {
                $data->font_family = $input['font_family'];
                $data->font_size = $input['font_size'];
                $data->top_margin = $input['top_margin'];
                $data->bottom_margin = $input['bottom_margin'];
                $data->left_margin = $input['left_margin'];
                $data->right_margin = $input['right_margin'];
                $data->clinic_id = $input['clinic_id'];
                $data->clinic_name = $input['clinic_name'];
                $data->clinic_address = $input['clinic_address'];
                $data->usermapid = $user_map_id;
                $save = $data->save();
                if ($save) {
                    return response()->json(['status' => true, 'message' => 'Data update successfully', 'code' => 200], 200);
                } else {
                    return response()->json(['status' => false, 'message' => 'Data update failed', 'code' => 200], 200);
                }
            } else {
                $data = new MedicalCertificateTemplateModel();
                $data->font_family = $input['font_family'];
                $data->font_size = $input['font_size'];
                $data->top_margin = $input['top_margin'];
                $data->bottom_margin = $input['bottom_margin'];
                $data->left_margin = $input['left_margin'];
                $data->right_margin = $input['right_margin'];
                $data->clinic_name = $input['clinic_name'];
                $data->clinic_address = $input['clinic_address'];
                $data->clinic_id = $input['clinic_id'];
                $data->usermapid = $user_map_id;
                $save = $data->save();
                if ($save) {
                    return response()->json(['status' => true, 'message' => 'Data added successfully', 'code' => 200], 200);
                } else {
                    return response()->json(['status' => false, 'message' => 'Data update failed', 'code' => 200], 200);
                }
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }

    public function getCertificateTemplate($user_map_id, $clinic_id)
    {
        try {
            $data = MedicalCertificateTemplateModel::where('usermapid', $user_map_id)->where('clinic_id', $clinic_id)->get();
            if (count($data) > 0) {
                return response()->json(['status' => true, 'message' => 'data retrived successfully', 'data' => $data, 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'data not found', 'data' => [], 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }


    public function getPricedetailsv3($user_map_id, Request $request)
    {
        try {
            $input = $request->all();
            // $clinicId = DB::table('docexa_clinic_user_map')->where('user_map_id', $user_map_id)->first()->id;
            $clinicId = DB::table('docexa_clinic_user_map')->where('user_map_id', $user_map_id)->value('id');

            $clinicId = isset($input['clinic_id']) ? $input['clinic_id'] : $clinicId;
            Log::info(['ckinicid', $clinicId]);
            $consultation_fee = DB::table('docexa_esteblishment_user_map_sku_details')->where('user_map_id', $user_map_id)
                ->where('default_flag', 1)
                ->where('clinic_id', $clinicId)->where('booking_type', 'In clinic Consultation')->first()->fee;
            Log::info(['consultation_fee', $consultation_fee]);
            $vaccine_prices = DB::table('bill_master')
                ->where('usermap_id', $user_map_id)
                ->where('clinic_id', $clinicId)
                ->whereIn('item_name', $input['data'])
                ->select('item_name', 'price')
                ->get();
            $data = [];
            $item_counts = array_count_values($input['data']);

            $total_price = 0;
            $groupedBrands = DB::table('vaccine_brand_groups')->get()->groupBy('brand_name');
            $processedBrands = [];

            if (count($vaccine_prices) > 0) {
                foreach ($vaccine_prices as $vaccine_price) {
                    $itemName = $vaccine_price->item_name;

                    if ($itemName) {
                        $brandInfo = $groupedBrands->first(function ($group) use ($itemName) {
                            return $group->contains('brand_name', $itemName);
                        });
                        if ($brandInfo) {
                            $brandName = $brandInfo->first()->brand_name;
                            if (!in_array($brandName, $processedBrands)) {
                                $data[] = [
                                    'item_name' => $brandName,
                                    'item_price' => round((float) $vaccine_price->price)
                                ];

                                $total_price += round((float) $vaccine_price->price);
                                $processedBrands[] = $brandName;
                            }
                        } else {
                            if (isset($item_counts[$itemName])) {
                                for ($i = 0; $i < $item_counts[$vaccine_price->item_name]; $i++) {
                                    $data[] = [
                                        'item_name' => $vaccine_price->item_name,
                                        'item_price' => round((float) $vaccine_price->price)
                                    ];
                                    $total_price = $total_price + round((float) $vaccine_price->price);
                                }
                            }
                        }
                    }
                }
            }

            $total_price = $total_price + $consultation_fee;
            $data[] = [
                'item_name' => 'Consultation',
                'item_price' => $consultation_fee
            ];


            if ($vaccine_prices) {
                return response()->json(['status' => true, 'message' => 'data retrived successfully', 'items' => $data, 'total_price' => $total_price, 'code' => 200], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'data not found', 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            Log::error(['error' => $th]);
            return response()->json(['status' => false, 'message' => 'Internal server error', 'error' => $th->getMessage()], 500);
        }
    }
}
