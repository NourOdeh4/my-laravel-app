<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
   public function createOrder(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }


    $request->validate([
        'shipping_address' => 'required|string',
        'payment_method' => 'required|in:cash,card'
    ]);


    $cartItems = CartItem::with('product')
        ->where('user_id', $user->id)
        ->whereNull('status')
        ->get();


    if ($cartItems->isEmpty()) {
        return response()->json([
            'message' => 'السلة فارغة'
        ], 400);
    }


    DB::beginTransaction();

    try {

        $total = 0;

        foreach ($cartItems as $item) {
            $total += $item->product->price * $item->quantity;
        }


        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => $total,
            'status' => 'pending',
            'shipping_address' => $request->shipping_address,
            'payment_method' => $request->payment_method
        ]);


        foreach ($cartItems as $item) {

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price
            ]);


            // إخفاء المنتج من السلة بانتظار الموافقة
            $item->status = 'pending';
            $item->save();
        }


        DB::commit();


        return response()->json([
            'message' => 'تم إنشاء الطلب بنجاح',
            'order_id' => $order->id
        ]);


    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'message' => $e->getMessage()
        ], 500);
    }
}
    // عرض طلبات المستخدم
    public function getOrders()
    {
        $orders = Order::with('items.product')
                       ->where('user_id', auth()->id())
                       ->get();

        return response()->json(['data' => $orders]);
    }

    // عرض تفاصيل طلب واحد
    public function getOrderDetails($id)
{
    $order = Order::with('items.product')
                  ->where('user_id', auth()->id())
                  ->findOrFail($id);

    return response()->json([
        'data' => $order
    ]);
}

    // حذف طلب
   public function deleteOrder($id)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $order = Order::where('user_id', $user->id)
        ->findOrFail($id);

    DB::beginTransaction();

    try {

        // جلب منتجات الطلب
        $orderItems = OrderItem::where('order_id', $order->id)->get();

        foreach ($orderItems as $item) {

            // إرجاع الكمية للمخزون فقط
            $product = Product::find($item->product_id);

            if ($product) {
                $product->stock += $item->quantity;
                $product->save();
            }
        }

        // حذف تفاصيل الطلب
        OrderItem::where('order_id', $order->id)->delete();

        // حذف الطلب
        $order->delete();

        DB::commit();

        return response()->json([
            'message' => 'تم حذف الطلب'
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'message' => $e->getMessage()
        ], 500);

    }
}
}

