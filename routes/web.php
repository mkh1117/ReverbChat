<?php

use App\Events\TestEvent;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\auth\AuthenticatedSessionController;
use App\Http\Controllers\RoomController;
use App\Models\room_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



Route::get('/main',[RoomController::class,'main'])->middleware('auth');
Route::get('/chat/{room_id}', function($room_id){
    $id=Auth::id();
    $user=Auth::user();
    return Inertia::render('test',['user'=>$user,'id'=>$id,'room_id'=>$room_id]);
})->name("test")->middleware('auth');
Route::post('/testreverb', function(Request $request){
    $message=$request->input('message');
    $id=Auth::id();
    $room_id=$request->input('room_id');

    $exist=room_user::where('room_id',$room_id)->where('user_id',$id)->exists();
    if($exist){
    broadcast(new TestEvent($message,$room_id))->toOthers();
    return response()->json(['message' => 'Message broadcasted successfully!']);
    }
    return;
})->middleware('auth');
Route::get('/testmodel',function(){
$chat=Auth::user();
dd($chat->name);
});
Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
