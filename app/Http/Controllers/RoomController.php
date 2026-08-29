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
use App\Rules\UserMatchUsername;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function main()
{
    $userId = Auth::id();

    $savedContacts = Contact::where('owner_id', $userId)
        ->pluck('custom_name', 'target_id');

    $rooms = Room::query()
        ->select('rooms.id', 'rooms.name', 'rooms.type')
        ->whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
        ->with(['messages' => function ($query) {
            $query->latest()->limit(1);
        }])
        ->with(['users' => function ($query) {
            $query->select('users.id', 'users.name')->withPivot('last_read_sequence_id');
        }])
        ->withUnreadCount($userId)
        ->with(['avatars' => function ($query) {
            $query->latest();
        }])
        ->get()
        ->map(function ($room) use ($userId, $savedContacts) {
            $avatarPath = null;

            if ($room->type === 'private') {
                $partner = $room->users->firstWhere('id', '!=', $userId);

                if ($partner) {
                    $partnerName = $savedContacts->get($partner->id) ?? $partner->name;
                    $partner->name = $partnerName;
                    $lastAvatar = $partner->avatars?->first();
                    $avatarPath = $lastAvatar?->path;
                }
            } else {
                $lastAvatar = $room->avatars->first();
                $avatarPath = $lastAvatar?->path;
            }

            $room->avatar_url = $avatarPath ? Storage::url($avatarPath) : null;

            return $room;
        })
        ->sortByDesc(function ($room) {
            return optional($room->messages->first())->created_at;
        })
        ->values();

    $contacts = Contact::where('owner_id', $userId)->get();

    return Inertia::render('main', [
        'rooms' => $rooms,
        'contacts' => $contacts
    ]);
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

    $chats = Chat::with(['sender:id,name','parent','forwardedFrom.sender:id,name,username'])
        ->select('id', 'sender_id', 'message','sequence_id','views_count','reply_to_id', 'created_at','forwarded_from_id', 'updated_at')
        ->where('room_id', $room_id)
        ->where(function ($query) use ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)->where('sender_delete', false);
            })->orWhere(function ($q) use ($userId) {
                $q->where('sender_id', '!=', $userId)->where('receiver_delete', false);
            });
        })
        ->orderBy('sequence_id', 'asc')
        ->get();


    $avatars = RoomAvatar::where('room_id', $room_id)
        ->latest()
        ->pluck('path')
        ->map(fn($path) => Storage::url($path))
        ->toArray();

    $chatName = $this->resolveChatName($room, $userId);

    $userRooms = Room::whereHas('users', function ($q) use ($userId) {
        $q->where('users.id', $userId);
    })
    ->with([
        'users' => function ($q) {
            $q->select('users.id', 'users.name')
              ->with('avatar');
        }
    ])
    ->get()
    ->map(function ($r) use ($userId) {
        $currentUserPivot = $r->users->firstWhere('id', $userId);
        $r->user_role = $currentUserPivot?->pivot?->role ?? 'member';

        if ($r->type === 'private') {
            $partner = $r->users->firstWhere('id', '!=', $userId);
            $r->name = $partner?->name ?? 'کاربر';
        }
        return $r;
    });

    return Inertia::render('Room', [
        'user'       => Auth::user(),
        'room'       => $room,
        'chat_name'  => $chatName,
        'user_role'  => $roomUser->role,
        'other_user' => $otherUser,
        'user_last_read' => $roomUser->last_read_sequence_id ?? 0,
        'members'    => $room->type !== 'private' ? $this->getRoomMembers($room_id) : [],
        'chats'      => $chats,
        'all_chats'      => $userRooms,
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
    $room = Room::findOrFail($roomId);

    return $room->users()
        ->select('users.id', 'users.name', 'users.last_seen_at')
        ->withPivot('role', 'last_read_sequence_id')
        ->get();
}

public function getContactsAndRecent(Request $request)
{
    try {
        $userId = Auth::id();


        $recentUserIds = DB::table('room_users as ru1')
            ->join('rooms', 'rooms.id', '=', 'ru1.room_id')
            ->join('room_users as ru2', 'ru2.room_id', '=', 'ru1.room_id')
            ->where('ru1.user_id', $userId)
            ->where('ru2.user_id', '!=', $userId)
            ->where('rooms.type', 'private')
            ->pluck('ru2.user_id');


        $contacts = Contact::where('owner_id', $userId)
            ->get()
            ->keyBy('target_id');


        $allUserIds = $recentUserIds->merge($contacts->keys())->unique();

        $users = User::whereIn('id', $allUserIds)
            ->with('avatar')
            ->select('id', 'name', 'username')
            ->get()
            ->map(function ($user) use ($contacts) {
                $contact = $contacts->get($user->id);
                return [
                    'id' => $user->id,
                    'name' => ($contact && $contact->custom_name) ? $contact->custom_name : $user->name,
                    'username' => $user->username,
                    'avatar' => $user->avatar ? $user->avatar->path : null,
                    'is_contact' => (bool) $contact,
                ];
            });

        return response()->json([
            'users' => $users->values()
        ]);

    } catch (\Throwable $e) {
        Log::error('Error in getContactsAndRecent: ' . $e->getMessage());

        return response()->json([
            'message' => 'خطا در دریافت مخاطبین و چت‌های اخیر'
        ], 500);
    }
}

public function addMember(Request $request, string $roomId)
{

    $validated = $request->validate([
        'user_id'  => ['required', 'integer', Rule::exists('users', 'id')],
        'username' => ['required', 'string', new UserMatchUsername($request->user_id)],
    ]);

    $authUserId = Auth::id();


    $room = Room::where('id', $roomId)
        ->whereHas('users', fn($q) => $q->where('users.id', $authUserId))
        ->first();

    abort_if(!$room, 403, 'شما دسترسی به این اتاق را ندارید.');


    if ($room->type === 'private') {
        return response()->json([
            'message' => 'امکان افزودن عضو به چت خصوصی وجود ندارد.'
        ], 422);
    }


    if ($room->users()->where('users.id', $validated['user_id'])->exists()) {
        return response()->json([
            'message' => 'این کاربر قبلاً به گروه اضافه شده است.'
        ], 422);
    }

    $room->users()->attach($validated['user_id'], [
        'role' => 'member',
    ]);

    return response()->json([
        'message' => 'کاربر با موفقیت به گروه اضافه شد.',
    ], 201);
}

public function startPrivateChat($target_user_id)
{
    $currentUserId = Auth::id();

    if ($currentUserId == $target_user_id) {
        return back()->withErrors(['message' => 'نمی‌توانید با خودتان چت خصوصی ایجاد کنید.']);
    }

    $existingRoom = Room::where('type', 'private')
        ->whereHas('users', function ($q) use ($currentUserId) {
            $q->where('users.id', $currentUserId);
        })
        ->whereHas('users', function ($q) use ($target_user_id) {
            $q->where('users.id', $target_user_id);
        })
        ->first();

    if ($existingRoom) {
        return redirect()->route('chat.show', $existingRoom->id);
    }


    $room = DB::transaction(function () use ($currentUserId, $target_user_id) {
        $newRoom = Room::create([
            'type' => 'private',
            'name' => null,
        ]);

        $newRoom->users()->attach([
            $currentUserId   => ['role' => 'admin', 'last_read_sequence_id' => 0],
            $target_user_id  => ['role' => 'admin', 'last_read_sequence_id' => 0],
        ]);

        return $newRoom;
    });

    return redirect()->route('chat.show', $room->id);
}
}
