<?php

namespace App\Http\Controllers;

use App\Events\DeleteEvent;
use App\Models\chat;
use App\Models\room_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // اگر فرستنده خواست برای خودش حذف کند
        if ($chat->sender_id == $userId) {
            $chat->update(['sender_delete' => true]);
        } else {
            // اگر گیرنده خواست برای خودش حذف کند
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
}
