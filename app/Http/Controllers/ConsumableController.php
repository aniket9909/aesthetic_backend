<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConsumableMaster;

class ConsumableController extends Controller
{
    public function index()
    {
        return response()->json(ConsumableMaster::all());
    }

    public function store(Request $request)
    {
        $data = $request->only(['name', 'unit', 'description']);
        $consumable = ConsumableMaster::create($data);
        return response()->json($consumable, 201);
    }

    public function show($id)
    {
        $consumable = ConsumableMaster::findOrFail($id);
        return response()->json($consumable);
    }

    public function update(Request $request, $id)
    {
        $consumable = ConsumableMaster::findOrFail($id);
        $consumable->update($request->only(['name', 'unit', 'description']));
        return response()->json($consumable);
    }

    public function destroy($id)
    {
        ConsumableMaster::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
