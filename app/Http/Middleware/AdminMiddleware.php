<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $role = auth()->user()->role;

        if (!in_array($role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        return $next($request);
    }
}
