<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\room_user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function main(){
        $id=Auth::id();
        $rooms = Room::select('id', 'name')
                    ->whereIn('id',
                        room_user::where('user_id', $id)
                                 ->pluck('room_id')
                    )->get();

        return Inertia::render('main',['rooms'=>$rooms]);
    }
}
