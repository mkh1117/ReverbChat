<?php

use App\Events\MessageEvent;
use App\Events\MessagesReadEvent;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\auth\AuthenticatedSessionController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UpdateInfoController;
use App\Models\chat;
use App\Models\Room;
use App\Models\room_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



Route::get('/main',[RoomController::class,'main'])->middleware('auth');
Route::get('/chat/contacts-and-recent', [RoomController::class, 'getContactsAndRecent'])->middleware('auth');
Route::get('/chat/{room_id}', [RoomController::class, 'show'])->name("test")->middleware('auth');
Route::post('/rooms/create', [RoomController::class, 'store'])->middleware('auth');
Route::post('/chat/rooms/{room_id}/delete-avatar',[RoomController::class,'deleteAvatar'])->middleware(['auth','chat.admin']);
Route::post('/chat/rooms/{room_id}/update-description',[UpdateInfoController::class,'updateDescription'])->middleware(['auth','chat.admin']);
Route::post('/chat/rooms/{room_d}/add-member',[RoomController::class,'addMember'])->middleware(['auth']);


Route::delete('/chat/messages/{message_id}/{room_id}',[MessageController::class,'delete'])->middleware('auth');



Route::post('/chat/rooms/{room_id}/update-avatar',[UpdateInfoController::class,'updateAvatar'])->name('update_avatar')->middleware(['auth','chat.admin']);

Route::post('/chat/{room_id}/messages', function (Request $request, $room_id) {
    $userId = Auth::id();


    $roomUser = room_user::where('room_id', $room_id)
        ->where('user_id', $userId)
        ->first();

    abort_if(!$roomUser, 403);

    $room = Room::select('id', 'type')->findOrFail($room_id);


    if ($room->type === 'channel' && $roomUser->role === 'member') {
        abort(403);
    }

    $validated = $request->validate([
        'message' => 'required|string|max:1000',
        'reply_to_id' => 'nullable|exists:chats,id',
    ]);

    $sequenceId = chat::getNextSequenceId($room_id);

    $chat = Chat::create([
        'room_id'   => $room_id,
        'sender_id' => $userId,
        'sequence_id' => $sequenceId,
        'message'   => $validated['message'],
        'reply_to_id' => $validated['reply_to_id'] ?? null,
    ]);

    $chat->load(['sender:id,name', 'parent']);

    broadcast(new MessageEvent($chat))->toOthers();

    return response()->json([
        'success' => true,
        'id'      => $chat->id,
        'message' => $chat
    ], 201);

})->middleware('auth');
Route::post('chat/{room_id}/read', function(Request $request, $room_id) {
    $request->validate([
        'sequence_id' => 'required|integer|min:1',
    ]);

    $userId = Auth::id();
    $newSeq = (int) $request->input('sequence_id');

    $roomUser = DB::table('room_users')
        ->where('room_id', $room_id)
        ->where('user_id', $userId)
        ->first();

    if (!$roomUser) {
        return response()->json(['message' => 'عضو این روم نیستید.'], 403);
    }

    $oldSeq = (int) $roomUser->last_read_sequence_id;

    // ۲. تنها در صورتی که کاربر پیام‌های جدیدتری را ببیند
    if ($newSeq > $oldSeq) {

        // افزایش ۱ واحدی ویوی تمام پیام‌های بین oldSeq و newSeq (به‌جز پیام‌های خود کاربر)
        $updatedCount = Chat::where('room_id', $room_id)
            ->where('sequence_id', '>', $oldSeq)
            ->where('sequence_id', '<=', $newSeq)
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', '!=', $userId)
                  ->orWhereNull('sender_id');
            })
            ->increment('views_count');

        // آپدیت پوینتر کاربر در جدول room_users
        DB::table('room_users')
            ->where('room_id', $room_id)
            ->where('user_id', $userId)
            ->update(['last_read_sequence_id' => $newSeq]);

        // ۳. ارسال ایونت برای به‌روزرسانی آنی فرانت‌اند سایر کاربران
        if ($updatedCount > 0) {
            broadcast(new MessagesReadEvent($room_id, $userId, $newSeq))->toOthers();
        }

        return response()->json([
            'success' => true,
            'updated' => $updatedCount,
            'last_read_sequence_id' => $newSeq,
        ]);
    }

    return response()->json([
        'success' => true,
        'updated' => 0,
        'last_read_sequence_id' => $oldSeq,
    ]);
})->middleware('auth');

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::get('/dashboard', [RoomController::class,'main'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


