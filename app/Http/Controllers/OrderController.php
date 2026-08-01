<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
<<<<<<< HEAD
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
=======
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // إنشاء طلب جديد
    public function createOrder()
    {
        $cartItems = CartItem::where('user_id', auth()->id())->get();

        if ($cartItems->count() == 0) {
            return response()->json(['message' => 'السلة فارغة'], 400);
        }
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7

        $total = 0;

        foreach ($cartItems as $item) {
            $total += $item->product->price * $item->quantity;
        }

<<<<<<< HEAD

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => $total,
            'status' => 'pending',
            'shipping_address' => $request->shipping_address,
            'payment_method' => $request->payment_method
        ]);


        foreach ($cartItems as $item) {

=======
        $order = Order::create([
            'user_id' => auth()->id(),
            'total_price' => $total,
            'status' => 'pending'
        ]);

        foreach ($cartItems as $item) {
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price
            ]);
<<<<<<< HEAD


            // إخفاء المنتج من السلة بانتظار الموافقة
            $item->status = 'pending';
            $item->save();
        }


        DB::commit();

=======
        }

        // تفريغ السلة بعد الطلب
        CartItem::where('user_id', auth()->id())->delete();
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7

        return response()->json([
            'message' => 'تم إنشاء الطلب بنجاح',
            'order_id' => $order->id
        ]);
<<<<<<< HEAD


    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'message' => $e->getMessage()
        ], 500);
    }
}
=======
    }

>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7
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
<<<<<<< HEAD
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
=======
    public function deleteOrder($id)
    {
        $order = Order::where('user_id', auth()->id())->findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'تم حذف الطلب']);
    }
}
>>>>>>> 1f07254a9c81854a5a6b734cb8f37e452278f2e7

