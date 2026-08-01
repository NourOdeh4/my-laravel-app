<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class AdminStatsController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_orders' => Order::count(),
            'total_products' => Product::count(),
            'total_users' => User::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'approved_orders' => Order::where('status', 'approved')->count(),
            'rejected_orders' => Order::where('status', 'rejected')->count(),
        ]);
    }
}
