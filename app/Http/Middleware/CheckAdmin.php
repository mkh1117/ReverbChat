<?php

namespace App\Http\Middleware;

use App\Models\room_user;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $room = $request->route('room_id');
        if (!$room) {
            return response()->json(['message' => 'روم یافت نشد.'], 404);
        }
        $roomId = is_object($room) ? $room->id : $room;
        $userId = Auth::id();
        $access = ['admin','owner'];

        if (is_object($room) && $room->type === 'private') {
        return response()->json(['message' => 'این عملیات برای چت خصوصی امکان‌پذیر نیست.'], 403);
    }

        $exists=room_user::where('room_id',$roomId)->where('user_id',$userId)->whereIn('role', $access)->exists();

        if (!$exists){
            return response()->json(['message' => 'عدم دسترسی'], 403);
        }


        return $next($request);
    }
}
