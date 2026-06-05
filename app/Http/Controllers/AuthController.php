<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;

class AuthController extends Controller
{
   

public function register(Request $request)
{
    // 1. التحقق من صحة البيانات
   $request->validate([
        'email' => 'required|email|unique:users,email',
        // إضافة confirmed تجعل لارافيل يتوقع حقل باسم password_confirmation
        'password' => 'required|min:6|confirmed', 
    ]);

    // 2. إنشاء مستخدم مؤقت بحالة غير مفعل
    $user = User::create([
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'is_active' => false,
    ]);

    return response()->json([
        'message' => 'تم حفظ الإيميل وكلمة المرور، يرجى إكمال البيانات.',
        'user_id' => $user->id
    ], 200);
}
public function completeProfile(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'name' => 'required|string',
        'last_name' => 'required|string',
        'address' => 'required|string',
    ]);

    $user = User::find($request->user_id);
    $code = rand(100000, 999999);

    $user->update([
        'name' => $request->name,
        'last_name' => $request->last_name,
        'address' => $request->address,
        'verification_code' => $code,
    ]);

    // --- أضف هذا السطر هنا لإرسال الإيميل ---
    Mail::to($user->email)->send(new VerificationCodeMail($code));
    // ----------------------------------------

    return response()->json([
        'message' => 'تم حفظ البيانات وتوليد الكود بنجاح وإرساله للإيميل',
        'code' => $code
    ], 200);
}

public function verifyAccount(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'code' => 'required|numeric',
    ]);

    // نستخدم findOrFail لنعرف إذا كان النظام يجد المستخدم فعلاً
    $user = User::find($request->user_id);

    // للتأكد من الكود (استخدم == بدلاً من != في حال كان هناك اختلاف بسيط في النوع)
    if ((int)$user->verification_code !== (int)$request->code) {
        return response()->json([
            'message' => 'الكود غير صحيح.',
            'db_code' => $user->verification_code, // لنرى ماذا يوجد في قاعدة البيانات
            'sent_code' => $request->code
        ], 400);
    }

    // هنا نقوم بالتحديث المباشر
    $updateResult = $user->update([
        'is_active' => 1, // جرب وضع رقم 1 بدلاً من true
        'verification_code' => null,
    ]);

    // نتحقق إذا تم التحديث فعلياً
    if ($updateResult) {
        return response()->json(['message' => 'تم تفعيل الحساب بنجاح!'], 200);
    } else {
        return response()->json(['message' => 'فشل التحديث في قاعدة البيانات'], 500);
    }
}


public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    // تشخيص المشكلة: هل المستخدم موجود؟
    if (!$user) {
        return response()->json(['message' => 'الإيميل غير موجود'], 404);
    }

    // تشخيص المشكلة: ما هي قيمة is_active التي يراها السيرفر؟
    if ((int)$user->is_active !== 1) {
        return response()->json([
            'message' => 'يجب تفعيل حسابك أولاً',
            'debug_is_active' => $user->is_active,
            'user_id' => $user->id
        ], 403);
    }

    // التحقق من كلمة المرور
    if (!Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'كلمة المرور غير صحيحة'], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'تم تسجيل الدخول بنجاح',
        'access_token' => $token,
        'token_type' => 'Bearer',
    ], 200);
}

public function store(Request $request)
{
    $request->validate([
        'email' => ['required', 'email'],
    ]);

    $status = Password::sendResetLink($request->only('email'));

    return $status == Password::RESET_LINK_SENT
        ? response()->json(['message' => __($status)], 200)
        : response()->json(['message' => __($status)], 400);
}

public function logout(Request $request)
{
    // يقوم بحذف الـ Token الذي يستخدمه المستخدم حالياً
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'تم تسجيل الخروج بنجاح'
    ]);
}
}
