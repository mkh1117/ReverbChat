<?php


use App\Http\Controllers\ProfileController;
use App\Http\Controllers\auth\AuthenticatedSessionController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UpdateInfoController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;



Route::middleware('auth')->group(function () {

    Route::get('/main',[RoomController::class,'main']);

    Route::get('/chat/contacts-and-recent', [RoomController::class, 'getContactsAndRecent']);

    Route::get('/chat/{room_id}', [RoomController::class, 'show'])->name("chat.show");

    Route::post('/rooms/create', [RoomController::class, 'store']);

    Route::post('/chat/rooms/{room_id}/delete-avatar',[RoomController::class,'deleteAvatar'])->middleware(['chat.admin']);

    Route::post('/chat/rooms/{room_id}/update-description',[UpdateInfoController::class,'updateDescription'])->middleware(['chat.admin']);

    Route::post('/chat/rooms/{room_d}/add-member',[RoomController::class,'addMember']);

    Route::delete('/chat/messages/{message_id}/{room_id}',[MessageController::class,'delete']);

    Route::post('/chat/rooms/{room_id}/update-avatar',[UpdateInfoController::class,'updateAvatar'])->name('update_avatar')->middleware(['chat.admin']);

    Route::get('/chat/attachments/{attachment_id}', [MessageController::class, 'streamAttachment'])
    ->middleware(['auth'])
    ->name('attachments.stream');

    Route::prefix('chat/rooms/{room_id}')->group(function () {

        Route::post('/change-role', [RoomController::class, 'changeRole'])->middleware('chat.admin');

        Route::post('/kick', [RoomController::class, 'kickUser'])->middleware('chat.admin');

    });

    Route::post('/chat/{room_id}/messages', [MessageController::class,'newMessage']);

    Route::post('/chat/{room_id}/read', [MessageController::class,'messageRead']);


    Route::get('/dashboard', [RoomController::class,'main'])->middleware(['verified'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar.upload');
    
    Route::delete('/profile/avatar/{avatar}', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');

    Route::post('/chat/private/start/{target_user_id}', [RoomController::class, 'startPrivateChat'])->name('chat.private.start');

    Route::get('/@{username}', [UserProfileController::class, 'show'])->name('user.profile');
});

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');

require __DIR__.'/auth.php';


