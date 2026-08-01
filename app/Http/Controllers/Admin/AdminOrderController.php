<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('items.product')->get();
        return response()->json(['data' => $orders]);
    }

    public function show($id)
    {
        $order = Order::with('items.product')->findOrFail($id);
        return response()->json(['data' => $order]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'تم تحديث حالة الطلب']);
    }

    public function approve($id)
    {
        $order = Order::findOrFail($id);
        $order->status = 'approved';
        $order->save();

        return response()->json(['message' => 'تمت الموافقة على الطلب']);
    }

    public function reject($id)
    {
        $order = Order::findOrFail($id);
        $order->status = 'rejected';
        $order->save();

        return response()->json(['message' => 'تم رفض الطلب']);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'تم حذف الطلب']);
    }
}
