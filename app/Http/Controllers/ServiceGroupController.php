<?php

namespace App\Http\Controllers;

use App\Models\ServiceGroupItems;
use Illuminate\Http\Request;
use App\Models\ServiceGroupMaster;
use Illuminate\Support\Facades\DB;

class ServiceGroupController extends Controller
{
    public function index()
    {
        $groups = ServiceGroupMaster::with('groupItems')->get();
        return response()->json(['success' => true, 'data' => $groups]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        if (empty($data['name']) || !isset($data['package_amount']) || !isset($data['services'])) {
            return response()->json(['error' => 'Name, Package Amount, and Services are required'], 422);
        }

        try {
            DB::beginTransaction();
            //code...

            $group = ServiceGroupMaster::create([
                'name' => $data['name'],
                'package_type' => $data['package_type'] ?? null,
                'package_amount' => $data['package_amount'],
                'total_discount' => $data['total_discount'] ?? 0,
                'total_tax' => $data['total_tax'] ?? 0,
                'validity_months' => $data['validity_months'] ?? 0,
                'created_by' => $data['created_by'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]);
            
            $items = [];
            foreach ($data['services'] as $service) {
                if (!isset($service['id'])) continue;

                $items[] = ServiceGroupItems::create([
                    'group_master_id' => $group->id,
                    'service_master_id' => $service['id'],
                    'custom_price' => $service['base_price'] ?? null,
                    'tax_amount' => ($service['total'] ?? 0.0) - ($service['base_price'] ?? 0.0),
                    'discount_amount' => $service['discount_amount'] ?? 0,
                    'total_sessions' => $service['total_sessions'] ?? 0,
                    'completed_sessions' => $service['completed_sessions'] ?? 0,
                    'is_tax_inclusive' => $service['is_tax_inclusive'] ?? false,
                    'total'=>$service['total'] ?? 0.0,
                    'tax_per'=>$service['tax'] ?? 0.0,
                ]);
            }
            DB::commit();
            return response()->json(["success" => true, 'message' => 'Group created', 'data' => ["package" => $group, "packageItems" => $items]], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(["success" => false, "errorMessage" => $th->getMessage(), 'message' => 'failed to create group', 'data' => []], 500);
        }
    }

    public function show($id)
    {
        $group = ServiceGroupMaster::with('services')->find($id);
        if (!$group) return response()->json(['error' => 'Group not found'], 404);
        return response()->json($group);
    }

    public function update(Request $request, $id)
    {
        $group = ServiceGroupMaster::find($id);
        if (!$group) return response()->json(['error' => 'Group not found'], 404);

        $group->update($request->all());
        return response()->json(['message' => 'Group updated']);
    }

    public function destroy($id)
    {
        $group = ServiceGroupMaster::find($id);
        if (!$group) return response()->json(['error' => 'Group not found'], 404);

        $group->delete();
        ServiceGroupItems::where('group_master_id', $id)->delete();
        return response()->json(['message' => 'Group and items deleted']);
    }
}
