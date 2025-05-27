<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceConsumable;

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
        ServiceConsumable::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
