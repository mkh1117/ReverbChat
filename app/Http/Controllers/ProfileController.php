<?php
namespace App\Http\Controllers;

use App\Models\UserAvatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user()->load(['avatars', 'currentAvatar']);

        return Inertia::render('Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'bio' => $user->bio,
                'current_avatar' => $user->currentAvatar ? Storage::url($user->currentAvatar->path) : null,
                'avatars' => $user->avatars->map(fn($a) => [
                    'id' => $a->id,
                    'url' => Storage::url($a->path),
                ]),
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'bio' => 'nullable|string|max:500',
        ]);

        $user->update($validated);

        return back()->with('success', 'اطلاعات پروفایل با موفقیت بروزرسانی شد.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'رمز عبور با موفقیت تغییر یافت.');
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');

        $avatar = $request->user()->avatars()->create([
            'path' => $path
        ]);

        return back()->with('success', 'تصویر پروفایل آپلود شد.');
    }

    public function deleteAvatar(UserAvatar $avatar)
    {
        if ($avatar->user_id !== Auth::id()) {
            abort(403);
        }

        Storage::disk('public')->delete($avatar->path);
        $avatar->delete();

        return back()->with('success', 'تصویر پروفایل حذف شد.');
    }
}
