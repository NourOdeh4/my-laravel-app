<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
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

        $total = 0;

        foreach ($cartItems as $item) {
            $total += $item->product->price * $item->quantity;
        }

        $order = Order::create([
            'user_id' => auth()->id(),
            'total_price' => $total,
            'status' => 'pending'
        ]);

        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price
            ]);
        }

        // تفريغ السلة بعد الطلب
        CartItem::where('user_id', auth()->id())->delete();

        return response()->json([
            'message' => 'تم إنشاء الطلب بنجاح',
            'order_id' => $order->id
        ]);
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
        $order = Order::where('user_id', auth()->id())->findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'تم حذف الطلب']);
    }
}

