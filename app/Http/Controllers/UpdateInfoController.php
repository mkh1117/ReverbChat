<?php

namespace App\Http\Controllers;

use App\Models\RoomAvatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UpdateInfoController extends Controller
{
    public function updateAvatar(Request $request,$room)
{

    $request->validate([
        'avatar' => 'required|image|mimes:jpeg,png,jpg,webp',
    ]);

    if ($request->hasFile('avatar')) {

        $path = $request->file('avatar')->store('avatars/rooms', 'public');


        $newAvatar = RoomAvatar::create([
            'room_id' => $room,
            'path'    => $path,
        ]);


        return response()->json([
            'message' => 'تصویر با موفقیت آپلود شد.',
            'avatar'  => Storage::url($path),
            'avatar_id' => $newAvatar->id,
        ], 201);
    }

    return response()->json([
        'message' => 'فایلی برای آپلود انتخاب نشده است.'
    ], 400);
}
}
