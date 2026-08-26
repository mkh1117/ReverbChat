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

Route::post('/chat/{room_id}/messages', [MessageController::class,'newMessage'])->middleware('auth');
Route::post('chat/{room_id}/read', [MessageController::class,'messageRead'])->middleware('auth');

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::get('/dashboard', [RoomController::class,'main'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


