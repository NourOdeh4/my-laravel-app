<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\ProfileController;

// 1. راوتات عامة (لا تحتاج لتسجيل دخول)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/complete-profile', [AuthController::class, 'completeProfile']);
Route::post('/verify-account', [AuthController::class, 'verifyAccount']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
// 2. راوتات محمية (تحتاج Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});