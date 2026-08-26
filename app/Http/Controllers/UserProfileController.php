<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserProfileController extends Controller
{
    public function show($username){

        $profileUser = User::where('username', $username)
            ->select('id', 'name','bio', 'username','last_seen_at')
            ->with(['avatars' => function ($q) {
                $q->latest();
            }])
            ->firstOrFail();

        $avatars = $profileUser->avatars->map(function ($avatar) {
            return Storage::url($avatar->path);
        })->toArray();


        $currentAvatar = $avatars[0] ?? null;

        return Inertia::render('UserProfile', [
            'profileUser'   => $profileUser,
            'avatars'       => $avatars,
        ]);
    }
}
