<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
=======
use App\Models\Product;
use Illuminate\Http\Request;
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7

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

<<<<<<< HEAD
  public function update(Request $request, $id)
{
    $user = Auth::user();

    if (!$user || $user->role !== 'super_admin') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }


    $request->validate([
        'name' => 'nullable|string',
        'description' => 'nullable|string',
        'price' => 'nullable|numeric',
        'stock' => 'nullable|integer',
        'image' => 'nullable|string',
        'category_name' => 'nullable|string'
    ]);


    $product = Product::findOrFail($id);


    $data = $request->only([
        'name',
        'description',
        'price',
        'stock',
        'image'
    ]);


    // إذا تم إرسال اسم تصنيف نغير التصنيف
    if ($request->filled('category_name')) {

        $category = Category::where('name', $request->category_name)
            ->first();

        if (!$category) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }

        $data['category_id'] = $category->id;
    }


    $product->update($data);


    return response()->json([
        'message' => 'تم تعديل المنتج بنجاح',
        'data' => $product
    ]);
}

=======
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

>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7
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

<<<<<<< HEAD
     public function store(Request $request)
{
    $user = Auth::user();

    if (!$user || $user->role !== 'super_admin') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }


=======
    public function store(Request $request)
{
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7
    $request->validate([
        'name' => 'required|string',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'stock' => 'required|integer',
        'image' => 'nullable|string',
<<<<<<< HEAD
        'category_name' => 'required|string'
    ]);


    $category = Category::where('name', $request->category_name)
        ->first();


    if (!$category) {
        return response()->json([
            'message' => 'Category not found'
        ], 404);
    }


    $product = Product::create([

        'name' => $request->name,

        'description' => $request->description,

        'price' => $request->price,

        'stock' => $request->stock,

        'image' => $request->image,

        'category_id' => $category->id

    ]);


    return response()->json([
        'message' => 'تم إضافة المنتج بنجاح',
        'data' => $product
    ],201);

}
}
=======
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
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7
