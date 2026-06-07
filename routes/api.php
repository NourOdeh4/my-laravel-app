<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\ProfileController;

// راوتات التسجيل والتفعيل
Route::post('/register', [AuthController::class, 'register']);
Route::post('/complete-profile', [AuthController::class, 'completeProfile']);
Route::post('/verify-account', [AuthController::class, 'verifyAccount']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
Route::middleware('auth:sanctum')->post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
use Illuminate\Support\Facades\Auth;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
