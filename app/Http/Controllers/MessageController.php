<?php

namespace App\Http\Controllers;

use App\Events\DeleteEvent;
use App\Events\MessageEvent;
use App\Events\MessagesReadEvent;
use App\Models\chat;
use App\Models\Room;
use App\Models\room_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;


class MessageController extends Controller
{
    public function delete($message_id,$room_id,Request $request){

        $validated = $request->validate([
        'delete_type' => ['required', 'string', Rule::in(['everyone', 'me'])],
    ], [
        'delete_type.required' => 'نوع حذف پیام الزامی است.',
        'delete_type.in' => 'نوع حذف وارد شده معتبر نیست.',
    ]);

    $deleteType = $validated['delete_type'];

        $userId = Auth::id();
        $roomUser = room_user::where('room_id', $room_id)
            ->where('user_id', $userId)
            ->first();

        abort_if(!$roomUser, 403);

        $chat = chat::where('id', $message_id)->where('room_id', $room_id)->firstOrFail();

        if ($deleteType == 'me') {
        //اگر فرستنده خواست برای خودش حذف کنه
        if ($chat->sender_id == $userId) {
            $chat->update(['sender_delete' => true]);
        } else {
            // اگر گیرنده خواست برای خودش حذف کنه
            $chat->update(['receiver_delete' => true]);
        }
    } else {

        // حذف برای همه
        if ($chat->sender_id == $userId) {
            $chat->update(['sender_delete' => true, 'receiver_delete' => true]);


            broadcast(new DeleteEvent($message_id, $room_id))->toOthers();
        }
    }


        return response()->json([
        'success' => true,
    ], 201);

    }
    public function messageRead(Request $request,$room_id){

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

    if ($newSeq > $oldSeq) {


        $updatedCount = Chat::where('room_id', $room_id)
            ->where('sequence_id', '>', $oldSeq)
            ->where('sequence_id', '<=', $newSeq)
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', '!=', $userId)
                  ->orWhereNull('sender_id');
            })
            ->increment('views_count');


        DB::table('room_users')
            ->where('room_id', $room_id)
            ->where('user_id', $userId)
            ->update(['last_read_sequence_id' => $newSeq]);

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
    }



    public function newMessage(Request $request,$room_id){
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
        'forwarded_from_id' => 'nullable|exists:chats,id',
    ]);

    $sequenceId = chat::getNextSequenceId($room_id);

    $chat = Chat::create([
        'room_id'   => $room_id,
        'sender_id' => $userId,
        'sequence_id' => $sequenceId,
        'message'   => $validated['message'],
        'forwarded_from_id' => $request->forwarded_from_id,
        'reply_to_id' => $validated['reply_to_id'] ?? null,
    ]);

    $chat->load(['sender:id,name', 'parent']);

    broadcast(new MessageEvent($chat))->toOthers();

    return response()->json([
        'success' => true,
        'id'      => $chat->id,
        'message' => $chat
    ], 201);
    }

}
