<?php

namespace App\Http\Controllers;

use App\Events\AvatarEvent;
use App\Events\BioEvent;
use App\Models\Room;
use App\Models\RoomAvatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UpdateInfoController extends Controller
{
    public function updateAvatar(Request $request,$room_id)
{

    $request->validate([
        'avatar' => 'required|image|mimes:jpeg,png,jpg,webp',
    ]);

    if ($request->hasFile('avatar')) {

        $path = $request->file('avatar')->store('avatars/rooms', 'public');


        $newAvatar = RoomAvatar::create([
            'room_id' => $room_id,
            'path'    => $path,
        ]);

        broadcast(new AvatarEvent(Storage::url($path),$room_id))->toOthers();


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

public function updateDescription(Request $request, $room_id){

    $request->validate([
        'description' => 'nullable|string|max:255',
    ]);


    Room::where('id',$room_id)->update([
                'bio' => $request->description,
            ]);
    broadcast(new BioEvent($request->description,$room_id))->toOthers();

    return response()->json([
        'status' => 'success',
        'description' => $request->description,
    ]);

}
}
