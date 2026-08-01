<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
<<<<<<< HEAD
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
=======
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7

class CartController extends Controller
{
    // إضافة منتج للسلة
<<<<<<< HEAD
   public function addToCart(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1'
    ]);

    $product = Product::findOrFail($request->product_id);

    // التحقق من توفر الكمية
    if ($request->quantity > $product->stock) {
        return response()->json([
            'message' => 'Requested quantity exceeds available stock'
        ], 422);
    }

    DB::beginTransaction();

    try {

        $item = CartItem::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($item) {

            // التأكد أن مجموع الكمية لا يتجاوز المخزون
            if (($item->quantity + $request->quantity) > ($item->quantity + $product->stock)) {
                return response()->json([
                    'message' => 'Requested quantity exceeds available stock'
                ], 422);
            }

            $item->quantity += $request->quantity;
            $item->save();

        } else {

            CartItem::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity
            ]);

        }

        // خصم الكمية من المخزون
        $product->stock -= $request->quantity;
        $product->save();

        DB::commit();

        return response()->json([
            'message' => 'Product added to cart successfully'
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'message' => $e->getMessage()
        ], 500);

    }
}
public function getCart()
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $items = CartItem::with('product')
        ->where('user_id', $user->id)
        ->get();

    $cart = $items->map(function ($item) {

        return [
            'product_name' => $item->product->name,
            'quantity' => $item->quantity,
            'unit_price' => $item->product->price,
            'total_price' => $item->quantity * $item->product->price,
        ];

    });

    return response()->json([
        'data' => $cart
    ]);
}
    // تعديل كمية منتج
   public function updateQuantity(Request $request, $id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request->validate([
        'quantity' => 'required|integer|min:1'
    ]);

    $item = CartItem::where('user_id', $user->id)
        ->findOrFail($id);

    $product = Product::findOrFail($item->product_id);

    DB::beginTransaction();

    try {

        $oldQuantity = $item->quantity;
        $newQuantity = $request->quantity;

        // إذا زادت الكمية
        if ($newQuantity > $oldQuantity) {

            $difference = $newQuantity - $oldQuantity;

            if ($difference > $product->stock) {
                return response()->json([
                    'message' => 'Requested quantity exceeds available stock'
                ], 422);
            }

            $product->stock -= $difference;
        }

        // إذا نقصت الكمية
        elseif ($newQuantity < $oldQuantity) {

            $difference = $oldQuantity - $newQuantity;

            $product->stock += $difference;
        }

        $product->save();

        $item->quantity = $newQuantity;
        $item->save();

        DB::commit();

        return response()->json([
            'message' => 'Quantity updated successfully'
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'message' => $e->getMessage()
        ], 500);

    }
}
public function removeItem($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $item = CartItem::where('user_id', $user->id)
        ->findOrFail($id);

    DB::beginTransaction();

    try {

        $product = Product::findOrFail($item->product_id);

        // إعادة الكمية إلى المخزون
        $product->stock += $item->quantity;
        $product->save();

        // حذف المنتج من السلة
        $item->delete();

        DB::commit();

        return response()->json([
            'message' => 'Item removed from cart'
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'message' => $e->getMessage()
        ], 500);

    }
}
}
=======
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
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7
