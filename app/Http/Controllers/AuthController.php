<?php

namespace App\Http\Controllers;
//use Illuminate\Support\Facades\Mail;
//use Illuminate\Support\Facades\Log;
//use App\Mail\VerificationCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{


public function register(Request $request)
{
    $request->validate([
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6|confirmed',
        'role' => 'nullable|in:user,technician',
    ]);
    $verificationCode=rand(100000,999999);

    $role = $request->role ?? 'user';

    $user = User::create([
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'verification_code'=>$verificationCode,
        'role' => $role,
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'تم إنشاء الحساب بنجاح',
        'token' => $token,
        'role' => $user->role,
        'user_id' => $user->id,
        'verificationCode'=>$verificationCode
    ], 201);
}
public function sendVerificationCode(Request $request)
{
    $user = $request->user();

    if ($user->is_active) {
        return response()->json([
            'message' => 'الحساب مفعل بالفعل'
        ], 400);
    }

    $code = rand(100000,999999);

    $user->update([
        'verification_code' => $code
    ]);

    try {
        Mail::to($user->email)->send(new VerificationCodeMail($code));

        return response()->json([
            'message' => 'تم إرسال كود التحقق بنجاح'
            ,'code'=>$code
        ], 200);

    } catch (\Exception $e) {

        Log::error('Mail failed: ' . $e->getMessage());

        return response()->json([
           // 'message' => 'فشل إرسال الإيميل، لكن هذا هو الكود للاختبار',
            'code' => $code
        ], 200);
    }

}


public function completeProfile(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'last_name' => 'required|string',
        'address' => 'required|string',
        'phone' => 'required|string|max:15',
    ]);


    $user = $request->user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $user->update([
        'name' => $request->name,
        'last_name' => $request->last_name,
        'address' => $request->address,
        'phone' => $request->phone,
    ]);

    return response()->json([
        'message' => 'تم حفظ البيانات بنجاح',
        'user' => $user
    ], 200);
}
public function verifyAccount(Request $request)
{
    $request->validate([
        'code' => 'required'
    ]);

    // المستخدم من التوكن
    $user = $request->user();

    // إذا الحساب مفعل مسبقًا
    if ($user->is_active) {
        return response()->json([
            'message' => 'الحساب مفعل مسبقًا'
        ], 400);
    }

    // التحقق من الكود
    if ($user->verification_code != $request->code) {
        return response()->json([
            'message' => 'الكود غير صحيح'
        ], 400);
    }

    // تفعيل الحساب
    $user->is_active = 1;
    $user->verification_code = null;
    $user->save();

    return response()->json([
        'message' => 'تم تفعيل الحساب بنجاح'
    ], 200);
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
            ,'role'=>$user->role
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
        'role'=>$user->role
    ], 200);
}


public function forgotPassword(Request $request)
{
    // التحقق من البيانات
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'password' => 'required|min:6|confirmed',
    ]);

    // البحث عن المستخدم
    $user = User::where('email', $request->email)->first();

    // توليد كود تحقق من 6 أرقام
    $code = rand(100000, 999999);

    // حفظ الكود وكلمة المرور الجديدة (مشفرة)
    $user->verification_code = $code;
    $user->temp_password = Hash::make($request->password);
    $user->save();

    // طباعة الكود في التيرمينال
    error_log("====================================");
    error_log("FORGOT PASSWORD");
    error_log("User ID : {$user->id}");
    error_log("Email   : {$user->email}");
    error_log("Code    : {$code}");
    error_log("====================================");

    // إرجاع استجابة JSON
    return response()->json([
        'message' => 'تم إنشاء كود التحقق بنجاح',
        'user_id' => $user->id
        ,'code'=>$code
    ], 200);
}
public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'password' => 'required|min:6|confirmed'
    ]);

    $user = User::where('email', $request->email)->first();

    $user->update([
        'password' => Hash::make($request->password),
        'verification_code' => null
    ]);

    return response()->json([
        'message' => 'تم تغيير كلمة المرور بنجاح'
    ]);
}
public function verifyResetCode(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'code' => 'required'
    ]);

    $user = User::find($request->user_id);

    if ($user->verification_code != $request->code) {
        return response()->json([
            'message' => 'الكود غير صحيح'
        ], 400);
    }

    // تغيير كلمة المرور
    $user->password = $user->temp_password;
    $user->temp_password = null;
    $user->verification_code = null;
    $user->save();

    return response()->json([
        'message' => 'تم تغيير كلمة المرور بنجاح'
    ], 200);

}
public function logout(Request $request)
{
    // يقوم بحذف الـ Token الذي يستخدمه المستخدم حالياً
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'تم تسجيل الخروج بنجاح'
    ]);
}
public function users()
{
    return User::select( 'id','name', 'last_name', 'email', 'address','phone', 'role')
        ->where('role', 'user')
        ->get();
}

public function technicians()
{
    return User::select( 'id','name', 'last_name', 'email', 'address','phone', 'role')
        ->where('role', 'technician')
        ->get();
}



public function deleteUser($id)
{
    $user = User::where('id', $id)
        ->where('role', 'user')
        ->first();

    if (!$user) {
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    $user->delete();

    return response()->json([
        'message' => 'User deleted successfully'
    ]);
}

public function deleteTechnician($id)
{
    $tech = User::where('id', $id)
        ->where('role', 'technician')
        ->first();

    if (!$tech) {
        return response()->json([
            'message' => 'Technician not found'
        ], 404);
    }

    $tech->delete();

    return response()->json([
        'message' => 'Technician deleted successfully'
    ]);
}
public function createTechnician(Request $request)
{
    $request->validate([
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
    ]);

    $admin = $request->user();

    if ($admin->role !== 'super_admin') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $technician = User::create([
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'technician',
        'is_active' => true,
    ]);

    return response()->json([
        'message' => 'تم إنشاء حساب الفني بنجاح',
        'technician' => $technician
    ], 201);
}
public function showProfile()
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'last_name' => $user->last_name,
        'address' => $user->address,
        'email' => $user->email,
        'phone'=>$user->phone
    ]);
}
public function updateProfile(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    $request->validate([
        'name' => 'sometimes|string|max:255',
        'last_name' => 'sometimes|string|max:255',
        'address' => 'sometimes|string|max:255',
        'email' => 'sometimes|email',
        'phone' => 'sometimes|string|max:15',
    ]);

    DB::table('users')
        ->where('id', $user->id)
        ->update([
            'name' => $request->name ?? $user->name,
            'last_name' => $request->last_name ?? $user->last_name,
            'address' => $request->address ?? $user->address,
            'email' => $request->email ?? $user->email,
            'phone'=>$request->phone ?? $user->phone,
            'updated_at' => now()
        ]);

    $updatedUser = DB::table('users')
        ->where('id', $user->id)
        ->first();

    return response()->json([
        'message' => 'Profile updated successfully',
        'user' => $updatedUser
    ]);
}
}
