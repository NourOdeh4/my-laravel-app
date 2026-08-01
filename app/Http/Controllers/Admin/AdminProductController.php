<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Product::all()]);
    }

    public function show($id)
    {
        return response()->json(['data' => Product::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->update($request->all());

        return response()->json(['message' => 'تم تعديل المنتج']);
    }

    public function updateStock(Request $request, $id)
    {
        $request->validate([
            'stock' => 'required|integer'
        ]);

        $product = Product::findOrFail($id);
        $product->stock = $request->stock;
        $product->save();

        return response()->json(['message' => 'تم تحديث المخزون']);
    }

    public function approve($id)
    {
        $product = Product::findOrFail($id);
        $product->status = 'approved';
        $product->save();

        return response()->json(['message' => 'تمت الموافقة على المنتج']);
    }

    public function reject($id)
    {
        $product = Product::findOrFail($id);
        $product->status = 'rejected';
        $product->save();

        return response()->json(['message' => 'تم رفض المنتج']);
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();

        return response()->json(['message' => 'تم حذف المنتج']);
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'stock' => 'required|integer',
        'image' => 'nullable|string',
        'device_id' => 'nullable|integer'
    ]);

    $product = Product::create([
        'name' => $request->name,
        'description' => $request->description,
        'price' => $request->price,
        'stock' => $request->stock,
        'image' => $request->image,
        'device_id' => $request->device_id,
        'status' => 'pending'   // المنتج ينتظر موافقة الأدمن
    ]);

    return response()->json([
        'message' => 'تم إضافة المنتج بنجاح',
        'data' => $product
    ]);
}

}
