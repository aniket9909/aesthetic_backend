<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConsultType;
use App\Models\ConsultTypeMaster;

class ConsultTypeController extends Controller
{
    // Get all consult types
    public function index()
    {
        return response()->json(ConsultTypeMaster::all());
    }

    // Get a single consult type by ID
    public function show($id)
    {
        $type = ConsultTypeMaster::find($id);
        if (!$type) {
            return response()->json(['message' => 'Not Found'], 404);
        }
        return response()->json($type);
    }

    // Create new consult type
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $type = ConsultTypeMaster::create($validated);
        return response()->json($type, 201);
    }

    // Update consult type
    public function update(Request $request, $id)
    {
        $type = ConsultTypeMaster::find($id);
        if (!$type) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $type->update($validated);
        return response()->json($type);
    }

    // Delete consult type
    public function destroy($id)
    {
        $type = ConsultTypeMaster::find($id);
        if (!$type) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $type->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
