<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // إضافة منتج للسلة
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $item = CartItem::where('user_id', auth()->id())
                        ->where('product_id', $request->product_id)
                        ->first();

        if ($item) {
            // إذا المنتج موجود بالسلة، نزيد الكمية
            $item->quantity += $request->quantity;
            $item->save();
        } else {
            // إذا المنتج جديد، نضيفه للسلة
            CartItem::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);
        }

        return response()->json([
            'message' => 'Product added to cart successfully'
        ]);
    }

    // عرض السلة
    public function getCart()
    {
        $items = CartItem::with('product')
                         ->where('user_id', auth()->id())
                         ->get();

        return response()->json([
            'data' => $items
        ]);
    }

    // تعديل كمية منتج
    public function updateQuantity(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $item = CartItem::where('user_id', auth()->id())->findOrFail($id);
        $item->quantity = $request->quantity;
        $item->save();

        return response()->json([
            'message' => 'Quantity updated successfully'
        ]);
    }

    // حذف منتج من السلة
    public function removeItem($id)
    {
        $item = CartItem::where('user_id', auth()->id())->findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'Item removed from cart'
        ]);
    }
}
