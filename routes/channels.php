<?php

use App\Models\room_user;
use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });
Broadcast::channel('message.{room_id}', function($user,$room_id){
    $userid=(int) Auth::id();
    return room_user::where('room_id',(int) $room_id)->where('user_id', $userid)->exists();
});
