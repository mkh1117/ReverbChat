<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\room_user;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function main(){
        $id=Auth::id();
        $rooms = Room::select('id', 'name')
                    ->whereIn('id',
                        room_user::where('user_id', $id)
                                 ->pluck('room_id')
                    )->get();

        return Inertia::render('main',['rooms'=>$rooms]);
    }


public function show($room_id)
{
    $id = Auth::id();
    abort_if(
        !room_user::where('room_id', $room_id)
                  ->where('user_id', $id)
                  ->exists(),
        403
    );

    $room=Room::where('id',$room_id)->firstOrFail();

    $chatName = $this->resolveChatName($room, $id);

    return Inertia::render('test', [
        'user'      => Auth::user(),
        'room'      => $room,
        'chat_name' => $chatName,
    ]);
}

private function resolveChatName(Room $room, int $userId): string
{
    if($room->type !== 'private') {
        return $room->name;
    }
    $contacts = Contact::where('owner_id', $userId)
                       ->pluck('custom_name', 'target_id');

    $other = room_user::where('room_id', $room->id)
                      ->where('user_id', '!=', $userId)
                      ->with('user:id,name')
                      ->first();

    return $contacts->get($other?->user?->id)
           ?? $other?->user?->name
           ?? 'کاربر حذف شده';
}
}
