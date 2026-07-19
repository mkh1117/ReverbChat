<?php

use App\Events\TestEvent;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\auth\AuthenticatedSessionController;
use App\Http\Controllers\RoomController;
use App\Models\chat;
use App\Models\room_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



Route::get('/main',[RoomController::class,'main'])->middleware('auth');
Route::get('/chat/{room_id}', [RoomController::class, 'show'])->name("test")->middleware('auth');
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

    broadcast(new TestEvent($validated['message'], $room_id))->toOthers();

    return response()->json(['success' => true]);

})->middleware('auth');
// Route::get('/testmodel',function(){
// $chat=Auth::user();
// dd($chat->name);
// });
Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::get('/dashboard', [RoomController::class,'main'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
