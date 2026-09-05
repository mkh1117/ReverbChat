<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserProfileController extends Controller
{
    public function show($username)
    {
        $profileUser = User::where('username', $username)
            ->select('id', 'name', 'bio', 'username', 'last_seen_at')
            ->with(['avatars' => function ($q) {
                $q->latest();
            }])
            ->firstOrFail();

        $avatars = $profileUser->avatars->map(function ($avatar) {
            return Storage::url($avatar->path);
        })->toArray();

        return Inertia::render('UserProfile', [
            'profileUser' => [
                'id' => $profileUser->id,
                'name' => $profileUser->name,
                'username' => $profileUser->username,
                'bio' => $profileUser->bio,
                'last_seen_at' => $profileUser->last_seen_at,
                'avatar' => $avatars[0] ?? null,
            ],
            'avatars' => $avatars,
        ]);
    }
}
