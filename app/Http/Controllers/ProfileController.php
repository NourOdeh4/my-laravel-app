<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller{
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

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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

// أضيفي هذا السطر:
    \Log::info($request->all());
    \Log::info($request->hasFile('avatar') ? 'File Found' : 'File NOT Found');
    // 1. التحقق
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // 2. رفع الملف
    $path = $request->file('image')->store('avatars', 'public');

    // 3. تحديث المستخدم
    $user = auth()->user();
    $user->update(['avatar' => $path]);

    // 4. الرد بصيغة JSON للفريق (بدل back)
    return response()->json([
        'message' => 'Profile avatar updated successfully',
        'path' => asset('storage/' . $path) // هذا الرابط الذي سيستخدمه الفرونت إند لعرض الصورة
    ], 200);
}

}
