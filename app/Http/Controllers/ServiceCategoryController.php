<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceCategory;

class ServiceCategoryController extends Controller
{
    public function index()
    {
        return response()->json(ServiceCategory::all());
    }

    public function store(Request $request)
    {
       

        $category = ServiceCategory::create($request->only('name', 'description'));

        return response()->json([
            'success' => true,
            'message' => 'Category created',
            'data' => $category
        ]);
    }
}
