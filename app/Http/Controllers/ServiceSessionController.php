<?php

namespace App\Http\Controllers;

use App\Models\ServiceTransaction;
use Illuminate\Http\Request;

class ServiceSessionController extends Controller
{


    public function getSessionsByPatientId($esteblishmentusermapID, $patientId)
    {


        if (!$esteblishmentusermapID || !$patientId) {
            return response()->json([
                'status' => false,
                'message' => 'doctor_id and patient_id are required.'
            ], 400);
        }

        $transactions = ServiceTransaction::with('serviceTransactionItems.sessions')
            ->where('doctor_id', $esteblishmentusermapID)
            ->where('patient_id', $patientId)
            ->get();



        $data = [];

        foreach ($transactions as $transaction) {
            $transactionData = [
                'transaction_id' => $transaction->id,
                'enrollment_type' => $transaction->enrollment_type,
                'group_master_id' => $transaction->group_master_id,
                'total_amount' => $transaction->total_amount,
                'total_discount' => $transaction->total_discount,
                'total_tax' => $transaction->total_tax,
                'enrollment_type' => $transaction->enrollment_type,
                "groupInfo" => $transaction->groupInfo,
                'services' => []
            ];

            foreach ($transaction->serviceTransactionItems as $item) {
                $itemData = [
                    'service_item_id' => $item->id,
                    'service_name' => $item->service->name ?? null,
                    'custom_price' => $item->custom_price,
                    'tax_amount' => $item->tax_amount,
                    'discount_amount' => $item->discount_amount,
                    'is_tax_inclusive' => $item->is_tax_inclusive,
                    'total_sessions' => $item->total_sessions,
                    'completed_sessions' => $item->completed_sessions,
                    'remaining_sessions' => $item->remaining_sessions,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'sub_total' => $item->sub_total,
                    'sessions' => []
                ];

                foreach ($item->sessions as $session) {
                    $itemData['sessions'][] = [
                        'session_number' => $session->session_number,
                        'conducted_at' => $session->conducted_at,
                        'remarks' => $session->remarks,
                    ];
                }

                $transactionData['services'][] = $itemData;
            }

            $data[] = $transactionData;
        }

        return response()->json([
            'status' => true,
            'message' => 'Session data fetched successfully.',
            'data' => $data
        ]);
    }
}
