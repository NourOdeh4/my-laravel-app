<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }



public function updateAvatar(Request $request)
{
    // Debug logs
    Log::info($request->all());
    Log::info($request->hasFile('avatar') ? 'File Found' : 'File NOT Found');

    // لازم نفس الاسم avatar
    $request->validate([
        'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // المستخدم (Sanctum)
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated user'
        ], 401);
    }

    // رفع الصورة
    $path = $request->file('avatar')->store('avatars', 'public');

    // تحديث قاعدة البيانات
    $user->update([
        'avatar' => $path
    ]);

    return response()->json([
        'message' => 'Profile avatar updated successfully',
        'path' => asset('storage/' . $path)
    ], 200);
}
public function getAvatar(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated user'
        ], 401);
    }

    return response()->json([
        'avatar' => $user->avatar
            ? asset('storage/' . $user->avatar)
            : null
    ], 200);
}
}
