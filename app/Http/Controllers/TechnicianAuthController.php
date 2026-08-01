<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
   use App\Models\Technician;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TechnicianAuthController extends Controller
{



public function register(Request $request)
{
    // 1. Validation
    $request->validate([
        'email' => 'required|email|unique:technicians,email',
        'password' => 'required|min:6|confirmed',
    ]);

    // 2. Create technician
    $technician = Technician::create([
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'is_active' => false,
    ]);

    // 3. Create token (Sanctum)
    $token = $technician->createToken('tech_token')->plainTextToken;

    // 4. Return response
    return response()->json([
        'message' => 'تم إنشاء حساب الفني بنجاح',
        'technician_id' => $technician->id,
        'access_token' => $token,
        'token_type' => 'Bearer',
    ], 201);
}}
