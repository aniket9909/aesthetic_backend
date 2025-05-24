<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceMaster;

class ServiceMasterController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, "message" => "Data ferch success", 'data' => ServiceMaster::all()]);
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
