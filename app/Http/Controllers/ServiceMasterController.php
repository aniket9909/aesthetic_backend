<?php

namespace App\Http\Controllers;

use App\BillingModel;
use App\Models\ServiceConsumable;
use App\Models\ServiceGroupMaster;
use Illuminate\Http\Request;
use App\Models\ServiceMaster;
use App\Models\ServiceTransaction;
use App\Patientmaster;
use Illuminate\Support\Facades\DB;

class ServiceMasterController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, "message" => "Data ferch success", 'data' => ServiceMaster::all()]);
    }
    public function getServiceAndPackages($doctorId, $patientId)
    {
        $patient = Patientmaster::where('patient_id', $patientId)->first();
        $doctor = DB::table('docexa_medical_establishments_medical_user_map')->where('id', $doctorId)->join('docexa_doctor_master', 'docexa_doctor_master.pharmaclient_id', 'docexa_medical_establishments_medical_user_map.medical_user_id')->first();

        // Always include service and package data
        $services = ServiceMaster::with('consumable')->get();
        $packages = ServiceGroupMaster::with('groupItems')->get();

        // If either doctor or patient is invalid, return defaults
        if (!$patient || !$doctor) {
            return response()->json([
                'success' => true,
                'message' => 'Fetched general data (no doctor or patient)',
                'data' => compact('services', 'packages')
            ]);
        }

        // Fetch the latest service transaction
        $serviceTransaction = ServiceTransaction::where('patient_id', $patient->patient_id)
            ->where('doctor_id', $doctor->id)
            // ->latest()
            ->get();




        // Default empty arrays
        // $workingSessions = [];
        // $groupInfo = [];
        // $billingData = null;
        // $latestBilling = null;
        // $pendingTrasaction = [];
        // $latestTransaction = $serviceTransaction->sortByDesc('created_at')->first();
        // // dd($latestTransaction);
        // if ($latestTransaction) {
        //     foreach ($latestTransaction->serviceTransactionItems as $item) {
        //         if ($item->remaining_sessions > 0) {
        //             $groupInfo[] = $serviceTransaction['groupInfo'] ?? [];
        //             $workingSessions = $serviceTransaction['serviceTransactionItems'] ?? [];
        //             break;
        //         }

        //         //Method Illuminate\Database\Eloquent\Collection::plunk does not exist.            }


        //         $billingData = BillingModel::whereIn('transaction_id', $serviceTransaction->pluck('id'))
        //             ->where('balanced_amount', '>', 0)
        //             ->get();


        //             $disctinTrasanction = $billingData->unique('id');
        //             dd($disctinTrasanction);



        //         $pendingAmount = 0;

        //         foreach ($disctinTrasanction as $value) {

        //             if ($value->balanced_amount > 0) {
        //                 $pendingTrasaction[] = $value;
        //                 $pendingAmount += $value->balanced_amount;
        //                 // dump($pendingAmount);
        //             }

        //             // Keep updating with the latest model (assuming created_at is used to determine "latest")
        //             if (!$latestBilling || $value->created_at > $latestBilling->created_at) {
        //                 $latestBilling = $value;
        //             }
        //         }
        //         $latestBilling->pending_amount = $pendingAmount;
        //     }





        // $latestTransaction = $serviceTransaction->sortByDesc('created_at')->first();
        // $workingSessions = [];
        // $groupInfo = [];
        // $billingData = null;
        // $latestBilling = [];
        // $pendingTrasaction = [];
        // $pendingAmount = 0;
        // $billingDataAll = BillingModel::whereIn('transaction_id', $serviceTransaction->pluck('id'))
        //     // ->where('balanced_amount', '>', 0)
        //     ->get();
        //     dd($billingDataAll);

        // if ($latestTransaction) {
        //     foreach ($latestTransaction->serviceTransactionItems as $item) {
        //         if ($item->remaining_sessions > 0) {
        //             $groupInfo[] = $latestTransaction->groupInfo ?? [];
        //             $workingSessions = $latestTransaction->serviceTransactionItems ?? [];
        //             $latestBilling = $billingDataAll
        //                 ->where('transaction_id', $latestTransaction->id)
        //                 ->sortByDesc('created_at')
        //                 ->first();
        //             break;
        //         }
        //     }
        //     // b. Pending previous transactions (excluding latest one)
        //     if (
        //         count($billingDataAll) > 1
        //     ) {

        //         $previousTransactions = $billingDataAll
        //             ->where('transaction_id', '!=', $latestTransaction->id)
        //             ->where('balanced_amount', '>', 0)

        //             ->unique('transaction_id');
        //     } else {
        //         $previousTransactions = $billingDataAll
        //             // ->where('transaction_id', '!=', $latestTransaction->id)
        //             ->where('balanced_amount', '>', 0)

        //             ->unique('transaction_id');
        //     }
        //     // $previousTransactions = $billingDataAll
        //     //         // ->where('transaction_id', '!=', $latestTransaction->id)
        //     //         ->unique('transaction_id');
        //     // dd($previousTransactions);
        //     foreach ($previousTransactions as $value) {
        //         $pendingTrasaction[] = $value;
        //         $pendingAmount += $value->balanced_amount;
        //     }

        //     if ($latestBilling) {
        //         $latestBilling->pending_amount = $pendingAmount;
        //     }
        // }

        // $latestTransaction = $serviceTransaction->sortByDesc('created_at')->first();

        // $workingSessions = [];
        // $groupInfo = [];
        // $billingData = null;
        // $latestBilling = null;
        // $pendingTrasaction = [];
        // $pendingAmount = 0;

        // $transactionIDs = $serviceTransaction->pluck('id');
        // $billingDataAll = BillingModel::whereIn('transaction_id', $transactionIDs)->get();

        // if ($latestTransaction) {
        //     // 1. Check if latest transaction has any remaining sessions
        //     $hasRemainingSessions = false;
        //     foreach ($latestTransaction->serviceTransactionItems as $item) {
        //         if ($item->remaining_sessions > 0) {
        //             $hasRemainingSessions = true;
        //             break;
        //         }
        //     }

        //     // 2. Fetch latest billing for latest transaction
        //     $latestBilling = $billingDataAll
        //         ->where('transaction_id', $latestTransaction->id)
        //         ->sortByDesc('created_at')
        //         ->first();

        //     // Set working sessions and group info if remaining sessions exist
        //     if ($hasRemainingSessions) {
        //         $groupInfo[] = $latestTransaction->groupInfo ?? [];
        //         $workingSessions = $latestTransaction->serviceTransactionItems ?? [];
        //         $billingData = $latestBilling;
        //     }

        //     // 3. Process pending transactions

        //     if ($billingDataAll->count() === 1) {
        //         if ($latestBilling && $latestBilling->balanced_amount > 0) {
        //             // Check if any service item still has remaining sessions
        //             $hasRemainingSessions = false;
        //             foreach ($latestTransaction->serviceTransactionItems as $item) {
        //                 if ($item->remaining_sessions > 0) {
        //                     $hasRemainingSessions = true;
        //                     break;
        //                 }
        //             }

        //             if ($hasRemainingSessions) {
        //                 // Not complete → treat as active billing
        //                 $billingData = $latestBilling;
        //             } else {
        //                 // Complete but pending → treat as pending transaction
        //                 $pendingTrasaction[] = $latestBilling;
        //                 $pendingAmount += $latestBilling->balanced_amount;
        //             }
        //         }
        //     } else {

        //         // Multiple transactions: check all except latest
        //         // $previousTransactions = $billingDataAll
        //         //     ->where('transaction_id', '!=', $latestTransaction->id)
        //         //     // ->where('balanced_amount', '>', 0)
        //         //     ->unique('transaction_id');
        //         //     dd($previousTransactions);

        //         // foreach ($previousTransactions as $value) {
        //         //     $pendingTrasaction[] = $value;
        //         //     $pendingAmount += $value->balanced_amount;
        //         // }

        //         // // If latest billing exists, attach the total pending amount
        //         // if ($latestBilling) {
        //         //     $latestBilling->pending_amount = $pendingAmount;
        //         // }
        //         $previousTransactions = $billingDataAll
        //             ->where('transaction_id', '!=', $latestTransaction->id)
        //             ->unique('transaction_id');

        //         // Default assumption: no billingData yet
        //         $billingData = null;

        //         foreach ($previousTransactions as $billing) {
        //             $transactionId = $billing->transaction_id;

        //             // Find the transaction for this billing
        //             $transaction = $serviceTransaction->firstWhere('id', $transactionId);

        //             if (!$transaction) {
        //                 continue;
        //             }

        //             // Check if any item in this transaction has remaining sessions
        //             $hasRemainingSessions = false;
        //             foreach ($transaction->serviceTransactionItems as $item) {
        //                 if ($item->remaining_sessions > 0) {
        //                     $hasRemainingSessions = true;
        //                     break;
        //                 }
        //             }

        //             if ($hasRemainingSessions) {
        //                 // If no billingData assigned yet, assign this one
        //                 if (!$billingData) {
        //                     $billingData = $billing;
        //                 }
        //             } elseif ($billing->balanced_amount > 0) {
        //                 // Otherwise it's a completed billing with pending amount
        //                 $pendingTrasaction[] = $billing;
        //                 $pendingAmount += $billing->balanced_amount;
        //             }
        //         }

        //         // Attach total pending to latest billing if available
        //         if ($latestBilling) {
        //             $latestBilling->pending_amount = $pendingAmount;
        //         }
        //     }
        // }  


        $billingData = null;
        $groupInfoList = [];
        $workingSessionsList = [];
        $pendingTransactions = [];
        $pendingAmount = 0;

        $billingDataAll = BillingModel::whereIn('transaction_id', $serviceTransaction->pluck('id'))->get();

        // Keep track of transaction_ids added to billingData
        $activeTransactionIds = [];
        $latestTransaction = $serviceTransaction->sortByDesc('created_at')->first();

        if ($latestTransaction) {
            $transactionId = $latestTransaction->id;

            $hasRemainingSession = $latestTransaction->serviceTransactionItems->contains(function ($item) {
                return $item->remaining_sessions > 0;
            });

            if ($hasRemainingSession) {
                // Add to activeTransactionIds
                $activeTransactionIds[] = $transactionId;

                // Collect group info & sessions
                $groupInfoList = $latestTransaction->groupInfo ?? [];
                $workingSessionsList = $latestTransaction->serviceTransactionItems;

                // Get billing for this transaction
                $billing = $billingDataAll
                    ->where('transaction_id', $transactionId)
                    ->sortByDesc('created_at')
                    ->first();

                if ($billing) {
                    $billingData = $billing;
                }
            }
        }

        // Now build pendingTransactions: only those NOT in activeTransactionIds
        foreach ($billingDataAll as $billing) {
            if (
                !in_array($billing->transaction_id, $activeTransactionIds) &&
                $billing->balanced_amount > 0
            ) {
                $pendingTransactions[] = $billing;
                $pendingAmount += $billing->balanced_amount;
            }
        }

        // Optionally attach pending amount to each active billing
        // foreach ($billingData as $bill) {
        //     $bill->pending_amount = $pendingAmount;
        // }
        return response()->json([
            'success' => true,
            'message' => 'Session and service/package data fetched',
            'data' => [
                'services' => $services,
                'packages' => $packages,
                "groupInfo" => $groupInfoList,
                'workingSessions' => $workingSessionsList,
                "billingData" => $billingData,
                "outstandingAmount" => $pendingAmount,
                'pendingTransactions' => $pendingTransactions
            ]
        ]);
    }


    public function store(Request $request)
    {
        try {

            $data = $request->all();

            // Manual validation
            if (empty($data['name']) || !isset($data['base_price'])) {
                return response()->json(['error' => 'Name and Base Price are required'], 422);
            }

            $service = ServiceMaster::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'base_price' => $data['base_price'],
                'category' => $data['category'] ?? null,
                'is_tax_applied' => $data['is_tax_applied'] ?? false,
                'tax_percent' => $data['tax_percent'] ?? 0,
            ]);

            return response()->json(['success' => true, 'message' => 'Service created', 'data' => $service], 201);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'erro in created the service',
                'erroMessage' => $th->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function show($id)
    {
        $service = ServiceMaster::find($id);
        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $service]);
    }

    public function update(Request $request, $id)
    {
        $service = ServiceMaster::find($id);
        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }

        $service->update($request->all());
        return response()->json(['message' => 'Service updated']);
    }

    public function destroy($id)
    {
        $service = ServiceMaster::find($id);
        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }

        $service->delete();
        return response()->json(['message' => 'Service deleted']);
    }
}
