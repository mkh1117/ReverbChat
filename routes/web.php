<?php

use App\Events\MessageEvent;
use App\Events\MessagesReadEvent;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\auth\AuthenticatedSessionController;
use App\Http\Controllers\RoomController;
use App\Models\chat;
use App\Models\room_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



Route::get('/main',[RoomController::class,'main'])->middleware('auth');
Route::get('/chat/{room_id}', [RoomController::class, 'show'])->name("test")->middleware('auth');
Route::post('/rooms/create', [RoomController::class, 'store'])->middleware('auth');
Route::post('/chat/{room_id}/messages', function(Request $request, $room_id){
    $id = Auth::id();

    $exist = room_user::where('room_id', $room_id)
                      ->where('user_id', $id)
                      ->exists();

    abort_if(!$exist, 403);

    $validated = $request->validate([
        'message' => 'required|string|max:1000',
    ]);

    $chat = Chat::create([
        'room_id'   => $room_id,
        'sender_id' => $id,
        'message'   => $validated['message'],
    ]);

    broadcast(new MessageEvent($chat))->toOthers();

    return response()->json(['success' => true, 'message_id' => $chat->id]);

})->middleware('auth');
Route::post('chat/{room_id}/read', function(Request $request, $room_id) {
    $userId = Auth::id();

    // دریافت آی‌دی پیام‌های ارسال شده از سمت فرانت‌اند
    $message_Ids = $request->input('message_ids', []);

    if (empty($message_Ids)) {
        return response()->json(['success' => true, 'updated' => 0]);
    }
    // تغییر وضعیت فقط برای پیام‌های مشخصی که کاربر واقعاً روی صفحه دیده است
    $updatedCount = Chat::where('room_id', $room_id)
        ->whereIn('id', $message_Ids)
        ->where('sender_id', '!=', $userId)
        ->where('is_read', 0)
        ->update(['is_read' => 1]);
    Log::error($updatedCount);
    if ($updatedCount > 0) {
        // فرستادن لیست آی‌دی‌های خوانده شده به طرف مقابل جهت آبی کردن تیک‌ها به صورت آنی
        broadcast(new MessagesReadEvent($room_id, $message_Ids))->toOthers();
    }

    return response()->json(['success' => true, 'updated' => $updatedCount]);
})->middleware('auth');

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::get('/dashboard', [RoomController::class,'main'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
