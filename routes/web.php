<?php

use App\Events\TestEvent;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\auth\AuthenticatedSessionController;
use App\Http\Controllers\RoomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



Route::get('/main',[RoomController::class,'main'])->middleware('auth');
Route::get('/test', function(){
    $id=Auth::id();
    $user=Auth::user();
    return Inertia::render('test',['user'=>$user,'id'=>$id,'room_id'=>1]);
})->name("test")->middleware('auth');
Route::post('/testreverb', function(Request $request){
    $message=$request->input('message');
    $room_id=$request->input('room_id');
    broadcast(new TestEvent($message,$room_id))->toOthers();
    return response()->json(['message' => 'Message broadcasted successfully!']);
});
Route::get('/testmodel',function(){
$chat=Auth::user();

dd($chat->name);
});
Route::get('/', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
