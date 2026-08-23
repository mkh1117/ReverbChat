<?php

namespace App\Http\Controllers;

use App\Events\DeleteAvatarEvent;
use App\Models\chat;
use App\Models\Room;
use App\Models\room_user;
use App\Models\Contact;
use App\Models\RoomAvatar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function main(){
        $userId=Auth::id();
        $rooms = Room::query()
        ->select('id', 'name', 'type')
        ->whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
        ->withCount(['messages as unread_count' => function ($query) use ($userId) {
            $query->where('is_read', false)
                  ->where('sender_id', '!=', $userId);
        }])
        ->get();

        $contacts=Contact::where('owner_id',$userId)->get();

        return Inertia::render('main',['rooms'=>$rooms,'contacts'=>$contacts]);
    }


public function show($room_id)
{
    $userId = Auth::id();

    $roomUser = DB::table('room_users')
        ->where('room_id', $room_id)
        ->where('user_id', $userId)
        ->first();

    abort_if(!$roomUser, 403);

    $room = Room::where('id', $room_id)->firstOrFail();
    $otherUser = null;
    if ($room->type === 'private') {
        $otherUser = User::whereHas('rooms', function ($q) use ($room_id) {
            $q->where('rooms.id', $room_id);
        })->where('id', '!=', $userId)
          ->select('id', 'name', 'bio', 'last_seen_at')
          ->first();
    }

    $chats = Chat::with(['sender:id,name','parent'])
        ->select('id', 'sender_id', 'message', 'is_read','reply_to_id', 'created_at', 'updated_at')
        ->where('room_id', $room_id)
        ->where(function ($query) use ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)->where('sender_delete', false);
            })->orWhere(function ($q) use ($userId) {
                $q->where('sender_id', '!=', $userId)->where('receiver_delete', false);
            });
        })
        ->orderBy('created_at', 'asc')
        ->get();


    $avatars = RoomAvatar::where('room_id', $room_id)
        ->latest()
        ->pluck('path')
        ->map(fn($path) => Storage::url($path))
        ->toArray();

    $chatName = $this->resolveChatName($room, $userId);

    return Inertia::render('Room', [
        'user'       => Auth::user(),
        'room'       => $room,
        'chat_name'  => $chatName,
        'user_role'  => $roomUser->role,
        'other_user' => $otherUser,
        'members'    => $room->type !== 'private' ? $this->getRoomMembers($room_id) : [],
        'chats'      => $chats,
        'avatars'    => $avatars,
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

public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:group,channel',
            'selectedMembers' => 'nullable|array',
            'selectedMembers.*' => 'exists:users,id',
        ]);

        $userId = Auth::id();


        $room = Room::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'owner_id' => $userId,
        ]);


        room_user::create([
            'room_id' => $room->id,
            'user_id' => $userId,
            'role'    => 'owner',
        ]);


        if (!empty($validated['selectedMembers'])) {
            foreach ($validated['selectedMembers'] as $memberId) {
                room_user::create([
                    'room_id' => $room->id,
                    'user_id' => $memberId,
                    'role'    => 'member',
                ]);
            }
        }

        return redirect()->back();
    }

    public function updateLastSeen(Request $request) {
        $user = $request->user();
        $userId = Auth::id();
        if(! $user){
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $cacheKey = 'user_last_seen_'.$user->id;
        $dbThrottleKey = 'user_last_seen_db_updated_'.$user->id;

        if (! Cache::has($dbThrottleKey)) {
            User::where('id',$userId)->update([
                'last_seen_at' => now(),
            ]);

            Cache::put($cacheKey, true, 60);
        }
        return response()->json(['status' => 'success']);

    }

public function deleteAvatar(Request $request, $room)
{
    $request->validate([
        'image_url' => 'required|string',
    ]);


    $path = str_replace('/storage/', '', parse_url($request->image_url, PHP_URL_PATH));

    $avatar = RoomAvatar::where('room_id', $room)
        ->where('path', $path)
        ->first();

    if (!$avatar) {
        return response()->json(['message' => 'تصویر مورد نظر یافت نشد.'], 404);
    }


    DB::transaction(function () use ($avatar) {

        DB::afterCommit(function () use ($avatar) {
            Storage::disk('public')->delete($avatar->path);
        });

        $avatar->delete();
    });

    broadcast(new DeleteAvatarEvent($room,Storage::url($avatar->path)))->toOthers();

    return response()->json(['message' => 'تصویر با موفقیت حذف شد.']);
}

private function getRoomMembers($roomId)
{
    return User::whereHas('rooms', function ($q) use ($roomId) {
            $q->where('rooms.id', $roomId);
        })
        ->join('room_users', 'users.id', '=', 'room_users.user_id')
        ->where('room_users.room_id', $roomId)
        ->select('users.id', 'users.name', 'users.last_seen_at', 'room_users.role')
        ->get();
}
}
