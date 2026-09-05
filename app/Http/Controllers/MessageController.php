<?php

namespace App\Http\Controllers;

use App\Events\DeleteEvent;
use App\Events\MessageEvent;
use App\Events\MessagesReadEvent;
use App\Models\chat;
use App\Models\chatAttachment;
use App\Models\Room;
use App\Models\room_user;
use App\Services\ChatEncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'message' => 'nullable|string|max:1000',
            'reply_to_id' => 'nullable|exists:chats,id',
            'file'         => 'nullable|file|max:512000',
            'forwarded_from_id' => 'nullable|exists:chats,id',
        ]);

        if (!$request->filled('message') && !$request->hasFile('file')) {
            return response()->json(['message' => 'ارسال متن یا فایل الزامی است.'], 422);
        }

        $sequenceId = chat::getNextSequenceId($room_id);


        $createdFilePath = null;

        try {
            $chat = DB::transaction(function () use ($request, $room_id, $userId, $sequenceId, $validated, &$createdFilePath) {

                $chat = Chat::create([
                    'room_id'           => $room_id,
                    'sender_id'         => $userId,
                    'sequence_id'       => $sequenceId,
                    'message'           => $validated['message'] ?? null,
                    'forwarded_from_id' => $request->forwarded_from_id,
                    'reply_to_id'       => $validated['reply_to_id'] ?? null,
                ]);


                if ($request->hasFile('file')) {
                    $uploadedFile = $request->file('file');
                    $originalName = $uploadedFile->getClientOriginalName();
                    $mimeType     = $uploadedFile->getClientMimeType();
                    $fileSize     = $uploadedFile->getSize();


                    $fileType = strtok($mimeType, '/');


                    $storageDir = storage_path("app/encrypted_chats/room_{$room_id}");
                    if (!file_exists($storageDir)) {
                        mkdir($storageDir, 0755, true);
                    }

                    $encryptedFileName = Str::uuid() . '.enc';
                    $fullDestinationPath = $storageDir . '/' . $encryptedFileName;


                    $encryptedSuccess = ChatEncryptionService::encryptFileStream(
                        $uploadedFile->getRealPath(),
                        $fullDestinationPath,
                        $room_id
                    );

                    if (!$encryptedSuccess) {
                        throw new \Exception('خطا در رمزنگاری و ذخیره‌سازی فایل.');
                    }


                    $createdFilePath = $fullDestinationPath;
                    $relativeFilePath = "encrypted_chats/room_{$room_id}/" . $encryptedFileName;


                    ChatAttachment::create([
                        'chat_id'       => $chat->id,
                        'room_id'       => $room_id,
                        'file_path'     => $relativeFilePath,
                        'original_name' => $originalName,
                        'file_type'     => $fileType,
                        'mime_type'     => $mimeType,
                        'file_size'     => $fileSize,
                        'meta'          => null,
                    ]);
                }

                return $chat;
            });

        } catch (\Throwable $e) {
            // در صورت بروز خطا، دیتابیس رول‌بک می‌شود و فایل فیزیکی ایجاد شده پاک می‌گردد
            if ($createdFilePath && file_exists($createdFilePath)) {
                @unlink($createdFilePath);
            }

            Log::error('Chat Creation Failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'خطا در ثبت پیام و پردازش فایل.'
            ], 500);
        }

        if ($chat->message) {
            $chat->message = ChatEncryptionService::decrypt($chat->message, $room_id);
        }



        $chat->load(['sender:id,name', 'parent' , 'attachment']);

        broadcast(new MessageEvent($chat))->toOthers();

        return response()->json([
            'success' => true,
            'id'      => $chat->id,
            'message' => $chat
        ], 201);
    }

    public function streamAttachment($attachment_id)
{
    $userId = Auth::id();

    $attachment = ChatAttachment::findOrFail($attachment_id);


    $isMember = DB::table('room_users')
        ->where('room_id', $attachment->room_id)
        ->where('user_id', $userId)
        ->exists();

    abort_if(!$isMember, 403);

    $fullPath = storage_path('app/' . $attachment->file_path);

    return ChatEncryptionService::decryptFileStreamResponse(
        $fullPath,
        $attachment->room_id,
        $attachment->mime_type,
        $attachment->original_name
    );
}

}
