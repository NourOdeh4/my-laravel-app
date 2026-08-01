<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;

class StoreCategoryController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Category::all()
        ]);
    }

    public function show($id)
    {
        $category = Category::with('products')->findOrFail($id);

        return response()->json([
            'data' => $category
        ]);
    }
}
