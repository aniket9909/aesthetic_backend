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
            ->latest()
            ->first();


        // Default empty arrays
        $workingSessions = [];
        $billingData = null;

        if ($serviceTransaction) {
            foreach ($serviceTransaction->serviceTransactionItems as $item) {
                if ($item->remaining_sessions > 0) {
                    $workingSessions = $serviceTransaction->serviceTransactionItems;
                    break;
                }
            }
            $billingData = BillingModel::where('transaction_id', $serviceTransaction->id)
                ->where('balanced_amount', ">", 0)
                ->first();
        }


        return response()->json([
            'success' => true,
            'message' => 'Session and service/package data fetched',
            'data' => [
                'services' => $services,
                'packages' => $packages,
                'workingSessions' => $workingSessions,
                "billingData" => $billingData
            ]
        ]);
    }


    public function store(Request $request)
    {
        try {
            //code...

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
