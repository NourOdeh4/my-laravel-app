<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Category::all()
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $category->update([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'تم تعديل التصنيف'
        ]);
    }

    public function destroy($id)
    {
        Category::findOrFail($id)->delete();

        return response()->json([
            'message' => 'تم حذف التصنيف'
        ]);
    }
    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string'
    ]);

    Category::create([
        'name' => $request->name
    ]);

    return response()->json([
        'message' => 'تم إضافة التصنيف بنجاح'
    ]);
}

}
