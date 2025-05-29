<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceConsumable;
use App\Models\ServiceMaster;
use Illuminate\Support\Facades\DB;

class ServiceConsumableController extends Controller
{
    public function index()
    {
        return response()->json(ServiceConsumable::all());
    }

    public function store(Request $request)
    {
        $data = $request->only(['service_master_id', 'consumable_id', 'quantity']);
        $record = ServiceConsumable::create($data);
        return response()->json($record, 201);
    }

    public function show($id)
    {
        return response()->json(ServiceConsumable::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $record = ServiceConsumable::findOrFail($id);
        $record->update($request->only(['service_master_id', 'consumable_id', 'quantity']));
        return response()->json($record);
    }

    public function destroy($id)
    {
        // Delete the related ServiceMaster record first
        ServiceMaster::where('id', $id)->delete();

        ServiceConsumable::where('service_master_id', $id)->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
    public function storeServiceConsumable(Request $request)
    {
        DB::beginTransaction();

        try {
            // Create the service
            $service = ServiceMaster::create([
                'name' => isset($request->name) ? $request->name : null,
                'description' => isset($request->description) ? $request->description : null,
                'base_price' => isset($request->charges) ? $request->charges : 0,
                'category' => isset($request->category) ? $request->category : null,
                'is_tax_applied' => isset($request->is_tax_applied) ? $request->is_tax_applied : 0,
                'tax_percent' => isset($request->tax_percent) ? $request->tax_percent : 18,
                'is_active' => isset($request->is_active) ? $request->is_active : 1,
            ]);

            // Create consumables
            foreach ($request->consumables ?? [] as $item) {
                ServiceConsumable::create([
                    'service_master_id' => $service->id,
                    'consumable_id' => $item['id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service and consumables created successfully',
                'data' => $service
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateServiceConsumable(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // Update the service
            $service = ServiceMaster::findOrFail($id);
            $service->update([
                'name' => isset($request->name) ? $request->name : $service->name,
                'description' => isset($request->description) ? $request->description : $service->description,
                'base_price' => isset($request->charges) ? $request->charges : $service->base_price,
                'category' => isset($request->category) ? $request->category : $service->category,
                'is_tax_applied' => isset($request->is_tax_applied) ? $request->is_tax_applied : $service->is_tax_applied,
                'tax_percent' => isset($request->tax_percent) ? $request->tax_percent : $service->tax_percent,
                'is_active' => isset($request->is_active) ? $request->is_active : $service->is_active,
            ]);

            // Remove existing consumables
            ServiceConsumable::where('service_master_id', $service->id)->delete();

            // Add new consumables
            foreach ($request->consumables ?? [] as $item) {
                ServiceConsumable::create([
                    'service_master_id' => $service->id,
                    'consumable_id' => $item['id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service and consumables updated successfully',
                'data' => $service
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update service',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
