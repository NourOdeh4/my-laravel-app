<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Password;

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
        'phone' => 'required|string|max:15', 
    ]);

    $user = User::find($request->user_id);
    $code = rand(100000, 999999);

    // --- هنا التعديل: أضفنا سطر الـ phone ---
    $user->update([
        'name' => $request->name,
        'last_name' => $request->last_name,
        'address' => $request->address,
        'phone' => $request->phone, // هذا هو السطر الناقص!
        'verification_code' => $code,
    ]);

    Mail::to($user->email)->send(new VerificationCodeMail($code));

    return response()->json([
        'message' => 'تم حفظ البيانات وتوليد الكود بنجاح وإرساله للإيميل',
        'code' => $code
    ], 200);
}
public function verifyAccount(Request $request)
{
    // 1. التحقق من البيانات
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'code' => 'required',
    ]);

    // 2. البحث عن المستخدم
    $user = \App\Models\User::find($request->user_id);

    // 3. التحقق من صحة الكود وتاريخ انتهائه
    if ($user->verification_code == $request->code) {
        
        // التحقق من أن الكود لم ينتهِ (اختياري، تأكدي من توافقه مع كودك)
        
        // 4. التعديل الصحيح (هنا يكمن سر الحل)
        $user->is_active = 1;
        $user->verification_code = null; // مسح الكود بعد الاستخدام
        $user->save(); // حفظ التغييرات في قاعدة البيانات

        return response()->json(['message' => 'تم تفعيل الحساب بنجاح!'], 200);
    }

    return response()->json(['message' => 'الكود غير صحيح'], 400);
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


public function forgotPassword(Request $request)
{
    $request->validate(['email' => 'required|email']);

    // إرسال رابط إعادة التعيين
    $status = Password::sendResetLink($request->only('email'));

    return $status === Password::RESET_LINK_SENT
        ? response()->json(['message' => 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك'], 200)
        : response()->json(['message' => 'فشل في إرسال الرابط'], 400);
}
public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? response()->json(['message' => 'تم تغيير كلمة المرور بنجاح'], 200)
        : response()->json(['message' => 'فشل تغيير كلمة المرور، التوكن غير صالح'], 400);
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
