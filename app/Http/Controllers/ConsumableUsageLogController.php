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

    public function destroy($id)
    {
        ConsumableUsageLog::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
