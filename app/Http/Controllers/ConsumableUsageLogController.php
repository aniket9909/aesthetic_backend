<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConsumableUsageLog;

class ConsumableUsageLogController extends Controller
{
    public function index()
    {
        return response()->json(ConsumableUsageLog::all());
    }

    public function store(Request $request)
    {
        $data = $request->only([
            'enrollment_transaction_id',
            'enrollment_item_id',
            'consumable_id',
            'used_quantity',
            'used_unit',
            'used_by_doctor_id',
            'remarks'
        ]);
        $log = ConsumableUsageLog::create($data);
        return response()->json($log, 201);
    }

    public function show($id)
    {
        return response()->json(ConsumableUsageLog::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $log = ConsumableUsageLog::findOrFail($id);
        $log->update($request->only([
            'used_quantity',
            'used_unit',
            'remarks'
        ]));
        return response()->json($log);
    }
    // The updateAll method allows batch updating of multiple ConsumableUsageLog records.
    public function updateAll(Request $request)
    {
        try {
            //code...

            $data = $request->all();
            $updatedLogs = [];

            foreach ($data as $service) {
                if (isset($service['consumable']) && is_array($service['consumable'])) {
                    foreach ($service['consumable'] as $item) {
                        if (isset($item['id']) && isset($item['used_quantity'])) {
                            $log = ConsumableUsageLog::find($item['id']);
                            if ($log) {
                                $log->update([
                                    'used_quantity' => $item['used_quantity'],
                                    'remarks' => $item['remarks'] ?? $log->remarks,
                                ]);
                                $updatedLogs[] = $log;
                            }
                        }
                    }
                }
            }

            return response()->json([
                'message' => 'Consumable usage logs updated successfully',
                'updated_logs' => $updatedLogs
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'message' => 'Error updating consumable usage logs',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        ConsumableUsageLog::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
