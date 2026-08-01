<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
class ProductController extends Controller
{
    // عرض كل المنتجات
    public function index()
    {
        $products = Product::paginate(10);

        return response()->json([
            'data' => $products
        ]);
    }

    // عرض تفاصيل منتج واحد
    public function show($id)
    {
        $product = Product::findOrFail($id);

        return response()->json([
            'data' => $product
        ]);
    }

    // بحث عن منتج 
   public function search(Request $request)
{
    $query = $request->query('query');

    $products = Product::where('name', 'LIKE', "%$query%")
        ->orWhere('description', 'LIKE', "%$query%")
        ->get();

    return response()->json([
        'data' => $products
    ]);
}


}
